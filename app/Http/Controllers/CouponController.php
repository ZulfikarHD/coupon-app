<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Coupon::with('user');

        // Apply filters using service
        $this->couponService->applyFilters($query, $request);

        // Apply sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort column to prevent SQL injection
        $allowedColumns = ['code', 'customer_name', 'customer_phone', 'type', 'status', 'created_at', 'expires_at'];
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'created_at';
        }
        
        // Validate sort direction
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';
        
        $query->orderBy($sortColumn, $sortDirection);

        $coupons = $query->paginate(20)->withQueryString();

        // Append formatted_phone to each coupon
        $coupons->getCollection()->transform(function ($coupon) {
            $coupon->formatted_phone = $coupon->formatted_phone;
            return $coupon;
        });

        return Inertia::render('coupons/Index', [
            'coupons' => $coupons,
            'filters' => $request->only([
                'status',
                'search',
                'customer_name',
                'customer_phone',
                'coupon_type',
                'date_from',
                'date_to',
                'expires_from',
                'expires_to',
                'sort',
                'direction',
            ]),
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

        $coupon = $this->couponService->create($validated);

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
        $result = $this->couponService->check($code);

        return response()->json(
            Arr::except($result, 'status_code'),
            $result['status_code']
        );
    }

    /**
     * Validate (mark as used) a coupon
     */
    public function validate(Request $request, string $code)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $result = $this->couponService->validate($code, $request->password);

        return response()->json(
            Arr::except($result, ['status_code', 'success']),
            $result['status_code']
        );
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

        $result = $this->couponService->reverse(
            (int) $id,
            $request->password,
            $request->reason
        );

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
