<?php

namespace Tests\Feature\Routes;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class ApiRoutesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test setup - authenticate as admin for admin routes.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Push the StartSession middleware into the application's middleware stack
        $this->app->make(\Illuminate\Contracts\Http\Kernel::class)
            ->pushMiddleware(\Illuminate\Session\Middleware\StartSession::class);

        // Authenticate as admin
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); // Use the admin guard
    }

    /**
     * Test admin login functionality (POST).
     */
    public function test_admin_can_login()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Admin logged in successfully.',
            ]);

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /**
     * Test user login functionality (POST).
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'user@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User logged in successfully.',
            ]);

        $this->assertAuthenticatedAs($user, 'web');
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_prevented_with_invalid_credentials()
    {

        $response = $this->postJson(route('api.login'), [
            'email' => 'wrongemail@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials provided.',
            ]);

    }

    /**
     * Test admin route for creating a user.
     */
    public function test_admin_can_create_user()
    {
        $file = UploadedFile::fake()->image('profile.jpg');

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'simon.doe@example.com',
            'phone' => '123456789',
            'country' => 'United Kingdom',
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_picture' => $file,
        ];

        $response = $this->postJson('/api/users', $data); // API route

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created successfully!',
                'data' => [
                    'first_name' => 'John',
                    'email' => 'simon.doe@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'email' => 'simon.doe@example.com',
        ]);

        // Cleanup: Delete uploaded profile picture
        $user = User::where('email', 'simon.doe@example.com')->first();
        if ($user && $user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }
    }

    /**
     * Test admin route for updating a user.
     */
    public function test_admin_can_update_user()
    {
        // Create an admin and authenticate
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // Create a user to update
        $user = User::factory()->create([
            'first_name' => 'Old Name',
            'last_name' => 'Old Last Name',
            'email' => 'old_email@example.com',
            'gender' => 'male',
            'phone' => '123456789',
            'country' => 'United Kingdom',
        ]);
        
        // Data to update (include last_name field to meet validation rules)
        $data = [
            'first_name' => 'New Name',
            'last_name' => 'New Last Name',
            'email' => 'new_email@example.com',
            'gender' => 'male',
            'country' => 'United Kingdom',
            'phone' => '123456789',
        ];
        

        // Make PATCH request to update the user
        $response = $this->patchJson(route('api.users.update', $user), $data);

        // Assert the response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User updated successfully!',
                'data' => [
                    'first_name' => 'New Name',
                    'last_name' => 'New Last Name',
                    'email' => 'new_email@example.com',
                ],
            ]);

        // Verify the database has been updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'New Name',
            'last_name' => 'New Last Name',
            'email' => 'new_email@example.com',
        ]);
    }

    /**
     * Test admin route for deleting a user.
     */
    public function test_admin_can_delete_user()
    {
        $user = User::factory()->create(['profile_picture' => 'profile_pictures/profile.webp']);

        Storage::fake('public');
        Storage::disk('public')->put('profile_pictures/profile.webp', 'content');

        $response = $this->deleteJson('/api/users/'.$user->id); // API route

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully!',
            ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        Storage::disk('public')->assertMissing('profile_pictures/profile.webp');
    }

    /**
     * Test admin route for retrieving all users.
     */
    public function test_admin_can_retrieve_all_users()
    {
        User::factory()->count(10)->create();

        $response = $this->getJson('/api/users'); // API route

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Users retrieved successfully.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => ['id', 'first_name', 'last_name', 'email'],
                    ],
                ],
            ]);
    }

    /**
     * Test admin route for filtering users.
     */
    public function test_admin_can_filter_users()
    {
        User::factory()->count(3)->create(['first_name' => 'Jerri']);
        User::factory()->create(['first_name' => 'Jane']);

        $response = $this->getJson('/api/users/filter?search=Jerri'); // API route

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Users filtered successfully.')
            ->assertJsonFragment(['first_name' => 'Jerri']); // Check for matching entry
    }

    /**
     * Test user profile retrieval route.
     */
    public function test_user_can_retrieve_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('api.profile.show')); // API route

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile retrieved successfully.',
                'data' => [
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                ],
            ]);
    }

    /**
     * Test user can update their profile.
     */
    public function test_user_can_update_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $data = [
            'first_name' => 'New',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '123456789',
            'country' => 'United Kingdom',
            'phone' => '123456789',
            'gender' => 'male',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->patchJson(route('api.profile.update'), $data); // API route

        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully.']);

        $this->assertDatabaseHas('users', [
            'first_name' => 'New',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test user profile deletion route.
     */
    public function test_user_can_delete_profile()
    {

        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->deleteJson(route('api.profile.delete')); // API route

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Your profile has been deleted successfully.']);
    }
}
