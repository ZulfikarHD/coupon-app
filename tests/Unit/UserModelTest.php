<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_user_role(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($user->isAdmin());
    }

    public function test_user_factory_creates_user_with_default_role(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->role);
        $this->assertContains($user->role, ['admin', 'user']);
    }

    public function test_user_factory_can_create_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertEquals('admin', $user->role);
        $this->assertTrue($user->isAdmin());
    }

    public function test_user_factory_can_create_regular_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertEquals('user', $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_has_name_attribute(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);

        $this->assertEquals('Test User', $user->name);
    }

    public function test_user_has_email_attribute(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'plainpassword']);

        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertStringStartsWith('$2y$', $user->password); // Bcrypt hash format
    }

    public function test_user_role_is_fillable(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertEquals('admin', $user->role);
    }

    public function test_user_password_is_hidden_from_array(): void
    {
        $user = User::factory()->create();

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_user_password_is_hidden_from_json(): void
    {
        $user = User::factory()->create();

        $json = $user->toJson();
        $array = json_decode($json, true);

        $this->assertArrayNotHasKey('password', $array);
    }
}
