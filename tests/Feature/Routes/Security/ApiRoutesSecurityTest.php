<?php

namespace Tests\Feature\Routes\Security;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiRoutesSecurityTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test accessing admin routes without authentication.
     */
    public function test_access_admin_route_without_authentication_fails()
    {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(403) // Unauthorized
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    /**
     * Test accessing admin routes with regular user authentication fails.
     */
    public function test_access_admin_route_with_regular_user_authentication_fails()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web'); // Simulate a regular user login

        $response = $this->postJson('/api/users', []);

        $response->assertStatus(403) // Unauthorized
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    /**
     * Test creating user with invalid email format fails.
     */
    public function test_create_user_fails_with_invalid_email_format()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); // Simulate admin authentication

        $response = $this->postJson('/api/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email-format',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422) // Unprocessable Entity
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test unauthorized access to user profile.
     */
    public function test_access_user_profile_without_authentication_fails()
    {
        $response = $this->getJson(route('api.profile.show'));

        $response->assertStatus(401) // Unauthorized
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
