<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponStoreRequest;
use App\Jobs\GenerateCouponCode;
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
        if ($request->filled('status') && in_array($request->status, ['active', 'used', 'expired'])) {
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
    public function store(CouponStoreRequest $request)
    {
        $code = $this->generateCouponCode();

        $coupon = Coupon::create([
            'code' => $code,
            'type' => $request->type,
            'description' => $request->description,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_social_media' => $request->customer_social_media,
            'expires_at' => $request->expires_at,
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

        return Inertia::render('coupons/Show', [
            'coupon' => $coupon,
        ]);
    }

    /**
     * Generate a unique coupon code in format ABC-1234-XYZ
     */
    protected function generateCouponCode(): string
    {
        do {
            $part1 = strtoupper(Str::random(3)); // ABC
            $part2 = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT); // 1234
            $part3 = strtoupper(Str::random(3)); // XYZ
            $code = "{$part1}-{$part2}-{$part3}";
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
