<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\CouponValidation;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardService $dashboardService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = new DashboardService();
        $this->user = User::factory()->create();
    }

    public function test_get_stats_returns_active_coupons_count()
    {
        Coupon::factory()->count(5)->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->count(3)->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $stats = $this->dashboardService->getStats();

        $this->assertEquals(5, $stats['active_coupons']);
    }

    public function test_get_stats_returns_used_today_count()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Create validations for today
        $coupon1 = Coupon::factory()->create(['created_by' => $this->user->id]);
        $coupon2 = Coupon::factory()->create(['created_by' => $this->user->id]);
        
        CouponValidation::factory()->create([
            'coupon_id' => $coupon1->id,
            'validated_by' => $this->user->id,
            'validated_at' => $today,
            'action' => 'used',
        ]);
        CouponValidation::factory()->create([
            'coupon_id' => $coupon2->id,
            'validated_by' => $this->user->id,
            'validated_at' => $today,
            'action' => 'used',
        ]);

        // Create validation for yesterday (should not be counted)
        $coupon3 = Coupon::factory()->create(['created_by' => $this->user->id]);
        CouponValidation::factory()->create([
            'coupon_id' => $coupon3->id,
            'validated_by' => $this->user->id,
            'validated_at' => $yesterday,
            'action' => 'used',
        ]);

        $stats = $this->dashboardService->getStats();

        $this->assertEquals(2, $stats['used_today']);
    }

    public function test_get_stats_returns_expiring_this_week_count()
    {
        $today = Carbon::today();
        $threeDaysFromNow = Carbon::today()->addDays(3);
        $eightDaysFromNow = Carbon::today()->addDays(8);

        // Coupons expiring this week
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => $threeDaysFromNow,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => Carbon::today()->addDays(7),
            'created_by' => $this->user->id,
        ]);

        // Coupon expiring after this week (should not be counted)
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => $eightDaysFromNow,
            'created_by' => $this->user->id,
        ]);

        // Used coupon (should not be counted)
        Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'expires_at' => $threeDaysFromNow,
            'created_by' => $this->user->id,
        ]);

        // Coupon without expiration (should not be counted)
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => null,
            'created_by' => $this->user->id,
        ]);

        $stats = $this->dashboardService->getStats();

        $this->assertEquals(2, $stats['expiring_this_week']);
    }

    public function test_get_stats_returns_total_coupons_count()
    {
        Coupon::factory()->count(10)->create([
            'created_by' => $this->user->id,
        ]);

        $stats = $this->dashboardService->getStats();

        $this->assertEquals(10, $stats['total_coupons']);
    }

    public function test_get_recent_activity_returns_last_10_validations()
    {
        $coupons = Coupon::factory()->count(12)->create([
            'created_by' => $this->user->id,
        ]);

        foreach ($coupons as $coupon) {
            CouponValidation::factory()->create([
                'coupon_id' => $coupon->id,
                'validated_by' => $this->user->id,
                'validated_at' => now()->subMinutes(rand(1, 60)),
                'action' => 'used',
            ]);
        }

        $activity = $this->dashboardService->getRecentActivity();

        $this->assertCount(10, $activity);
        $this->assertArrayHasKey('customer_name', $activity[0]);
        $this->assertArrayHasKey('coupon_type', $activity[0]);
        $this->assertArrayHasKey('coupon_code', $activity[0]);
        $this->assertArrayHasKey('validated_by', $activity[0]);
        $this->assertArrayHasKey('validated_at', $activity[0]);
        $this->assertArrayHasKey('time_ago', $activity[0]);
    }

    public function test_get_recent_activity_orders_by_most_recent_first()
    {
        $coupon1 = Coupon::factory()->create(['created_by' => $this->user->id]);
        $coupon2 = Coupon::factory()->create(['created_by' => $this->user->id]);

        $validation1 = CouponValidation::factory()->create([
            'coupon_id' => $coupon1->id,
            'validated_by' => $this->user->id,
            'validated_at' => now()->subHour(),
            'action' => 'used',
        ]);

        $validation2 = CouponValidation::factory()->create([
            'coupon_id' => $coupon2->id,
            'validated_by' => $this->user->id,
            'validated_at' => now()->subMinutes(30),
            'action' => 'used',
        ]);

        $activity = $this->dashboardService->getRecentActivity();

        // Most recent should be first
        $this->assertEquals($validation2->id, $activity[0]['id']);
        $this->assertEquals($validation1->id, $activity[1]['id']);
    }

    public function test_get_recent_activity_only_includes_used_action()
    {
        $coupon = Coupon::factory()->create(['created_by' => $this->user->id]);

        CouponValidation::factory()->create([
            'coupon_id' => $coupon->id,
            'validated_by' => $this->user->id,
            'action' => 'used',
        ]);

        CouponValidation::factory()->create([
            'coupon_id' => $coupon->id,
            'validated_by' => $this->user->id,
            'action' => 'reversed',
        ]);

        $activity = $this->dashboardService->getRecentActivity();

        $this->assertCount(1, $activity);
        $this->assertEquals('used', CouponValidation::find($activity[0]['id'])->action);
    }

    public function test_get_recent_activity_handles_custom_limit()
    {
        $coupons = Coupon::factory()->count(5)->create([
            'created_by' => $this->user->id,
        ]);

        foreach ($coupons as $coupon) {
            CouponValidation::factory()->create([
                'coupon_id' => $coupon->id,
                'validated_by' => $this->user->id,
                'action' => 'used',
            ]);
        }

        $activity = $this->dashboardService->getRecentActivity(3);

        $this->assertCount(3, $activity);
    }

    public function test_get_recent_activity_includes_formatted_data()
    {
        $coupon = Coupon::factory()->create([
            'customer_name' => 'John Doe',
            'type' => 'Gratis 1 Kopi',
            'code' => 'ABC-1234-XYZ',
            'created_by' => $this->user->id,
        ]);

        $validator = User::factory()->create(['name' => 'Validator User']);

        CouponValidation::factory()->create([
            'coupon_id' => $coupon->id,
            'validated_by' => $validator->id,
            'action' => 'used',
        ]);

        $activity = $this->dashboardService->getRecentActivity();

        $this->assertEquals('John Doe', $activity[0]['customer_name']);
        $this->assertEquals('Gratis 1 Kopi', $activity[0]['coupon_type']);
        $this->assertEquals('ABC-1234-XYZ', $activity[0]['coupon_code']);
        $this->assertEquals('Validator User', $activity[0]['validated_by']);
        $this->assertArrayHasKey('time_ago', $activity[0]);
    }
}
