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
    public function getTopTypes(Carbon $dateFrom, Carbon $dateTo, $request = null): array
    {
        $query = Coupon::select('type')
            ->selectRaw('COUNT(*) as created_count')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as used_count', [Coupon::STATUS_USED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as expired_count', [Coupon::STATUS_EXPIRED])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('type');

        // Apply sorting
        if ($request) {
            $sortColumn = $request->input('top_types_sort', 'created_count');
            $sortDirection = $request->input('top_types_direction', 'desc');
            
            $allowedColumns = ['type', 'created_count', 'used_count', 'expired_count', 'usage_rate'];
            if (in_array($sortColumn, $allowedColumns)) {
                if ($sortColumn === 'usage_rate') {
                    // For usage_rate, we need to calculate it in the orderBy
                    $query->orderByRaw('(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) ' . ($sortDirection === 'asc' ? 'ASC' : 'DESC'), [Coupon::STATUS_USED]);
                } else {
                    $query->orderBy($sortColumn, $sortDirection);
                }
            } else {
                $query->orderByDesc('created_count');
            }
        } else {
            $query->orderByDesc('created_count');
        }

        $results = $query->get()
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
            });

        // Apply pagination manually
        if ($request) {
            $page = (int) $request->input('top_types_page', 1);
            $perPage = (int) $request->input('per_page', 10);
            $total = $results->count();
            $items = $results->slice(($page - 1) * $perPage, $perPage)->values();
            
            return [
                'data' => $items->toArray(),
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
            ];
        }

        return $results->toArray();
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
    public function getFrequentCustomers(Carbon $dateFrom, Carbon $dateTo, $request = null): array
    {
        $query = Coupon::select('customer_phone')
            ->selectRaw('MAX(customer_name) as customer_name')
            ->selectRaw('COUNT(*) as total_coupons')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_used', [Coupon::STATUS_USED])
            ->selectRaw('MAX(created_at) as last_coupon_date')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('customer_phone');

        // Apply sorting
        if ($request) {
            $sortColumn = $request->input('customers_sort', 'total_coupons');
            $sortDirection = $request->input('customers_direction', 'desc');
            
            $allowedColumns = ['customer_name', 'customer_phone', 'total_coupons', 'total_used', 'usage_rate', 'last_coupon_date'];
            if (in_array($sortColumn, $allowedColumns)) {
                if ($sortColumn === 'usage_rate') {
                    // For usage_rate, we need to calculate it in the orderBy
                    $query->orderByRaw('(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) ' . ($sortDirection === 'asc' ? 'ASC' : 'DESC'), [Coupon::STATUS_USED]);
                } else {
                    $query->orderBy($sortColumn, $sortDirection);
                }
            } else {
                $query->orderByDesc('total_coupons');
            }
        } else {
            $query->orderByDesc('total_coupons');
        }

        $results = $query->get()
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
            });

        // Apply pagination manually
        if ($request) {
            $page = (int) $request->input('customers_page', 1);
            $perPage = (int) $request->input('per_page', 10);
            $total = $results->count();
            $items = $results->slice(($page - 1) * $perPage, $perPage)->values();
            
            return [
                'data' => $items->toArray(),
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
            ];
        }

        // Fallback: limit to 20 if no pagination requested
        return $results->take(20)->toArray();
    }
}
