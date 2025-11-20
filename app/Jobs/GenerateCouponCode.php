<?php

namespace App\Jobs;

use App\Models\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GenerateCouponCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $couponData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $couponData)
    {
        $this->couponData = $couponData;
    }

    /**
     * Execute the job.
     */
    public function handle(): Coupon
    {
        $code = $this->generateUniqueCode();

        return Coupon::create([
            'code' => $code,
            'type' => $this->couponData['type'],
            'description' => $this->couponData['description'],
            'customer_name' => $this->couponData['customer_name'],
            'first_name' => $this->couponData['first_name'] ?? null,
            'last_name' => $this->couponData['last_name'] ?? null,
            'customer_phone' => $this->couponData['customer_phone'],
            'customer_email' => $this->couponData['customer_email'] ?? null,
            'customer_social_media' => $this->couponData['customer_social_media'] ?? null,
            'expires_at' => $this->couponData['expires_at'] ?? null,
            'status' => $this->couponData['status'] ?? 'active',
            'created_by' => $this->couponData['created_by'],
        ]);
    }

    /**
     * Generate a unique coupon code in format ABC-1234-XYZ
     */
    protected function generateUniqueCode(): string
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
