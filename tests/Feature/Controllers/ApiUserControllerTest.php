<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiUserControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test setup - authenticate a regular user.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate as a regular user
        $this->actingAs(User::factory()->create(), 'web');
    }

    /**
     * Test the show profile API endpoint.
     */
    public function test_show_profile_displays_user_profile()
    {
        $response = $this->getJson('/api/profile'); // API endpoint for showing profile

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

        $response = $this->patchJson('/api/profile', $data); // API endpoint for updating profile

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test the delete profile API endpoint.
     */
    public function test_delete_profile_deletes_user()
    {
        Storage::fake('public');

        $user = auth('web')->user();

        $filePath = 'profile_pictures/profile.jpg';
        Storage::disk('public')->put($filePath, 'content');

        $user->update(['profile_picture' => $filePath]);

        $response = $this->deleteJson('/api/profile'); // API endpoint for deleting profile

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Your profile has been deleted successfully.');

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertNull(auth('web')->user());

        Storage::disk('public')->assertMissing($filePath);
    }

    /**
     * Test updating profile with a profile picture.
     */
    public function test_update_profile()
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

        $response = $this->patchJson('/api/profile', $data); // API endpoint for updating profile

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
        ]);

    }
}
