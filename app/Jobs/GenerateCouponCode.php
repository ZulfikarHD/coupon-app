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
    public function handle(): void
    {
        $code = $this->generateUniqueCode();

        Coupon::create([
            'code' => $code,
            'type' => $this->couponData['type'],
            'description' => $this->couponData['description'],
            'customer_name' => $this->couponData['customer_name'],
            'customer_phone' => $this->couponData['customer_phone'],
            'customer_email' => $this->couponData['customer_email'] ?? null,
            'customer_social_media' => $this->couponData['customer_social_media'] ?? null,
            'expires_at' => $this->couponData['expires_at'] ?? null,
            'status' => $this->couponData['status'] ?? 'active',
            'created_by' => $this->couponData['created_by'],
        ]);
    }

    /**
     * Generate a unique coupon code.
     */
    protected function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
