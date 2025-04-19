<?php

namespace Tests\Feature\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test setup - authenticate as admin using the admin guard.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user and authenticate as admin
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    /**
     * Test that an authenticated admin can access the login page (GET).
     */
    public function test_admin_can_access_login_page()
    {
        // Hit the login route (GET)
        $response = $this->get(route('login'));

        // Assert successful access (status 200)
        $response->assertStatus(200);
    }

    /**
     * Test admin login functionality (POST).
     */
    public function test_admin_login_success()
    {
        // Admin credentials
        $admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        // Attempt login
        $response = $this->post(route('login'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Assert redirection on successful login
        $response->assertRedirect('/users');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /**
     * Test admin login failure with invalid credentials.
     */
    public function test_admin_login_failure()
    {
        // Attempt login with invalid credentials
        $response = $this->post(route('login'), [
            'email' => 'wrongemail@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert that the user is not authenticated
        $response->assertSessionHasErrors('email');
        // $this->assertGuest('admin');
    }

    /**
     * Test logout functionality.
     */
    public function test_admin_logout()
    {
        // Ensure admin user is authenticated
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // Hit the logout route
        $response = $this->post(route('logout'));

        // Assert that the admin is redirected to login and no longer authenticated
        $response->assertRedirect(route('login'));
        $this->assertGuest('admin');
    }

    /**
     * Test sending password reset link.
     */
    public function test_send_reset_link_success()
    {
        $admin = User::factory()->create(['email' => 'admin@test.com']);

        $response = $this->post(route('password.email'), ['email' => 'admin@test.com']);

        $response->assertSessionHas('status', 'Password reset link sent.');
    }

    public function test_send_reset_link_failure()
    {
        $response = $this->post(route('password.email'), ['email' => 'nonexistent@test.com']);

        $response->assertSessionHasErrors(['email']);
    }
}
