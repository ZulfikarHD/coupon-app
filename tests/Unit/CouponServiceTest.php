<?php

namespace Tests\Unit;

use App\Helpers\PhoneHelper;
use App\Jobs\GenerateCouponCode;
use App\Models\Coupon;
use App\Models\CouponValidation;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CouponService $couponService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->couponService = new CouponService();
        $this->user = User::factory()->create();
        Auth::login($this->user);
    }

    public function test_apply_filters_filters_by_status()
    {
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $request = Request::create('/coupons', 'GET', ['status' => 'active']);
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals(Coupon::STATUS_ACTIVE, $results->first()->status);
    }

    public function test_apply_filters_filters_by_multiple_statuses()
    {
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_EXPIRED,
            'created_by' => $this->user->id,
        ]);

        $request = Request::create('/coupons', 'GET', ['status' => ['active', 'used']]);
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('status', Coupon::STATUS_ACTIVE));
        $this->assertTrue($results->contains('status', Coupon::STATUS_USED));
        $this->assertFalse($results->contains('status', Coupon::STATUS_EXPIRED));
    }

    public function test_apply_filters_filters_by_multiple_statuses_from_query_string()
    {
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_EXPIRED,
            'created_by' => $this->user->id,
        ]);

        // Simulate the query string format sent from the frontend using array notation
        $request = Request::create('/coupons?status[]=active&status[]=used', 'GET');
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('status', Coupon::STATUS_ACTIVE));
        $this->assertTrue($results->contains('status', Coupon::STATUS_USED));
        $this->assertFalse($results->contains('status', Coupon::STATUS_EXPIRED));
    }

    public function test_apply_filters_filters_by_search_term()
    {
        Coupon::factory()->create([
            'code' => 'ABC-1234-XYZ',
            'customer_name' => 'John Doe',
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'code' => 'DEF-5678-UVW',
            'customer_name' => 'Jane Smith',
            'created_by' => $this->user->id,
        ]);

        $request = Request::create('/coupons', 'GET', ['search' => 'John']);
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->customer_name);
    }

    public function test_apply_filters_filters_by_customer_name()
    {
        Coupon::factory()->create([
            'customer_name' => 'John Doe',
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'customer_name' => 'Jane Smith',
            'created_by' => $this->user->id,
        ]);

        $request = Request::create('/coupons', 'GET', ['customer_name' => 'John']);
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->customer_name);
    }

    public function test_apply_filters_filters_by_customer_phone()
    {
        Coupon::factory()->create([
            'customer_phone' => '6281234567890',
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'customer_phone' => '6289876543210',
            'created_by' => $this->user->id,
        ]);

        $request = Request::create('/coupons', 'GET', ['customer_phone' => '081234567890']);
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('6281234567890', $results->first()->customer_phone);
    }

    public function test_apply_filters_filters_by_coupon_type()
    {
        Coupon::factory()->create([
            'type' => 'Gratis 1 Kopi',
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'type' => 'Diskon 50%',
            'created_by' => $this->user->id,
        ]);

        $request = Request::create('/coupons', 'GET', ['coupon_type' => 'Kopi']);
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Gratis 1 Kopi', $results->first()->type);
    }

    public function test_apply_filters_filters_by_date_range()
    {
        $yesterday = now()->subDay();
        $today = now();
        $tomorrow = now()->addDay();

        Coupon::factory()->create([
            'created_at' => $yesterday,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'created_at' => $today,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'created_at' => $tomorrow,
            'created_by' => $this->user->id,
        ]);

        $request = Request::create('/coupons', 'GET', [
            'date_from' => $yesterday->format('Y-m-d'),
            'date_to' => $today->format('Y-m-d'),
        ]);
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(2, $results);
    }

    public function test_apply_filters_filters_by_expires_date_range()
    {
        $yesterday = now()->subDay();
        $today = now();
        $tomorrow = now()->addDay();

        Coupon::factory()->create([
            'expires_at' => $yesterday,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'expires_at' => $today,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'expires_at' => $tomorrow,
            'created_by' => $this->user->id,
        ]);

        $request = Request::create('/coupons', 'GET', [
            'expires_from' => $today->format('Y-m-d'),
            'expires_to' => $tomorrow->format('Y-m-d'),
        ]);
        $query = Coupon::query();
        
        $this->couponService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(2, $results);
    }

    public function test_create_normalizes_phone_number()
    {
        $data = [
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
        ];

        $coupon = $this->couponService->create($data);

        $this->assertEquals('6281234567890', $coupon->customer_phone);
    }

    public function test_create_sets_status_to_active()
    {
        $data = [
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
        ];

        $coupon = $this->couponService->create($data);

        $this->assertEquals(Coupon::STATUS_ACTIVE, $coupon->status);
    }

    public function test_create_sets_created_by_to_current_user()
    {
        $data = [
            'type' => 'Test Coupon',
            'description' => 'Test Description',
            'customer_name' => 'Test User',
            'customer_phone' => '081234567890',
        ];

        $coupon = $this->couponService->create($data);

        $this->assertEquals($this->user->id, $coupon->created_by);
    }

    public function test_validate_returns_error_for_wrong_password()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
        ]);

        $result = $this->couponService->validate($coupon->code, 'wrongpassword');

        $this->assertFalse($result['success']);
        $this->assertEquals('Password salah', $result['message']);
        $this->assertEquals(401, $result['status_code']);
    }

    public function test_validate_returns_error_for_used_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
        ]);

        $result = $this->couponService->validate($coupon->code, 'password');

        $this->assertFalse($result['success']);
        $this->assertEquals('Kupon sudah digunakan', $result['message']);
        $this->assertEquals(422, $result['status_code']);
    }

    public function test_validate_returns_error_for_expired_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->subDay(),
        ]);

        $result = $this->couponService->validate($coupon->code, 'password');

        $this->assertFalse($result['success']);
        $this->assertEquals('Kupon sudah kedaluwarsa', $result['message']);
        $this->assertEquals(422, $result['status_code']);
    }

    public function test_validate_successfully_validates_active_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
        ]);

        $result = $this->couponService->validate($coupon->code, 'password');

        $this->assertTrue($result['success']);
        $this->assertEquals('Kupon berhasil divalidasi', $result['message']);
        $this->assertEquals(200, $result['status_code']);
        
        $coupon->refresh();
        $this->assertEquals(Coupon::STATUS_USED, $coupon->status);
        
        $this->assertDatabaseHas('coupon_validations', [
            'coupon_id' => $coupon->id,
            'validated_by' => $this->user->id,
            'action' => 'used',
        ]);
    }

    public function test_validate_extracts_code_from_url()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
        ]);

        $url = '/coupon/' . $coupon->code;
        $result = $this->couponService->validate($url, 'password123');

        $this->assertTrue($result['success']);
    }

    public function test_reverse_returns_error_for_wrong_password()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
        ]);

        $result = $this->couponService->reverse($coupon->id, 'wrongpassword', 'Test reason');

        $this->assertFalse($result['success']);
        $this->assertEquals('Password salah', $result['message']);
    }

    public function test_reverse_returns_error_for_non_used_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
        ]);

        $result = $this->couponService->reverse($coupon->id, 'password', 'Test reason');

        $this->assertFalse($result['success']);
        $this->assertEquals('Hanya kupon yang sudah digunakan yang dapat dibatalkan', $result['message']);
    }

    public function test_reverse_successfully_reverses_used_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
        ]);

        $result = $this->couponService->reverse($coupon->id, 'password123', 'Test reversal reason');

        $this->assertTrue($result['success']);
        $this->assertEquals('Penggunaan kupon berhasil dibatalkan', $result['message']);
        
        $coupon->refresh();
        $this->assertEquals(Coupon::STATUS_ACTIVE, $coupon->status);
        
        $this->assertDatabaseHas('coupon_validations', [
            'coupon_id' => $coupon->id,
            'validated_by' => $this->user->id,
            'action' => 'reversed',
            'notes' => 'Test reversal reason',
        ]);
    }

    public function test_check_returns_not_found_for_invalid_code()
    {
        $result = $this->couponService->check('INVALID-CODE');

        $this->assertFalse($result['exists']);
        $this->assertEquals('Kupon tidak ditemukan', $result['message']);
        $this->assertEquals(404, $result['status_code']);
    }

    public function test_check_returns_can_validate_true_for_active_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
        ]);

        $result = $this->couponService->check($coupon->code);

        $this->assertTrue($result['exists']);
        $this->assertTrue($result['can_validate']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertEquals($coupon->code, $result['coupon']['code']);
    }

    public function test_check_returns_can_validate_false_for_used_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
        ]);

        $result = $this->couponService->check($coupon->code);

        $this->assertTrue($result['exists']);
        $this->assertFalse($result['can_validate']);
        $this->assertEquals('Kupon sudah digunakan', $result['message']);
        $this->assertEquals(422, $result['status_code']);
    }

    public function test_check_returns_can_validate_false_for_expired_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->subDay(),
        ]);

        $result = $this->couponService->check($coupon->code);

        $this->assertTrue($result['exists']);
        $this->assertFalse($result['can_validate']);
        $this->assertEquals('Kupon sudah kedaluwarsa', $result['message']);
        $this->assertEquals(422, $result['status_code']);
    }

    public function test_check_extracts_code_from_url()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
        ]);

        $url = '/coupon/' . $coupon->code;
        $result = $this->couponService->check($url);

        $this->assertTrue($result['exists']);
        $this->assertTrue($result['can_validate']);
    }
}
