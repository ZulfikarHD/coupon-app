<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Display the dashboard with stats and recent activity.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardService->getStats(),
            'recentActivity' => $this->dashboardService->getRecentActivity(),
        ]);
    }
}
