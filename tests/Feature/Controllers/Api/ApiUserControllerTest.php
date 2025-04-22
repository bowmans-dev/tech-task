<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiUserControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test setup - authenticate as admin using JWT.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user and generate a JWT token
        $this->user = User::factory()->create();
        $this->userToken = JWTAuth::fromUser($this->user);
    }

    /**
     * Test that unauthenticated access to the API profile endpoint returns 401 Unauthorized.
     */
    public function test_unauthenticated_access_returns_unauthorized_api()
    {
        // Log out the authenticated user
        auth()->logout();

        // Attempt to access the API endpoint to view the profile
        $response = $this->getJson('/api/profile');

        // Assert that the response returns a 401 Unauthorized status code
        $response->assertStatus(401);
    }

    /**
     * Test the show profile API endpoint.
     */
    public function test_show_profile_displays_user_profile()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
                        ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'country',
                    'gender',
                ],
            ]);
    }

    /**
     * Test the update profile API endpoint.
     */
    public function test_update_profile_updates_user()
    {
        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Profile',
            'email' => 'updated@example.com',
            'phone' => '123456789',
            'country' => 'United Kingdom',
            'gender' => 'male',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
                        ->patchJson('/api/profile', $data);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test that the updateProfile API endpoint fails with invalid data.
     */
    public function test_update_profile_fails_with_invalid_data()
    {
        $data = [
            'first_name' => '',             // Missing required first name
            'last_name'  => 'Doe',          // Valid value for last name
            'gender'     => 'male',         // Valid gender
            'country'    => 'United Kingdom', // Valid country
            'email'      => 'invalid-email', // Invalid email format
            'phone'      => 'not-a-phone-number', // Invalid phone format (does not meet the regex rules)
        ];

        // Send a PATCH JSON request with invalid data to update the profile
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
                        ->patchJson('/api/profile', $data);

        // Assert that the response returns a 422 Unprocessable Entity status code
        // Then assert that the JSON response includes validation errors for the relevant fields
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['first_name', 'email', 'phone']);
    }

    /**
     * Test the delete profile API endpoint.
     */
    public function test_delete_profile_deletes_user()
    {
        Storage::fake('public');

        $userId = $this->user->id;

        $filePath = 'profile_pictures/profile.webp';
        Storage::disk('public')->put($filePath, 'content');

        $this->user->update(['profile_picture' => $filePath]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
                        ->deleteJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Your profile has been deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $userId]);

        Storage::disk('public')->assertMissing($filePath);
    }

}