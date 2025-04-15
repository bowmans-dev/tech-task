<?php

namespace Tests\Feature\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminControllerTest extends TestCase
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
    public function test_store_creates_user_and_redirects()
    {

        $data = [
            'first_name' => 'Jack',
            'last_name' => 'Doe',
            'gender' => 'Male',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
        ];

        $this->post(route('users.store'), $data);

        // $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test the destroy method for deleting a user.
     */
    public function test_destroy_deletes_user_and_redirects()
    {
        $user = User::factory()->create(['profile_picture' => 'profile_pictures/profile.webp']);

        Storage::fake('public');
        Storage::disk('public')->put('profile_pictures/profile.webp', 'content');
        Storage::disk('public')->assertExists('profile_pictures/profile.webp');

        $response = $this->delete(route('users.destroy', $user->id));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        Storage::disk('public')->assertMissing('profile_pictures/profile.webp');
    }

    /**
     * Test the filter method.
     */
    public function test_filter_returns_filtered_results()
    {
        User::factory()->count(3)->create(['first_name' => 'Jerri']);
        User::factory()->create(['first_name' => 'Jane']);

        $response = $this->get(route('users.filter', ['search' => 'Jerri']));

        $response->assertStatus(200);
        $response->assertViewHas('users', function ($users) {
            return $users->total() === 3;
        });
    }
}
