<?php

namespace Tests\Unit;

use App\Jobs\GenerateCouponCode;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_code_follows_abc_1234_xyz_format()
    {
        $user = User::factory()->create();
        
        $coupon = GenerateCouponCode::dispatchSync([
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        // Check format: ABC-1234-XYZ
        $this->assertMatchesRegularExpression('/^[A-Z]{3}-\d{4}-[A-Z]{3}$/', $coupon->code);
    }

    public function test_coupon_codes_are_unique()
    {
        $user = User::factory()->create();
        
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $coupon = GenerateCouponCode::dispatchSync([
                'type' => 'Test Coupon',
                'description' => 'Test Description',
                'customer_name' => 'Test User',
                'customer_phone' => '081234567890',
                'status' => Coupon::STATUS_ACTIVE,
                'created_by' => $user->id,
            ]);
            $codes[] = $coupon->code;
        }

        $uniqueCodes = array_unique($codes);
        $this->assertCount(10, $uniqueCodes, 'All coupon codes should be unique');
    }

    public function test_coupon_code_has_correct_length()
    {
        $user = User::factory()->create();
        
        $coupon = GenerateCouponCode::dispatchSync([
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        // Format: ABC-1234-XYZ = 3 + 1 + 4 + 1 + 3 = 12 characters
        $this->assertEquals(12, strlen($coupon->code));
    }

    public function test_coupon_code_has_dashes_in_correct_positions()
    {
        $user = User::factory()->create();
        
        $coupon = GenerateCouponCode::dispatchSync([
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        $parts = explode('-', $coupon->code);
        $this->assertCount(3, $parts);
        $this->assertEquals(3, strlen($parts[0])); // ABC
        $this->assertEquals(4, strlen($parts[1])); // 1234
        $this->assertEquals(3, strlen($parts[2])); // XYZ
    }

    public function test_coupon_code_first_part_is_uppercase_letters()
    {
        $user = User::factory()->create();
        
        $coupon = GenerateCouponCode::dispatchSync([
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        $parts = explode('-', $coupon->code);
        $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $parts[0]);
    }

    public function test_coupon_code_second_part_is_four_digits()
    {
        $user = User::factory()->create();
        
        $coupon = GenerateCouponCode::dispatchSync([
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        $parts = explode('-', $coupon->code);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $parts[1]);
    }

    public function test_coupon_code_third_part_is_uppercase_letters()
    {
        $user = User::factory()->create();
        
        $coupon = GenerateCouponCode::dispatchSync([
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        $parts = explode('-', $coupon->code);
        $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $parts[2]);
    }
}
