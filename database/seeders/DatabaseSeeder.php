<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponValidation;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create regular user
        $user = User::factory()->create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
        ]);

        // Create additional users (5 regular users)
        $users = User::factory(5)->create();

        // Create coupons with various statuses
        // Active coupons
        $activeCoupons = Coupon::factory(15)
            ->active()
            ->create([
                'created_by' => $admin->id,
            ]);

        // Used coupons
        $usedCoupons = Coupon::factory(10)
            ->used()
            ->create([
                'created_by' => $admin->id,
            ]);

        // Expired coupons
        $expiredCoupons = Coupon::factory(5)
            ->expired()
            ->create([
                'created_by' => $admin->id,
            ]);

        // Create some coupons created by regular users
        Coupon::factory(10)
            ->active()
            ->create([
                'created_by' => $user->id,
            ]);

        // Create coupon validations for used coupons
        foreach ($usedCoupons->take(8) as $coupon) {
            CouponValidation::factory()->used()->create([
                'coupon_id' => $coupon->id,
                'validated_by' => $admin->id,
                'validated_at' => $coupon->updated_at,
            ]);
        }

        // Create some reversed validations
        foreach ($usedCoupons->skip(8)->take(2) as $coupon) {
            CouponValidation::factory()->reversed()->create([
                'coupon_id' => $coupon->id,
                'validated_by' => $admin->id,
                'validated_at' => now()->subDays(2),
            ]);
        }
    }
}
