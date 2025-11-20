<?php

namespace App\Services;

use App\Helpers\PhoneHelper;
use App\Models\Coupon;
use App\Models\CouponValidation;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get summary statistics for a date range
     */
    public function getSummaryStats(Carbon $dateFrom, Carbon $dateTo): array
    {
        $totalCreated = Coupon::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $totalUsed = CouponValidation::where('action', 'used')
            ->whereBetween('validated_at', [$dateFrom, $dateTo])
            ->count();
        $redemptionRate = $totalCreated > 0 
            ? round(($totalUsed / $totalCreated) * 100, 2) 
            : 0;
        $currentlyActive = Coupon::where('status', Coupon::STATUS_ACTIVE)->count();
        $totalExpired = Coupon::where('status', Coupon::STATUS_EXPIRED)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        return [
            'total_created' => $totalCreated,
            'total_used' => $totalUsed,
            'redemption_rate' => $redemptionRate,
            'currently_active' => $currentlyActive,
            'total_expired' => $totalExpired,
        ];
    }

    /**
     * Get top coupon types with statistics
     */
    public function getTopTypes(Carbon $dateFrom, Carbon $dateTo, int $limit = 10): array
    {
        return Coupon::select('type')
            ->selectRaw('COUNT(*) as created_count')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as used_count', [Coupon::STATUS_USED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as expired_count', [Coupon::STATUS_EXPIRED])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('type')
            ->orderByDesc('created_count')
            ->limit($limit)
            ->get()
            ->map(function ($type) {
                $usageRate = $type->created_count > 0 
                    ? round(($type->used_count / $type->created_count) * 100, 2) 
                    : 0;
                
                return [
                    'type' => $type->type,
                    'created_count' => $type->created_count,
                    'used_count' => $type->used_count,
                    'expired_count' => $type->expired_count,
                    'usage_rate' => $usageRate,
                ];
            })
            ->toArray();
    }

    /**
     * Get daily usage statistics
     */
    public function getDailyUsage(Carbon $dateFrom, Carbon $dateTo): array
    {
        return CouponValidation::where('action', 'used')
            ->whereBetween('validated_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(validated_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($day) {
                return [
                    'date' => $day->date,
                    'count' => $day->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get frequent customers report
     */
    public function getFrequentCustomers(Carbon $dateFrom, Carbon $dateTo, int $limit = 20): array
    {
        return Coupon::select('customer_phone')
            ->selectRaw('MAX(customer_name) as customer_name')
            ->selectRaw('COUNT(*) as total_coupons')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_used', [Coupon::STATUS_USED])
            ->selectRaw('MAX(created_at) as last_coupon_date')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('customer_phone')
            ->orderByDesc('total_coupons')
            ->limit($limit)
            ->get()
            ->map(function ($customer) {
                $usageRate = $customer->total_coupons > 0 
                    ? round(($customer->total_used / $customer->total_coupons) * 100, 2) 
                    : 0;
                
                return [
                    'customer_name' => $customer->customer_name,
                    'customer_phone' => $customer->customer_phone,
                    'formatted_phone' => PhoneHelper::formatForDisplay($customer->customer_phone),
                    'total_coupons' => $customer->total_coupons,
                    'total_used' => $customer->total_used,
                    'usage_rate' => $usageRate,
                    'last_coupon_date' => $customer->last_coupon_date,
                ];
            })
            ->toArray();
    }
}
