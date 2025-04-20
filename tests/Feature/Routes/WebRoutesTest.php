<?php

namespace Tests\Feature\Routes;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test login GET route renders the login page.
     */
    public function test_login_get_route_renders_login_page()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200)->assertViewIs('sign-in');
    }

    /**
     * Test login POST route for an admin redirects to /users.
     */
    public function test_admin_login_post_route_redirects_to_users()
    {
        $admin = Admin::factory()->create(['password' => bcrypt('password')]);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/users');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /**
     * Test login POST route for a regular user redirects to /profile.
     */
    public function test_user_login_post_route_redirects_to_profile()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/profile');
        $this->assertAuthenticatedAs($user, 'web');
    }

    /**
     * Test login POST route with invalid credentials shows errors.
     */
    public function test_login_post_route_with_invalid_credentials_shows_errors()
    {
        $response = $this->post(route('login'), [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email' => 'The provided credentials are incorrect.']);
    }

    /**
     * Test logout route redirects to login page.
     */
    public function test_logout_route_redirects_to_login_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->post(route('logout'));
        $response->assertRedirect('/login')->assertSessionHas('success', 'You have been logged out successfully.');
        $this->assertGuest('web');
    }

    /**
     * Test authenticated user can access profile routes.
     */
    public function test_authenticated_user_can_access_profile_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get(route('profile.show'));
        $response->assertStatus(200)->assertViewIs('profile');
    }

    /**
     * Test admin cannot access user profile routes.
     */
    public function test_admin_cannot_access_user_profile_routes()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('profile.show'));
        $response->assertRedirect(route('login')); // Expect redirection
    }

    /**
     * Test unauthenticated user is redirected from profile routes.
     */
    public function test_unauthenticated_user_is_redirected_from_profile_routes()
    {
        $response = $this->get(route('profile.show'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated admin can access admin routes.
     */
    public function test_authenticated_admin_can_access_admin_routes()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('users.index'));
        $response->assertStatus(200)->assertViewIs('user.index');
    }

    /**
     * Test unauthenticated user is redirected from admin routes.
     */
    public function test_unauthenticated_user_is_redirected_from_admin_routes()
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test regular user cannot access admin routes.
     */
    public function test_regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login')); // Expect redirection
    }

    /**
     * Test admin can access admin routes.
     */
    public function test_admin_can_access_admin_routes()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('users.index'));
        $response->assertStatus(200)->assertViewIs('user.index');
    }
}
