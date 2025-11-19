<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_normalization_from_08xx_format()
    {
        $coupon = new Coupon();
        $coupon->customer_phone = '081234567890';

        $this->assertEquals('6281234567890', $coupon->customer_phone);
    }

    public function test_phone_normalization_from_plus_62_format()
    {
        $coupon = new Coupon();
        $coupon->customer_phone = '+6281234567890';

        $this->assertEquals('6281234567890', $coupon->customer_phone);
    }

    public function test_phone_normalization_from_62_format()
    {
        $coupon = new Coupon();
        $coupon->customer_phone = '6281234567890';

        $this->assertEquals('6281234567890', $coupon->customer_phone);
    }

    public function test_phone_normalization_removes_non_numeric_characters()
    {
        $coupon = new Coupon();
        $coupon->customer_phone = '0812-3456-7890';

        $this->assertEquals('6281234567890', $coupon->customer_phone);
    }

    public function test_formatted_phone_displays_correctly()
    {
        $coupon = Coupon::factory()->create([
            'customer_phone' => '6281234567890',
        ]);

        $formatted = $coupon->formatted_phone;
        $this->assertStringStartsWith('0812', $formatted);
        $this->assertStringContainsString('-', $formatted);
    }

    public function test_active_scope_returns_only_active_coupons()
    {
        $user = User::factory()->create();
        
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_EXPIRED,
            'created_by' => $user->id,
        ]);

        $activeCoupons = Coupon::active()->get();

        $this->assertCount(1, $activeCoupons);
        $this->assertEquals(Coupon::STATUS_ACTIVE, $activeCoupons->first()->status);
    }

    public function test_used_scope_returns_only_used_coupons()
    {
        $user = User::factory()->create();
        
        Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        $usedCoupons = Coupon::used()->get();

        $this->assertCount(1, $usedCoupons);
        $this->assertEquals(Coupon::STATUS_USED, $usedCoupons->first()->status);
    }

    public function test_expired_scope_returns_only_expired_coupons()
    {
        $user = User::factory()->create();
        
        Coupon::factory()->create([
            'status' => Coupon::STATUS_EXPIRED,
            'created_by' => $user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        $expiredCoupons = Coupon::expired()->get();

        $this->assertCount(1, $expiredCoupons);
        $this->assertEquals(Coupon::STATUS_EXPIRED, $expiredCoupons->first()->status);
    }

    public function test_coupon_belongs_to_user()
    {
        $user = User::factory()->create();
        $coupon = Coupon::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $coupon->user);
        $this->assertEquals($user->id, $coupon->user->id);
    }

    public function test_coupon_has_many_validations()
    {
        $coupon = Coupon::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $coupon->validations());
    }

    public function test_can_be_validated_returns_true_for_active_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
        ]);

        $this->assertTrue($coupon->canBeValidated());
    }

    public function test_can_be_validated_returns_false_for_used_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
        ]);

        $this->assertFalse($coupon->canBeValidated());
    }

    public function test_can_be_validated_returns_false_for_expired_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($coupon->canBeValidated());
    }

    public function test_can_be_validated_returns_false_for_expired_status()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_EXPIRED,
        ]);

        $this->assertFalse($coupon->canBeValidated());
    }
}
