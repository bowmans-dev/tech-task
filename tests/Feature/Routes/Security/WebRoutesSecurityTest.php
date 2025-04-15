<?php

namespace Tests\Feature\Routes\Security;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WebRoutesSecurityTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test unauthenticated users cannot access the profile route.
     */
    public function test_unauthenticated_user_cannot_access_profile()
    {
        $response = $this->get(route('profile.show'));
        $response->assertRedirect(route('login')); // Redirect to login
    }

    /**
     * Test authenticated user can access their profile.
     */
    public function test_authenticated_user_can_access_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get(route('profile.show'));
        $response->assertStatus(200);
    }

    /**
     * Test regular user cannot access admin routes.
     */
    public function test_regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web'); // Simulate regular user login

        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login')); // Expect redirect to login page
    }

    /**
     * Test unauthenticated user cannot access admin routes.
     */
    public function test_unauthenticated_user_cannot_access_admin_routes()
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login')); // Redirect to login
    }

    /**
     * Test admin can access admin routes.
     */
    public function test_admin_can_access_admin_routes()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); // Simulate admin authentication

        $response = $this->get(route('users.index'));
        $response->assertStatus(200);
    }

    /**
     * Test POST route requires CSRF token.
     */
    public function test_post_route_requires_csrf_token()
    {
        $response = $this->post(route('logout'), [], ['X-CSRF-TOKEN' => 'invalid-token']);
        $response->assertRedirect(route('login')); // Expect redirect to login page
    }
}
