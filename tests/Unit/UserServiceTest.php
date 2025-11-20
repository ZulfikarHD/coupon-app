<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    public function test_apply_filters_filters_by_search_term_name(): void
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $request = Request::create('/users', 'GET', ['search' => 'John']);
        $query = User::query();
        
        $this->userService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    public function test_apply_filters_filters_by_search_term_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);
        User::factory()->create(['email' => 'jane@example.com']);

        $request = Request::create('/users', 'GET', ['search' => 'john@example.com']);
        $query = User::query();
        
        $this->userService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('john@example.com', $results->first()->email);
    }

    public function test_apply_filters_filters_by_role(): void
    {
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'user']);
        User::factory()->create(['role' => 'user']);

        $request = Request::create('/users', 'GET', ['role' => 'user']);
        $query = User::query();
        
        $this->userService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(2, $results);
        $this->assertEquals('user', $results->first()->role);
        $this->assertEquals('user', $results->last()->role);
    }

    public function test_apply_filters_ignores_role_filter_when_all(): void
    {
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'user']);

        $request = Request::create('/users', 'GET', ['role' => 'all']);
        $query = User::query();
        
        $this->userService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(2, $results);
    }

    public function test_apply_filters_returns_all_users_when_no_filters(): void
    {
        User::factory()->count(3)->create();

        $request = Request::create('/users', 'GET', []);
        $query = User::query();
        
        $this->userService->applyFilters($query, $request);
        
        $results = $query->get();
        $this->assertCount(3, $results);
    }

    public function test_create_hashes_password(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plainpassword',
            'role' => 'user',
        ];

        $user = $this->userService->create($data);

        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }

    public function test_create_sets_default_role_to_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            // role not provided
        ];

        $user = $this->userService->create($data);

        $this->assertEquals('user', $user->role);
    }

    public function test_create_sets_provided_role(): void
    {
        $data = [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ];

        $user = $this->userService->create($data);

        $this->assertEquals('admin', $user->role);
    }

    public function test_create_saves_user_to_database(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $user = $this->userService->create($data);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);
    }

    public function test_update_changes_user_name(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $data = [
            'name' => 'New Name',
            'email' => $user->email,
            'role' => $user->role,
        ];

        $updatedUser = $this->userService->update($user, $data);

        $this->assertEquals('New Name', $updatedUser->name);
    }

    public function test_update_changes_user_email(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $data = [
            'name' => $user->name,
            'email' => 'new@example.com',
            'role' => $user->role,
        ];

        $updatedUser = $this->userService->update($user, $data);

        $this->assertEquals('new@example.com', $updatedUser->email);
    }

    public function test_update_changes_user_role(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin',
        ];

        $updatedUser = $this->userService->update($user, $data);

        $this->assertEquals('admin', $updatedUser->role);
    }

    public function test_update_hashes_password_when_provided(): void
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'role' => $user->role,
        ];

        $updatedUser = $this->userService->update($user, $data);

        $this->assertNotEquals($oldPassword, $updatedUser->password);
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
    }

    public function test_update_does_not_change_password_when_not_provided(): void
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $data = [
            'name' => 'New Name',
            'email' => $user->email,
            'role' => $user->role,
            // password not provided
        ];

        $updatedUser = $this->userService->update($user, $data);

        $this->assertEquals($oldPassword, $updatedUser->password);
    }

    public function test_update_does_not_change_password_when_empty_string(): void
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'role' => $user->role,
        ];

        $updatedUser = $this->userService->update($user, $data);

        $this->assertEquals($oldPassword, $updatedUser->password);
    }

    public function test_delete_removes_user_from_database(): void
    {
        $user = User::factory()->create();

        $result = $this->userService->delete($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_prevents_self_deletion(): void
    {
        $user = User::factory()->create();
        
        // Simulate trying to delete yourself
        $this->actingAs($user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot delete your own account.');

        $this->userService->delete($user);
    }

    public function test_delete_allows_deleting_other_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);

        $result = $this->userService->delete($user2);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
        $this->assertDatabaseHas('users', ['id' => $user1->id]);
    }
}
