<?php

use App\Http\Controllers\CouponController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Note: Coupon check endpoint moved to web.php for session-based authentication
// API routes don't have session middleware, so web routes are better for
// endpoints called from authenticated web pages (like the scanner)
