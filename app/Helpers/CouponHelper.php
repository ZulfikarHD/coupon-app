<?php

use App\Models\Coupon;
use Illuminate\Support\Str;

if (!function_exists('generateCouponCode')) {
    /**
     * Generate a unique coupon code in format ABC-1234-XYZ
     */
    function generateCouponCode(): string
    {
        do {
            // Format: ABC-1234-XYZ
            $part1 = strtoupper(Str::random(3)); // ABC
            $part2 = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT); // 1234
            $part3 = strtoupper(Str::random(3)); // XYZ
            $code = "{$part1}-{$part2}-{$part3}";
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
