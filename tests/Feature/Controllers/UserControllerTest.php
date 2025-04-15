<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test setup - authenticate a regular user.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate as a regular user
        $this->actingAs(User::factory()->create());
    }

    /**
     * Test the showProfile method.
     */
    public function test_show_profile_displays_user_profile()
    {
        $response = $this->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertViewIs('profile'); // Ensure correct view is used
        $response->assertViewHas('profile'); // Ensure profile data is passed to the view
    }

    /**
     * Test the updateProfile method for updating user profile details.
     */
    public function test_update_profile_updates_user_and_redirects()
    {

        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Profile',
            'email' => 'updated@example.com',
            'phone' => '07123456789',
            'country' => 'United Kingdom',
            'gender' => 'male',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->patch(route('profile.update'), $data);

        $response->assertRedirect(route('profile.show'));
        $this->assertDatabaseHas('users', [
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test the deleteProfile method for deleting a user profile.
     */
    public function test_delete_profile_deletes_user_and_redirects()
    {
        Storage::fake('public');

        $user = auth('web')->user();

        $filePath = 'profile_pictures/profile.webp';
        Storage::disk('public')->put($filePath, 'content');

        $user->update(['profile_picture' => $filePath]);

        // Call the delete route.
        $response = $this->delete(route('profile.delete'));

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        // $this->assertNull(auth('web')->user());

        $response->assertRedirect(route('login'));

        Storage::disk('public')->assertMissing($filePath);
    }
}
