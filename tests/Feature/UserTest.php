<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
    }

    public function test_user_index_page_can_be_rendered_by_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Index')
        );
    }

    public function test_user_index_page_cannot_be_accessed_by_regular_user(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/users');

        $response->assertStatus(403);
    }

    public function test_user_index_page_cannot_be_accessed_by_guest(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect('/login');
    }

    public function test_user_create_page_can_be_rendered_by_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/users/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Create')
        );
    }

    public function test_user_create_page_cannot_be_accessed_by_regular_user(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/users/create');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'user',
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_create_admin_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'role' => 'admin',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');
    }

    public function test_user_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_user_creation_validates_email_format(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_creation_validates_unique_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_creation_validates_password_confirmation(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_user_creation_validates_password_min_length(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_user_creation_validates_role(): void
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid-role',
        ]);

        $response->assertSessionHasErrors(['role']);
    }

    public function test_user_edit_page_can_be_rendered_by_admin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Edit')
                ->has('user', fn ($user) => 
                    $user->has('id')
                        ->has('name')
                        ->has('email')
                        ->has('role')
                )
        );
    }

    public function test_user_edit_page_cannot_be_accessed_by_regular_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->regularUser)->get("/users/{$user->id}/edit");

        $response->assertStatus(403);
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'role' => 'admin',
        ]);

        $user->refresh();

        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('admin', $user->role);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_update_user_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'role' => $user->role,
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');
    }

    public function test_user_update_validates_unique_email_excluding_current_user(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($this->admin)->put("/users/{$user1->id}", [
            'name' => $user1->name,
            'email' => 'user2@example.com', // Same as user2
            'role' => $user1->role,
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_update_allows_same_email_for_same_user(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'test@example.com', // Same email
            'role' => $user->role,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/users');
    }

    public function test_user_update_validates_password_confirmation_when_password_provided(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
            'role' => $user->role,
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_user_update_does_not_require_password(): void
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $response = $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => $user->role,
            // No password provided
        ]);

        $user->refresh();
        $this->assertEquals($oldPassword, $user->password); // Password unchanged

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/users');
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/users/{$user->id}");

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete("/users/{$this->admin->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('cannot delete', session('error'));

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_user_delete_cannot_be_accessed_by_regular_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->regularUser)->delete("/users/{$user->id}");

        $response->assertStatus(403);
    }

    public function test_user_index_filters_by_role(): void
    {
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'user']);
        User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($this->admin)->get('/users?role=user');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Index')
                ->has('users.data', 2)
                ->where('users.data.0.role', 'user')
                ->where('users.data.1.role', 'user')
        );
    }

    public function test_user_index_searches_by_name(): void
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($this->admin)->get('/users?search=John');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.id', $user1->id)
        );
    }

    public function test_user_index_searches_by_email(): void
    {
        $user1 = User::factory()->create(['email' => 'john@example.com']);
        User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->actingAs($this->admin)->get('/users?search=john@example.com');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.id', $user1->id)
        );
    }

    public function test_user_index_paginates_results(): void
    {
        User::factory()->count(25)->create();

        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Index')
                ->where('users.per_page', 20)
                ->where('users.total', 27) // 25 created + 2 from setUp
                ->has('users.data', 20) // First page has 20 items
        );
    }

    public function test_user_index_shows_all_users_for_admin(): void
    {
        User::factory()->count(5)->create(['role' => 'user']);
        User::factory()->count(3)->create(['role' => 'admin']);

        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Index')
                ->has('users.data')
        );
    }

    public function test_user_index_returns_empty_when_no_users_match_search(): void
    {
        User::factory()->create(['name' => 'John Doe']);

        $response = $this->actingAs($this->admin)->get('/users?search=Nonexistent');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Index')
                ->has('users.data', 0)
        );
    }

    public function test_user_index_orders_by_created_at_desc(): void
    {
        $oldUser = User::factory()->create(['created_at' => now()->subDay()]);
        $newUser = User::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('users/Index')
                ->where('users.data.0.id', $newUser->id)
        );
    }
}
