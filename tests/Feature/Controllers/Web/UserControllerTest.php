<?php

namespace Tests\Feature\Controllers\Web;

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
     * Test unauthenticated access redirects to login.
     */
    public function test_unauthenticated_access_redirects_to_login()
    {
        auth()->logout(); // Log out the authenticated user

        $response = $this->get(route('profile.show')); // Try to access showProfile

        $response->assertRedirect(route('login')); // Ensure redirected to login
    }

    /**
     * Test the showProfile method.
     */
    public function test_show_profile_displays_user_profile()
    {
        $response = $this->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.user.profile'); // Ensure correct view is used
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
     * Test the updateProfile method fails with invalid data.
     */
    public function test_update_profile_fails_with_invalid_data()
    {
        $data = [
            'first_name' => '', // Missing required field
            'last_name' => 'Doe',
            'gender' => 'male',
            'country' => 'United Kingdom',
            'email' => 'invalid-email', // Invalid email format
            'phone' => 'not-a-phone-number', // Invalid phone format
        ];

        $response = $this->patch(route('profile.update'), $data);

        $response->assertSessionHasErrors(['first_name', 'email', 'phone']); // Assert only these fields fail
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

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        // $this->assertNull(auth('web')->user());

        $response->assertRedirect(route('login'));

        Storage::disk('public')->assertMissing($filePath);
    }
}
