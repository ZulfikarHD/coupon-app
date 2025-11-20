<?php

namespace App\Http\Controllers;

use App\Exports\CouponExport;
use App\Models\Coupon;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

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

        // Pagination parameters
        $topTypesPage = $request->input('top_types_page', 1);
        $customersPage = $request->input('customers_page', 1);
        $perPage = $request->input('per_page', 10);

        return Inertia::render('reports/Index', [
            'summaryStats' => $this->reportService->getSummaryStats($dateFromCarbon, $dateToCarbon),
            'topTypes' => $this->reportService->getTopTypes($dateFromCarbon, $dateToCarbon, $request),
            'dailyUsage' => $this->reportService->getDailyUsage($dateFromCarbon, $dateToCarbon),
            'frequentCustomers' => $this->reportService->getFrequentCustomers($dateFromCarbon, $dateToCarbon, $request),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'top_types_page' => $topTypesPage,
                'customers_page' => $customersPage,
                'per_page' => $perPage,
                'top_types_sort' => $request->input('top_types_sort', 'created_count'),
                'top_types_direction' => $request->input('top_types_direction', 'desc'),
                'customers_sort' => $request->input('customers_sort', 'total_coupons'),
                'customers_direction' => $request->input('customers_direction', 'desc'),
            ],
        ]);
    }

    /**
     * Export coupons to Excel or CSV format.
     */
    public function export(Request $request): BinaryFileResponse
    {
        // Validate format
        $format = $request->input('format', 'xlsx');
        if (!in_array($format, ['xlsx', 'csv'])) {
            abort(400, 'Invalid format. Must be xlsx or csv.');
        }

        // Get date range (default to last 30 days)
        $dateFrom = $request->input('date_from', Carbon::today()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
        $dateToCarbon = Carbon::parse($dateTo)->endOfDay();

        // Check if dataset is large (> 5000 rows)
        $couponCount = Coupon::whereBetween('created_at', [$dateFromCarbon, $dateToCarbon])->count();

        // Generate filename with current date
        $filename = 'coupons_' . Carbon::today()->format('Y-m-d') . '.' . $format;

        // Create export instance
        $export = new CouponExport($dateFromCarbon, $dateToCarbon);

        // For large datasets, we could queue this, but for now we'll handle it synchronously
        // The frontend will show a loading state during the download
        if ($format === 'csv') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX);
    }
}
