<?php

namespace Tests\Feature\Routes;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class ApiRoutesTest extends TestCase
{
    use DatabaseTransactions;


    /**
     * Test admin login functionality (POST).
     */
    public function test_admin_can_login()
    {
        // Create an admin with hashed password
        $admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);


        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Admin:api logged in successfully.',
            ]);

        $this->assertAuthenticatedAs($admin, 'admin:api');
    }

    /**
     * Test user login functionality (POST).
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'user@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User:api logged in successfully.',
            ]);

        $this->assertAuthenticatedAs($user, 'user:api');
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
        $admin = Admin::factory()->create();
        $adminToken = JWTAuth::fromUser($admin);

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

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
        ->postJson('/api/users', $data);

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
        $adminToken = JWTAuth::fromUser($admin);

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
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
                        ->patchJson('/api/users/' . $user->id, $data);

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
        // Create an admin and authenticate
        $admin = Admin::factory()->create();
        $adminToken = JWTAuth::fromUser($admin);

        $user = User::factory()->create(['profile_picture' => 'profile_pictures/profile.webp']);

        Storage::fake('public');
        Storage::disk('public')->put('profile_pictures/profile.webp', 'content');

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)->deleteJson('/api/users/' . $user->id);

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
        // Create an admin and authenticate
        $admin = Admin::factory()->create();
        $adminToken = JWTAuth::fromUser($admin);

        User::factory()->count(10)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
                        ->getJson('/api/users/' );

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
        // Create an admin and authenticate
        $admin = Admin::factory()->create();
        $adminToken = JWTAuth::fromUser($admin);

        User::factory()->count(3)->create(['first_name' => 'Jerri']);
        User::factory()->create(['first_name' => 'Jane']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
                  ->getJson('/api/users/filter?search=Jerri'); // Pass search explicitly in query string

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
        $userToken = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
                        ->getJson('/api/profile');

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
        $userToken = JWTAuth::fromUser($user);

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

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
                        ->patchJson('/api/profile', $data);

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
        $userToken = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
                        ->deleteJson('/api/profile');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Your profile has been deleted successfully.']);
    }
}
