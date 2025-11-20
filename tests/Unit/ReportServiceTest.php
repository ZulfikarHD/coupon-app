<?php

namespace Tests\Unit;

use App\Helpers\PhoneHelper;
use App\Models\Coupon;
use App\Models\CouponValidation;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReportService $reportService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = new ReportService();
        $this->user = User::factory()->create();
    }

    public function test_get_summary_stats_returns_total_created()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        Coupon::factory()->count(5)->create([
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        Coupon::factory()->count(3)->create([
            'created_at' => Carbon::today()->subDays(40), // Outside range
            'created_by' => $this->user->id,
        ]);

        $stats = $this->reportService->getSummaryStats($dateFrom, $dateTo);

        $this->assertEquals(5, $stats['total_created']);
    }

    public function test_get_summary_stats_returns_total_used()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        $coupon1 = Coupon::factory()->create(['created_by' => $this->user->id]);
        $coupon2 = Coupon::factory()->create(['created_by' => $this->user->id]);
        $coupon3 = Coupon::factory()->create(['created_by' => $this->user->id]);

        CouponValidation::factory()->create([
            'coupon_id' => $coupon1->id,
            'validated_at' => Carbon::today()->subDays(10),
            'action' => 'used',
        ]);
        CouponValidation::factory()->create([
            'coupon_id' => $coupon2->id,
            'validated_at' => Carbon::today()->subDays(5),
            'action' => 'used',
        ]);
        CouponValidation::factory()->create([
            'coupon_id' => $coupon3->id,
            'validated_at' => Carbon::today()->subDays(40), // Outside range
            'action' => 'used',
        ]);

        $stats = $this->reportService->getSummaryStats($dateFrom, $dateTo);

        $this->assertEquals(2, $stats['total_used']);
    }

    public function test_get_summary_stats_calculates_redemption_rate()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        $coupons = Coupon::factory()->count(10)->create([
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        // Use 3 coupons
        foreach ($coupons->take(3) as $coupon) {
            CouponValidation::factory()->create([
                'coupon_id' => $coupon->id,
                'validated_at' => Carbon::today()->subDays(5),
                'action' => 'used',
            ]);
        }

        $stats = $this->reportService->getSummaryStats($dateFrom, $dateTo);

        $this->assertEquals(30.0, $stats['redemption_rate']); // 3/10 * 100
    }

    public function test_get_summary_stats_returns_zero_redemption_rate_when_no_coupons()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        $stats = $this->reportService->getSummaryStats($dateFrom, $dateTo);

        $this->assertEquals(0, $stats['redemption_rate']);
    }

    public function test_get_summary_stats_returns_currently_active_count()
    {
        Coupon::factory()->count(5)->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->count(3)->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        $stats = $this->reportService->getSummaryStats($dateFrom, $dateTo);

        $this->assertEquals(5, $stats['currently_active']);
    }

    public function test_get_summary_stats_returns_total_expired()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        Coupon::factory()->count(3)->create([
            'status' => Coupon::STATUS_EXPIRED,
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        Coupon::factory()->count(2)->create([
            'status' => Coupon::STATUS_EXPIRED,
            'created_at' => Carbon::today()->subDays(40), // Outside range
            'created_by' => $this->user->id,
        ]);

        $stats = $this->reportService->getSummaryStats($dateFrom, $dateTo);

        $this->assertEquals(3, $stats['total_expired']);
    }

    public function test_get_top_types_returns_types_ordered_by_created_count()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        Coupon::factory()->count(5)->create([
            'type' => 'Type A',
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->count(3)->create([
            'type' => 'Type B',
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        $topTypes = $this->reportService->getTopTypes($dateFrom, $dateTo);

        $this->assertEquals('Type A', $topTypes[0]['type']);
        $this->assertEquals(5, $topTypes[0]['created_count']);
        $this->assertEquals('Type B', $topTypes[1]['type']);
        $this->assertEquals(3, $topTypes[1]['created_count']);
    }

    public function test_get_top_types_includes_used_and_expired_counts()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        $coupons = Coupon::factory()->count(10)->create([
            'type' => 'Type A',
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        // Mark 3 as used
        foreach ($coupons->take(3) as $coupon) {
            $coupon->status = Coupon::STATUS_USED;
            $coupon->save();
            CouponValidation::factory()->create([
                'coupon_id' => $coupon->id,
                'action' => 'used',
            ]);
        }

        // Mark 2 as expired
        foreach ($coupons->skip(3)->take(2) as $coupon) {
            $coupon->status = Coupon::STATUS_EXPIRED;
            $coupon->save();
        }

        $topTypes = $this->reportService->getTopTypes($dateFrom, $dateTo);

        $this->assertEquals(10, $topTypes[0]['created_count']);
        $this->assertEquals(3, $topTypes[0]['used_count']);
        $this->assertEquals(2, $topTypes[0]['expired_count']);
    }

    public function test_get_top_types_calculates_usage_rate()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        $coupons = Coupon::factory()->count(10)->create([
            'type' => 'Type A',
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        // Mark 4 as used
        foreach ($coupons->take(4) as $coupon) {
            $coupon->status = Coupon::STATUS_USED;
            $coupon->save();
            CouponValidation::factory()->create([
                'coupon_id' => $coupon->id,
                'action' => 'used',
            ]);
        }

        $topTypes = $this->reportService->getTopTypes($dateFrom, $dateTo);

        $this->assertEquals(40.0, $topTypes[0]['usage_rate']); // 4/10 * 100
    }

    public function test_get_top_types_respects_limit()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        foreach (['Type A', 'Type B', 'Type C', 'Type D', 'Type E'] as $type) {
            Coupon::factory()->count(2)->create([
                'type' => $type,
                'created_at' => Carbon::today()->subDays(10),
                'created_by' => $this->user->id,
            ]);
        }

        $topTypes = $this->reportService->getTopTypes($dateFrom, $dateTo, 3);

        $this->assertCount(3, $topTypes);
    }

    public function test_get_daily_usage_returns_daily_counts()
    {
        $dateFrom = Carbon::today()->subDays(7);
        $dateTo = Carbon::today();

        $coupon1 = Coupon::factory()->create(['created_by' => $this->user->id]);
        $coupon2 = Coupon::factory()->create(['created_by' => $this->user->id]);
        $coupon3 = Coupon::factory()->create(['created_by' => $this->user->id]);

        $date1 = Carbon::today()->subDays(2);
        $date2 = Carbon::today()->subDays(1);

        CouponValidation::factory()->create([
            'coupon_id' => $coupon1->id,
            'validated_at' => $date1,
            'action' => 'used',
        ]);
        CouponValidation::factory()->create([
            'coupon_id' => $coupon2->id,
            'validated_at' => $date1,
            'action' => 'used',
        ]);
        CouponValidation::factory()->create([
            'coupon_id' => $coupon3->id,
            'validated_at' => $date2,
            'action' => 'used',
        ]);

        $dailyUsage = $this->reportService->getDailyUsage($dateFrom, $dateTo);

        $this->assertCount(2, $dailyUsage);
        
        // Find entries by date
        $date1Entry = collect($dailyUsage)->firstWhere('date', $date1->format('Y-m-d'));
        $date2Entry = collect($dailyUsage)->firstWhere('date', $date2->format('Y-m-d'));

        $this->assertEquals(2, $date1Entry['count']);
        $this->assertEquals(1, $date2Entry['count']);
    }

    public function test_get_daily_usage_orders_by_date()
    {
        $dateFrom = Carbon::today()->subDays(7);
        $dateTo = Carbon::today();

        $coupon1 = Coupon::factory()->create(['created_by' => $this->user->id]);
        $coupon2 = Coupon::factory()->create(['created_by' => $this->user->id]);

        CouponValidation::factory()->create([
            'coupon_id' => $coupon1->id,
            'validated_at' => Carbon::today()->subDays(1),
            'action' => 'used',
        ]);
        CouponValidation::factory()->create([
            'coupon_id' => $coupon2->id,
            'validated_at' => Carbon::today()->subDays(3),
            'action' => 'used',
        ]);

        $dailyUsage = $this->reportService->getDailyUsage($dateFrom, $dateTo);

        $this->assertLessThan($dailyUsage[1]['date'], $dailyUsage[0]['date']);
    }

    public function test_get_frequent_customers_returns_customers_ordered_by_total_coupons()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        // Customer 1: 5 coupons
        Coupon::factory()->count(5)->create([
            'customer_name' => 'John Doe',
            'customer_phone' => '6281234567890',
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        // Customer 2: 3 coupons
        Coupon::factory()->count(3)->create([
            'customer_name' => 'Jane Smith',
            'customer_phone' => '6289876543210',
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        $customers = $this->reportService->getFrequentCustomers($dateFrom, $dateTo);

        $this->assertEquals('John Doe', $customers[0]['customer_name']);
        $this->assertEquals(5, $customers[0]['total_coupons']);
        $this->assertEquals('Jane Smith', $customers[1]['customer_name']);
        $this->assertEquals(3, $customers[1]['total_coupons']);
    }

    public function test_get_frequent_customers_includes_usage_statistics()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        $coupons = Coupon::factory()->count(10)->create([
            'customer_name' => 'John Doe',
            'customer_phone' => '6281234567890',
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        // Mark 4 as used
        foreach ($coupons->take(4) as $coupon) {
            $coupon->status = Coupon::STATUS_USED;
            $coupon->save();
            CouponValidation::factory()->create([
                'coupon_id' => $coupon->id,
                'action' => 'used',
            ]);
        }

        $customers = $this->reportService->getFrequentCustomers($dateFrom, $dateTo);

        $this->assertEquals(10, $customers[0]['total_coupons']);
        $this->assertEquals(4, $customers[0]['total_used']);
        $this->assertEquals(40.0, $customers[0]['usage_rate']); // 4/10 * 100
    }

    public function test_get_frequent_customers_includes_formatted_phone()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        Coupon::factory()->create([
            'customer_name' => 'John Doe',
            'customer_phone' => '6281234567890',
            'created_at' => Carbon::today()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        $customers = $this->reportService->getFrequentCustomers($dateFrom, $dateTo);

        $this->assertEquals('6281234567890', $customers[0]['customer_phone']);
        $this->assertEquals(PhoneHelper::formatForDisplay('6281234567890'), $customers[0]['formatted_phone']);
    }

    public function test_get_frequent_customers_respects_limit()
    {
        $dateFrom = Carbon::today()->subDays(30);
        $dateTo = Carbon::today();

        foreach (range(1, 5) as $i) {
            Coupon::factory()->count(2)->create([
                'customer_name' => "Customer $i",
                'customer_phone' => "628123456789$i",
                'created_at' => Carbon::today()->subDays(10),
                'created_by' => $this->user->id,
            ]);
        }

        $customers = $this->reportService->getFrequentCustomers($dateFrom, $dateTo, 3);

        $this->assertCount(3, $customers);
    }
}
