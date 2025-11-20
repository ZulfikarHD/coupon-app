<?php

use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('reports', [ReportController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('reports.index');

Route::get('reports/export', [ReportController::class, 'export'])
    ->middleware(['auth', 'verified'])
    ->name('reports.export');

// Public coupon view (no auth required)
Route::get('/coupon/{code}', function ($code) {
    $coupon = \App\Models\Coupon::with(['validations' => function ($query) {
        $query->where('action', 'used')->latest('validated_at')->limit(1);
    }])->where('code', $code)->firstOrFail();
    return Inertia::render('coupons/Public', [
        'coupon' => $coupon,
    ]);
})->name('coupons.public');

// Coupon routes (protected)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('coupons', CouponController::class);

    // Scan route
    Route::get('/scan', function () {
        return Inertia::render('scan/Index');
    })->name('scan');

    // API endpoint for coupon check (in web routes for session auth)
    // Called by scanner page - needs session middleware
    Route::get('/api/coupons/{code}/check', [CouponController::class, 'check'])->name('api.coupons.check');
    
    // Validation route (web route for CSRF protection)
    Route::post('/coupons/{code}/validate', [CouponController::class, 'validate'])->name('coupons.validate');
    
    // Reversal route (cancel coupon usage)
    Route::post('/coupons/{id}/reverse', [CouponController::class, 'reverse'])->name('coupons.reverse');
});

require __DIR__.'/settings.php';
