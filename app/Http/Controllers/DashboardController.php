<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponValidation;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with stats and recent activity.
     */
    public function index(): Response
    {
        $today = Carbon::today();
        $weekFromNow = Carbon::today()->addDays(7);

        // Efficient queries for stats
        $stats = [
            'active_coupons' => Coupon::where('status', Coupon::STATUS_ACTIVE)->count(),
            'used_today' => CouponValidation::whereDate('validated_at', $today)
                ->where('action', 'used')
                ->count(),
            'expiring_this_week' => Coupon::where('status', Coupon::STATUS_ACTIVE)
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [$today, $weekFromNow])
                ->count(),
            'total_coupons' => Coupon::count(),
        ];

        // Recent activity: Last 10 coupon validations with eager loading
        $recentActivity = CouponValidation::with(['coupon', 'validator'])
            ->where('action', 'used')
            ->whereHas('coupon') // Only include validations with existing coupons
            ->orderBy('validated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($validation) {
                return [
                    'id' => $validation->id,
                    'customer_name' => $validation->coupon->customer_name ?? 'Unknown',
                    'coupon_type' => $validation->coupon->type ?? 'N/A',
                    'coupon_code' => $validation->coupon->code ?? 'N/A',
                    'validated_by' => $validation->validator->name ?? 'Unknown',
                    'validated_at' => $validation->validated_at->toIso8601String(),
                    'time_ago' => $validation->validated_at->diffForHumans(),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }
}
