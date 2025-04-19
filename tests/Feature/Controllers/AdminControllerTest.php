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
     * Test unauthenticated or unauthorized access to web admin endpoints redirects to login.
     */
    public function test_unauthenticated_access_redirects_to_login_for_web_admin()
    {
        auth()->logout();

        $response = $this->get(route('users.index')); 

        $response->assertRedirect(route('login'));
    }

    /**
     * Test the store method.
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

        $response = $this->post(route('users.store'), $data);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test the store method fails with invalid data.
     */
    public function test_store_user_fails_with_invalid_data()
    {
        $data = [
            'first_name' => '',             // Missing required first name
            'last_name'  => 'Doe',          
            'gender'     => 'male',       
            'country'    => 'United Kingdom', 
            'email'      => 'invalid-email', // Invalid email format
            'phone'      => 'not-a-phone-number', // Invalid phone format
            'password'   => 'short',        // Invalid password (too short)
            'password_confirmation' => 'short', // Password confirmation does not meet length requirement
        ];

        // Send a POST request with invalid data to create a user
        $response = $this->post(route('users.store'), $data);

        // Assert that the response redirects back with validation errors
        $response->assertSessionHasErrors(['first_name', 'email', 'phone', 'password']); // Assert specific validation errors
    }

    /**
     * Test the update method.
     */
    public function test_update_user_and_redirects()
    {
        $user = User::factory()->create();

        $data = [
            'first_name' => 'Updated Name',
            'last_name' => $user->last_name,
            'gender' => $user->gender,
            'email' => $user->email,
            'phone' => '9876543210',
            'country' => $user->country,
        ]; 

        $response = $this->patch(route('users.update', $user->id), $data);

        $response->assertRedirect(route('users.show', $user->id))
            ->assertSessionHas('success', 'User updated successfully!');

        $this->assertDatabaseHas('users', ['first_name' => 'Updated Name']);
    }

    /**
     * Test the update method fails with invalid data.
     */
    public function test_update_user_fails_with_invalid_data()
    {
        $user = User::factory()->create();

        $data = [
            'first_name' => '',             // Missing required first name
            'last_name'  => 'Doe',         
            'gender'     => 'male',         
            'country'    => 'United Kingdom', 
            'email'      => 'invalid-email', // Invalid email format
            'phone'      => 'not-a-phone-number', // Invalid phone format (does not meet the regex rules)
        ];

        $response = $this->patch(route('users.update', $user->id), $data);

        // Assert that the response redirects back with validation errors
        $response->assertSessionHasErrors(['first_name', 'email', 'phone']); // Assert specific validation errors
    }

    /**
     * Test the destroy method.
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
     * Test the show method.
     */
    public function test_show_displays_user_details()
    {
        $user = User::factory()->create();

        $response = $this->get(route('users.show', $user->id));

        $response->assertStatus(200)
            ->assertViewIs('user.manage')
            ->assertViewHas('user', $user);
    }

    /**
     * Test the index method.
     */
    public function test_index_displays_paginated_users_with_correct_per_page_limit()
    {
        // Seed the database with more than 10 users
        User::factory()->count(25)->create(); 

        // Perform GET request on the index route
        $response = $this->get(route('users.index'));

        $response->assertStatus(200)
            ->assertViewIs('user.index')
            ->assertViewHas('users', function ($users) {
                // Validate that the collection is limited to 10 users per page
                return $users->count() === 10; 
            });

        // Ensure pagination metadata is correct
        $response->assertViewHas('users', function ($users) {
            return $users->perPage() === 10; // Confirm pagination limit
        });
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
