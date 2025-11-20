<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_coupon_index_page_can_be_rendered()
    {
        $response = $this->actingAs($this->user)->get('/coupons');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('coupons/Index')
        );
    }

    public function test_coupon_create_page_can_be_rendered()
    {
        $response = $this->actingAs($this->user)->get('/coupons/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('coupons/Create')
        );
    }

    public function test_user_can_create_coupon()
    {
        $response = $this->actingAs($this->user)->post('/coupons', [
            'type' => 'Gratis 1 Kopi',
            'description' => 'Dapatkan 1 kopi gratis',
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_email' => 'john@example.com',
            'customer_social_media' => '@johndoe',
            'expires_at' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('coupons', [
            'customer_name' => 'John Doe',
            'type' => 'Gratis 1 Kopi',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_coupon_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post('/coupons', []);

        $response->assertSessionHasErrors(['type', 'description', 'customer_name', 'customer_phone']);
    }

    public function test_coupon_creation_normalizes_phone_number()
    {
        $coupon = Coupon::factory()->create([
            'customer_phone' => '081234567890',
            'created_by' => $this->user->id,
        ]);

        // Phone should be normalized to 6281234567890
        $this->assertStringStartsWith('62', $coupon->customer_phone);
        $this->assertEquals('6281234567890', $coupon->customer_phone);
    }

    public function test_coupon_show_page_can_be_rendered()
    {
        $coupon = Coupon::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/coupons/{$coupon->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('coupons/Show')
                ->has('coupon', fn ($coupon) => 
                    $coupon->has('id')
                        ->has('code')
                        ->has('type')
                )
        );
    }

    public function test_coupon_can_be_deleted()
    {
        $coupon = Coupon::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/coupons/{$coupon->id}");

        $response->assertRedirect('/coupons');
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_coupon_index_filters_by_status()
    {
        Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $this->user->id,
        ]);
        Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/coupons?status=active');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('coupons/Index')
                ->has('coupons.data', 1)
                ->where('coupons.data.0.status', 'active')
        );
    }

    public function test_coupon_index_searches_by_code()
    {
        $coupon = Coupon::factory()->create([
            'code' => 'ABC-1234-XYZ',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/coupons?search=ABC-1234');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('coupons/Index')
                ->has('coupons.data', 1)
                ->where('coupons.data.0.id', $coupon->id)
        );
    }

    public function test_coupon_index_searches_by_customer_name()
    {
        $coupon = Coupon::factory()->create([
            'customer_name' => 'John Doe',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/coupons?search=John');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('coupons/Index')
                ->has('coupons.data', 1)
                ->where('coupons.data.0.id', $coupon->id)
        );
    }

    public function test_public_coupon_view_can_be_accessed_without_auth()
    {
        $coupon = Coupon::factory()->create([
            'code' => 'ABC-1234-XYZ',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get("/coupon/{$coupon->code}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('coupons/Public')
                ->has('coupon', fn ($coupon) => 
                    $coupon->has('code')
                        ->has('type')
                )
        );
    }

    public function test_public_coupon_view_returns_404_for_invalid_code()
    {
        $response = $this->get('/coupon/INVALID-CODE');

        $response->assertStatus(404);
    }

    public function test_coupon_index_paginates_results()
    {
        Coupon::factory()->count(25)->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/coupons');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('coupons/Index')
                ->where('coupons.per_page', 20)
                ->where('coupons.total', 25)
                ->has('coupons.data', 20) // First page has 20 items
        );
    }

    public function test_coupon_check_endpoint_returns_coupon_info()
    {
        $coupon = Coupon::factory()->create([
            'code' => 'ABC-1234-XYZ',
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/api/coupons/{$coupon->code}/check");

        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true,
            'can_validate' => true,
            'coupon' => [
                'code' => 'ABC-1234-XYZ',
            ],
        ]);
    }

    public function test_coupon_check_endpoint_returns_404_for_invalid_code()
    {
        $response = $this->actingAs($this->user)->get('/api/coupons/INVALID-CODE/check');

        $response->assertStatus(404);
        $response->assertJson([
            'exists' => false,
            'message' => 'Kupon tidak ditemukan',
        ]);
    }

    public function test_coupon_check_endpoint_returns_cannot_validate_for_used_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/api/coupons/{$coupon->code}/check");

        $response->assertStatus(422);
        $response->assertJson([
            'exists' => true,
            'can_validate' => false,
            'message' => 'Kupon sudah digunakan',
        ]);
    }

    public function test_coupon_check_endpoint_returns_cannot_validate_for_expired_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->subDay(),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/api/coupons/{$coupon->code}/check");

        $response->assertStatus(422);
        $response->assertJson([
            'exists' => true,
            'can_validate' => false,
            'message' => 'Kupon sudah kedaluwarsa',
        ]);
    }

    public function test_coupon_validate_endpoint_requires_password()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->code}/validate", []);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_coupon_validate_endpoint_returns_error_for_wrong_password()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->code}/validate", [
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Password salah',
        ]);
    }

    public function test_coupon_validate_endpoint_successfully_validates_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'expires_at' => now()->addDays(7),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->code}/validate", [
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Kupon berhasil divalidasi',
        ]);

        $coupon->refresh();
        $this->assertEquals(Coupon::STATUS_USED, $coupon->status);
        
        $this->assertDatabaseHas('coupon_validations', [
            'coupon_id' => $coupon->id,
            'action' => 'used',
        ]);
    }

    public function test_coupon_validate_endpoint_returns_error_for_used_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->code}/validate", [
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Kupon sudah digunakan',
        ]);
    }

    public function test_coupon_reverse_endpoint_requires_password_and_reason()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->id}/reverse", []);

        $response->assertSessionHasErrors(['password', 'reason']);
    }

    public function test_coupon_reverse_endpoint_requires_reason_min_length()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->id}/reverse", [
            'password' => 'password',
            'reason' => 'Short',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_coupon_reverse_endpoint_returns_error_for_wrong_password()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->id}/reverse", [
            'password' => 'wrongpassword',
            'reason' => 'This is a valid reason for reversal',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Password salah', session('error'));
    }

    public function test_coupon_reverse_endpoint_returns_error_for_non_used_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_ACTIVE,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->id}/reverse", [
            'password' => 'password',
            'reason' => 'This is a valid reason for reversal',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Hanya kupon yang sudah digunakan yang dapat dibatalkan', session('error'));
    }

    public function test_coupon_reverse_endpoint_successfully_reverses_coupon()
    {
        $coupon = Coupon::factory()->create([
            'status' => Coupon::STATUS_USED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post("/coupons/{$coupon->id}/reverse", [
            'password' => 'password',
            'reason' => 'This is a valid reason for reversal',
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Penggunaan kupon berhasil dibatalkan', session('success'));

        $coupon->refresh();
        $this->assertEquals(Coupon::STATUS_ACTIVE, $coupon->status);
        
        $this->assertDatabaseHas('coupon_validations', [
            'coupon_id' => $coupon->id,
            'action' => 'reversed',
            'notes' => 'This is a valid reason for reversal',
        ]);
    }

    public function test_guest_cannot_access_check_endpoint()
    {
        $coupon = Coupon::factory()->create([
            'code' => 'ABC-1234-XYZ',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get("/api/coupons/{$coupon->code}/check");

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_validate_endpoint()
    {
        $coupon = Coupon::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->post("/coupons/{$coupon->code}/validate", [
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_reverse_endpoint()
    {
        $coupon = Coupon::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->post("/coupons/{$coupon->id}/reverse", [
            'password' => 'password',
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect('/login');
    }
}
