<?php

use App\Http\Controllers\CouponController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Coupon API routes (protected with auth middleware)
Route::middleware(['auth:sanctum'])->group(function () {
    // Check coupon status before validation (SPRINT 2.4)
    Route::get('/coupons/{code}/check', [CouponController::class, 'check'])->name('api.coupons.check');
});
