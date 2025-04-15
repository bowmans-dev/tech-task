<?php

namespace Tests\Feature\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiAdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test setup - authenticate as admin.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Authenticate as admin
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); // Use the admin guard

    }

    /**
     * Test the store method for creating a user.
     */
    public function test_store_creates_user()
    {
        $file = UploadedFile::fake()->image('profile.jpg');

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'tim.doe@example.com',
            'phone' => '123456789',
            'country' => 'USA',
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
                    'email' => 'tim.doe@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'email' => 'tim.doe@example.com',
        ]);

        // Cleanup: Delete the uploaded profile picture
        $user = User::where('email', 'tim.doe@example.com')->first();
        if ($user && $user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }
    }

    /**
     * Test the destroy method for deleting a user.
     */
    public function test_destroy_deletes_user()
    {
        $user = User::factory()->create(['profile_picture' => 'profile_pictures/profile.jpg']);

        Storage::fake('public');
        Storage::disk('public')->put('profile_pictures/profile.jpg', 'content');

        $response = $this->deleteJson('/api/users/'.$user->id); // API route

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully!',
            ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        Storage::disk('public')->assertMissing('profile_pictures/profile.jpg');
    }

    /**
     * Test the filter method.
     */
    public function test_filter_returns_filtered_results()
    {
        User::factory()->count(3)->create(['first_name' => 'Jerri']);
        User::factory()->create(['first_name' => 'Jane']);

        $response = $this->getJson('/api/users/filter?search=Jerri'); // API route

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Users filtered successfully.')
            ->assertJsonFragment(['first_name' => 'Jerri']); // Check for any matching entry
    }

    /**
     * Test the index method for retrieving all users.
     */
    public function test_can_retrieve_users()
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
}
