<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
            'expires_at' => ['nullable', 'date'],
        ]);

        $code = $this->generateCouponCode();

        $coupon = Coupon::create([
            'code' => $code,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_social_media' => $validated['customer_social_media'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => Auth::id(),
        ]);

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
     * Generate a unique coupon code in format ABC-1234-XYZ
     */
    private function generateCouponCode(): string
    {
        do {
            // Format: ABC-1234-XYZ
            $part1 = strtoupper(Str::random(3)); // ABC
            $part2 = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT); // 1234
            $part3 = strtoupper(Str::random(3)); // XYZ
            $code = "{$part1}-{$part2}-{$part3}";
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
