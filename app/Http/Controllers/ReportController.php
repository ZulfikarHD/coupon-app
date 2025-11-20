<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard with analytics.
     */
    public function index(Request $request): Response
    {
        // Default to last 30 days if no date range provided
        $dateFrom = $request->input('date_from', Carbon::today()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
        $dateToCarbon = Carbon::parse($dateTo)->endOfDay();

        // Summary Stats
        $totalCreated = Coupon::whereBetween('created_at', [$dateFromCarbon, $dateToCarbon])
            ->count();

        $totalUsed = CouponValidation::where('action', 'used')
            ->whereBetween('validated_at', [$dateFromCarbon, $dateToCarbon])
            ->count();

        $redemptionRate = $totalCreated > 0 
            ? round(($totalUsed / $totalCreated) * 100, 2) 
            : 0;

        $currentlyActive = Coupon::where('status', Coupon::STATUS_ACTIVE)
            ->count();

        $totalExpired = Coupon::where('status', Coupon::STATUS_EXPIRED)
            ->whereBetween('created_at', [$dateFromCarbon, $dateToCarbon])
            ->count();

        // Top Coupon Types
        $topTypes = Coupon::select('type')
            ->selectRaw('COUNT(*) as created_count')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as used_count', [Coupon::STATUS_USED])
            ->whereBetween('created_at', [$dateFromCarbon, $dateToCarbon])
            ->groupBy('type')
            ->orderByDesc('created_count')
            ->limit(10)
            ->get()
            ->map(function ($type) {
                $usageRate = $type->created_count > 0 
                    ? round(($type->used_count / $type->created_count) * 100, 2) 
                    : 0;
                
                return [
                    'type' => $type->type,
                    'created_count' => $type->created_count,
                    'used_count' => $type->used_count,
                    'usage_rate' => $usageRate,
                ];
            });

        // Daily Usage Chart Data (optional - for future chart implementation)
        $dailyUsage = CouponValidation::where('action', 'used')
            ->whereBetween('validated_at', [$dateFromCarbon, $dateToCarbon])
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
            });

        return Inertia::render('reports/Index', [
            'summaryStats' => [
                'total_created' => $totalCreated,
                'total_used' => $totalUsed,
                'redemption_rate' => $redemptionRate,
                'currently_active' => $currentlyActive,
                'total_expired' => $totalExpired,
            ],
            'topTypes' => $topTypes,
            'dailyUsage' => $dailyUsage,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
