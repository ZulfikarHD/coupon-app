<?php

namespace App\Services;

use App\Helpers\PhoneHelper;
use App\Jobs\GenerateCouponCode;
use App\Models\Coupon;
use App\Models\CouponValidation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CouponService
{
    /**
     * Apply filters to coupon query
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        // Status filter (can be array for multi-select)
        $statusArray = $this->parseStatusFilter($request);
        if (!empty($statusArray)) {
            $query->whereIn('status', $statusArray);
        }

        // Basic search filter (code, name, phone, type)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Advanced filters
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', "%{$request->customer_name}%");
        }

        if ($request->filled('first_name')) {
            $query->where('first_name', 'like', "%{$request->first_name}%");
        }

        if ($request->filled('last_name')) {
            $query->where('last_name', 'like', "%{$request->last_name}%");
        }

        if ($request->filled('customer_phone')) {
            $phone = PhoneHelper::normalizeForSearch($request->customer_phone);
            $query->where('customer_phone', 'like', "%{$phone}%");
        }

        if ($request->filled('coupon_type')) {
            $query->where('type', 'like', "%{$request->coupon_type}%");
        }

        // Created date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Expires date range filter
        if ($request->filled('expires_from')) {
            $query->whereDate('expires_at', '>=', $request->expires_from);
        }
        if ($request->filled('expires_to')) {
            $query->whereDate('expires_at', '<=', $request->expires_to);
        }

        return $query;
    }

    /**
     * Parse status filter from request (handles multiple status values)
     */
    protected function parseStatusFilter(Request $request): array
    {
        $statusInput = $request->input('status');

        if (!$statusInput) {
            return [];
        }

        // Convert to array if it's a single value
        $statusArray = is_array($statusInput) ? $statusInput : [$statusInput];

        // Filter out 'all' and empty values
        $statusArray = array_filter(array_map('trim', $statusArray), function($s) {
            return $s !== 'all' && !empty($s);
        });

        return array_values($statusArray);
    }

    /**
     * Create a new coupon
     */
    public function create(array $data): Coupon
    {
        // Normalize phone number
        $data['customer_phone'] = PhoneHelper::normalize($data['customer_phone']);
        $data['status'] = Coupon::STATUS_ACTIVE;
        $data['created_by'] = Auth::id();

        // Create customer_name from first_name and last_name if provided
        if (isset($data['first_name']) && isset($data['last_name'])) {
            $data['customer_name'] = trim($data['first_name'] . ' ' . $data['last_name']);
        }

        // Use Job to create coupon synchronously
        GenerateCouponCode::dispatchSync($data);

        // Get the created coupon - use multiple fallback strategies
        $coupon = Coupon::where('created_by', Auth::id())
            ->where('first_name', $data['first_name'])
            ->where('last_name', $data['last_name'])
            ->where('customer_phone', $data['customer_phone'])
            ->where('type', $data['type'])
            ->latest('id')
            ->first();

        // Fallback: if not found, get the latest coupon created by this user
        if (!$coupon) {
            $coupon = Coupon::where('created_by', Auth::id())
                ->latest('id')
                ->firstOrFail();
        }

        return $coupon;
    }

    /**
     * Validate (mark as used) a coupon
     */
    public function validate(string $code, string $password): array
    {
        // Extract code from URL if full URL is provided
        $code = $this->extractCodeFromUrl($code);

        // Verify password
        if (!Hash::check($password, Auth::user()->password)) {
            return [
                'success' => false,
                'message' => 'Password salah',
                'status_code' => 401,
            ];
        }

        $coupon = Coupon::where('code', $code)->firstOrFail();

        // Verify coupon can be validated
        if (!$coupon->canBeValidated()) {
            $message = $this->getValidationErrorMessage($coupon);
            return [
                'success' => false,
                'message' => $message,
                'status_code' => 422,
            ];
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

        return [
            'success' => true,
            'message' => 'Kupon berhasil divalidasi',
            'coupon' => [
                'code' => $coupon->code,
                'status' => $coupon->status,
            ],
            'status_code' => 200,
        ];
    }

    /**
     * Reverse (cancel) a coupon validation
     */
    public function reverse(int $couponId, string $password, string $reason): array
    {
        // Verify password
        if (!Hash::check($password, Auth::user()->password)) {
            return [
                'success' => false,
                'message' => 'Password salah',
            ];
        }

        $coupon = Coupon::findOrFail($couponId);

        // Verify coupon status is 'used'
        if ($coupon->status !== Coupon::STATUS_USED) {
            return [
                'success' => false,
                'message' => 'Hanya kupon yang sudah digunakan yang dapat dibatalkan',
            ];
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
            'notes' => $reason,
        ]);

        return [
            'success' => true,
            'message' => 'Penggunaan kupon berhasil dibatalkan',
        ];
    }

    /**
     * Check coupon status before validation
     */
    public function check(string $code): array
    {
        // Extract code from URL if full URL is provided
        $code = $this->extractCodeFromUrl($code);

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return [
                'exists' => false,
                'message' => 'Kupon tidak ditemukan',
                'status_code' => 404,
            ];
        }

        $canValidate = $coupon->canBeValidated();
        $message = '';

        if (!$canValidate) {
            $message = $this->getValidationErrorMessage($coupon);
        }

        return [
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
            'status_code' => $canValidate ? 200 : 422,
        ];
    }

    /**
     * Get validation error message based on coupon status
     */
    protected function getValidationErrorMessage(Coupon $coupon): string
    {
        if ($coupon->status === Coupon::STATUS_USED) {
            return 'Kupon sudah digunakan';
        }

        if ($coupon->status === Coupon::STATUS_EXPIRED) {
            return 'Kupon sudah kedaluwarsa';
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return 'Kupon sudah kedaluwarsa';
        }

        return 'Kupon tidak dapat divalidasi';
    }

    /**
     * Extract coupon code from URL
     */
    protected function extractCodeFromUrl(string $input): string
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
