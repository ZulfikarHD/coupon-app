<?php

use App\Http\Controllers\CouponController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Coupon routes (protected)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('coupons', CouponController::class);
    
    // Public coupon view (no auth required)
    Route::get('/coupon/{code}', function ($code) {
        $coupon = \App\Models\Coupon::where('code', $code)->firstOrFail();
        return Inertia::render('coupons/Public', [
            'coupon' => $coupon,
        ]);
    })->name('coupons.public');
});

require __DIR__.'/settings.php';
