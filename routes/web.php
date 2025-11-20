<?php

use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
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
    
    // Scan route (placeholder - will be implemented in User Story 2.3)
    Route::get('/scan', function () {
        return Inertia::render('scan/Index');
    })->name('scan');
});

require __DIR__.'/settings.php';
