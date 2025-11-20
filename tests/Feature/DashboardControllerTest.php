<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponValidation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_page_can_be_rendered()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->has('stats')
                ->has('recentActivity')
        );
    }

    public function test_dashboard_displays_active_coupons_count()
    {
        Coupon::factory()->count(5)->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->count(3)->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->where('stats.active_coupons', 5)
        );
    }

    public function test_dashboard_displays_used_today_count()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

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

        CouponValidation::factory()->create([
            'coupon_id' => Coupon::factory()->create(['created_by' => $this->user->id])->id,
            'validated_by' => $this->user->id,
            'validated_at' => $yesterday,
            'action' => 'used',
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->where('stats.used_today', 2)
        );
    }

    public function test_dashboard_displays_expiring_this_week_count()
    {
        $threeDaysFromNow = Carbon::today()->addDays(3);
        $eightDaysFromNow = Carbon::today()->addDays(8);

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
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => $eightDaysFromNow,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->where('stats.expiring_this_week', 2)
        );
    }

    public function test_dashboard_displays_total_coupons_count()
    {
        Coupon::factory()->count(10)->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->where('stats.total_coupons', 10)
        );
    }

    public function test_dashboard_displays_recent_activity()
    {
        $coupons = Coupon::factory()->count(5)->create([
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

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->has('recentActivity', 5)
                ->has('recentActivity.0', fn ($activity) =>
                    $activity->has('customer_name')
                        ->has('coupon_type')
                        ->has('coupon_code')
                        ->has('validated_by')
                        ->has('validated_at')
                        ->has('time_ago')
                )
        );
    }

    public function test_dashboard_limits_recent_activity_to_10_items()
    {
        $coupons = Coupon::factory()->count(15)->create([
            'created_by' => $this->user->id,
        ]);

        foreach ($coupons as $coupon) {
            CouponValidation::factory()->create([
                'coupon_id' => $coupon->id,
                'validated_by' => $this->user->id,
                'action' => 'used',
            ]);
        }

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->has('recentActivity', 10)
        );
    }

    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
