<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCouponCode;
use App\Models\Coupon;
use App\Models\CouponValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Coupon::with('user')
            ->orderBy('created_at', 'desc');

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search filter (code, name, phone)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $coupons = $query->paginate(20)->withQueryString();

        // Append formatted_phone to each coupon
        $coupons->getCollection()->transform(function ($coupon) {
            $coupon->formatted_phone = $coupon->formatted_phone;
            return $coupon;
        });

        return Inertia::render('coupons/Index', [
            'coupons' => $coupons,
            'filters' => $request->only(['status', 'search', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('coupons/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_social_media' => ['nullable', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        // Normalize phone number before creating coupon (same as model mutator)
        $phone = preg_replace('/[^0-9]/', '', $validated['customer_phone']);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        // Use Job to create coupon synchronously (dispatchSync for immediate execution)
        GenerateCouponCode::dispatchSync([
            'type' => $validated['type'],
            'description' => $validated['description'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $phone, // Use normalized phone
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_social_media' => $validated['customer_social_media'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => Auth::id(),
        ]);

        // Get the created coupon - use multiple fallback strategies
        // First try to match by all fields, then fallback to latest by this user
        $coupon = Coupon::where('created_by', Auth::id())
            ->where('customer_name', $validated['customer_name'])
            ->where('customer_phone', $phone) // Use normalized phone
            ->where('type', $validated['type'])
            ->latest('id')
            ->first();

        // Fallback: if not found, get the latest coupon created by this user
        // (in case there's a timing issue or normalization difference)
        if (!$coupon) {
            $coupon = Coupon::where('created_by', Auth::id())
                ->latest('id')
                ->firstOrFail();
        }

        return redirect()
            ->route('coupons.show', $coupon->id)
            ->with('success', 'Kupon berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        $coupon = Coupon::with(['user', 'validations.validator'])
            ->findOrFail($id);

        // Generate QR code URL (public coupon view)
        $qrUrl = route('coupons.public', $coupon->code);
        $publicUrl = url('/coupon/' . $coupon->code);

        // Append formatted_phone
        $coupon->formatted_phone = $coupon->formatted_phone;

        return Inertia::render('coupons/Show', [
            'coupon' => $coupon,
            'qrUrl' => $qrUrl,
            'publicUrl' => $publicUrl,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Kupon berhasil dihapus!');
    }

    /**
     * Check coupon status before validation (API endpoint)
     */
    public function check(string $code)
    {
        // Extract code from URL if full URL is provided
        $code = $this->extractCodeFromUrl($code);

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'exists' => false,
                'message' => 'Kupon tidak ditemukan',
            ], 404);
        }

        $canValidate = $coupon->canBeValidated();
        $message = '';

        if (!$canValidate) {
            if ($coupon->status === Coupon::STATUS_USED) {
                $message = 'Kupon sudah digunakan';
            } elseif ($coupon->status === Coupon::STATUS_EXPIRED) {
                $message = 'Kupon sudah kedaluwarsa';
            } elseif ($coupon->expires_at && $coupon->expires_at->isPast()) {
                $message = 'Kupon sudah kedaluwarsa';
            } else {
                $message = 'Kupon tidak dapat divalidasi';
            }
        }

        return response()->json([
            'exists' => true,
            'can_validate' => $canValidate,
            'message' => $message,
            'coupon' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'description' => $coupon->description,
                'customer_name' => $coupon->customer_name,
                'status' => $coupon->status,
                'expires_at' => $coupon->expires_at?->toIso8601String(),
            ],
        ], $canValidate ? 200 : 422);
    }

    /**
     * Validate (mark as used) a coupon
     */
    public function validate(Request $request, string $code)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Extract code from URL if full URL is provided
        $code = $this->extractCodeFromUrl($code);

        // Verify password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json([
                'message' => 'Password salah',
            ], 401);
        }

        $coupon = Coupon::where('code', $code)->firstOrFail();

        // Verify coupon can be validated
        if (!$coupon->canBeValidated()) {
            if ($coupon->status === Coupon::STATUS_USED) {
                return response()->json([
                    'message' => 'Kupon sudah digunakan',
                ], 422);
            }
            if ($coupon->status === Coupon::STATUS_EXPIRED || ($coupon->expires_at && $coupon->expires_at->isPast())) {
                return response()->json([
                    'message' => 'Kupon sudah kedaluwarsa',
                ], 422);
            }
            return response()->json([
                'message' => 'Kupon tidak dapat divalidasi',
            ], 422);
        }

        // Update coupon status
        $coupon->status = Coupon::STATUS_USED;
        $coupon->save();

        // Create validation record
        CouponValidation::create([
            'coupon_id' => $coupon->id,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
            'action' => 'used',
        ]);

        return response()->json([
            'message' => 'Kupon berhasil divalidasi',
            'coupon' => [
                'code' => $coupon->code,
                'status' => $coupon->status,
            ],
        ], 200);
    }

    /**
     * Reverse (cancel) a coupon validation
     */
    public function reverse(Request $request, string $id)
    {
        $request->validate([
            'password' => ['required', 'string'],
            'reason' => ['required', 'string', 'min:10'],
        ]);

        // Verify password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Password salah');
        }

        $coupon = Coupon::findOrFail($id);

        // Verify coupon status is 'used'
        if ($coupon->status !== Coupon::STATUS_USED) {
            return back()->with('error', 'Hanya kupon yang sudah digunakan yang dapat dibatalkan');
        }

        // Update coupon status back to 'active'
        $coupon->status = Coupon::STATUS_ACTIVE;
        $coupon->save();

        // Create reversal validation record
        CouponValidation::create([
            'coupon_id' => $coupon->id,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
            'action' => 'reversed',
            'notes' => $request->reason,
        ]);

        return back()->with('success', 'Penggunaan kupon berhasil dibatalkan');
    }

    /**
     * Extract coupon code from URL
     */
    private function extractCodeFromUrl(string $input): string
    {
        // If it's a full URL, extract the code
        if (strpos($input, '/coupon/') !== false) {
            $parts = explode('/coupon/', $input);
            return end($parts);
        }

        // If it's just the code, return as is
        return $input;
    }
}
