<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponValidation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_reports_page_can_be_rendered()
    {
        $response = $this->actingAs($this->user)->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('reports/Index')
                ->has('summaryStats')
                ->has('topTypes')
                ->has('dailyUsage')
                ->has('frequentCustomers')
                ->has('filters')
        );
    }

    public function test_reports_page_uses_default_date_range_when_not_provided()
    {
        $response = $this->actingAs($this->user)->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('reports/Index')
                ->has('filters.date_from')
                ->has('filters.date_to')
        );
    }

    public function test_reports_page_filters_by_date_range()
    {
        $dateFrom = Carbon::today()->subDays(10)->format('Y-m-d');
        $dateTo = Carbon::today()->format('Y-m-d');

        Coupon::factory()->count(5)->create([
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        Coupon::factory()->count(3)->create([
            'created_at' => Carbon::today()->subDays(15), // Outside range
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/reports?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('reports/Index')
                ->where('summaryStats.total_created', 5)
                ->where('filters.date_from', $dateFrom)
                ->where('filters.date_to', $dateTo)
        );
    }

    public function test_reports_page_displays_summary_statistics()
    {
        $coupons = Coupon::factory()->count(10)->create([
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        // Mark 3 as used
        foreach ($coupons->take(3) as $coupon) {
            $coupon->status = Coupon::STATUS_USED;
            $coupon->save();
            CouponValidation::factory()->create([
                'coupon_id' => $coupon->id,
                'validated_at' => Carbon::today()->subDays(2),
                'action' => 'used',
            ]);
        }

        $response = $this->actingAs($this->user)->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('reports/Index')
                ->where('summaryStats.total_created', 10)
                ->where('summaryStats.total_used', 3)
                ->where('summaryStats.redemption_rate', 30.0)
        );
    }

    public function test_reports_page_displays_top_types()
    {
        Coupon::factory()->count(5)->create([
            'type' => 'Type A',
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->count(3)->create([
            'type' => 'Type B',
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('reports/Index')
                ->has('topTypes', 2)
                ->where('topTypes.0.type', 'Type A')
                ->where('topTypes.0.created_count', 5)
        );
    }

    public function test_reports_page_displays_daily_usage()
    {
        $coupon1 = Coupon::factory()->create(['created_by' => $this->user->id]);
        $coupon2 = Coupon::factory()->create(['created_by' => $this->user->id]);

        CouponValidation::factory()->create([
            'coupon_id' => $coupon1->id,
            'validated_at' => Carbon::today()->subDays(2),
            'action' => 'used',
        ]);
        CouponValidation::factory()->create([
            'coupon_id' => $coupon2->id,
            'validated_at' => Carbon::today()->subDays(2),
            'action' => 'used',
        ]);

        $response = $this->actingAs($this->user)->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('reports/Index')
                ->has('dailyUsage')
        );
    }

    public function test_reports_page_displays_frequent_customers()
    {
        Coupon::factory()->count(5)->create([
            'customer_name' => 'John Doe',
            'customer_phone' => '6281234567890',
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('reports/Index')
                ->has('frequentCustomers')
                ->has('frequentCustomers.0', fn ($customer) =>
                    $customer->has('customer_name')
                        ->has('customer_phone')
                        ->has('formatted_phone')
                        ->has('total_coupons')
                        ->has('total_used')
                        ->has('usage_rate')
                )
        );
    }

    public function test_export_endpoint_validates_format()
    {
        $response = $this->actingAs($this->user)->get('/reports/export?format=invalid');

        $response->assertStatus(400);
    }

    public function test_export_endpoint_exports_xlsx_format()
    {
        Coupon::factory()->count(5)->create([
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/reports/export?format=xlsx');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('coupons_', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_export_endpoint_exports_csv_format()
    {
        Coupon::factory()->count(5)->create([
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/reports/export?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('coupons_', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));
    }

    public function test_export_endpoint_uses_default_date_range()
    {
        Coupon::factory()->count(5)->create([
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/reports/export?format=xlsx');

        $response->assertStatus(200);
    }

    public function test_export_endpoint_filters_by_date_range()
    {
        $dateFrom = Carbon::today()->subDays(10)->format('Y-m-d');
        $dateTo = Carbon::today()->format('Y-m-d');

        Coupon::factory()->count(5)->create([
            'created_at' => Carbon::today()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        Coupon::factory()->count(3)->create([
            'created_at' => Carbon::today()->subDays(15), // Outside range
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/reports/export?format=xlsx&date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_reports_page()
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_export_endpoint()
    {
        $response = $this->get('/reports/export?format=xlsx');

        $response->assertRedirect('/login');
    }
}
