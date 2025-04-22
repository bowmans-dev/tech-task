<?php
namespace Tests\Feature\Controllers\Api;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test admin login success via API.
     */
    public function test_admin_login_success()
    {
        // Create an admin with hashed password
        $admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt to log in
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Assert response structure
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'token', 
                ]);


        $token = $response->json('token');
        $this->assertNotNull($token); 
    }


    /**
     * Test admin login failure via API.
     */
    public function test_admin_login_failure()
    {
        // Attempt login with invalid credentials
        $response = $this->postJson('/api/login', [
            'email' => 'wrongemail@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert failure response
        $response->assertStatus(401)
                ->assertJsonPath('message', 'Invalid credentials provided.');

        // Ensure token is not returned
        $response->assertJsonMissing(['token']);
    }

    /**
     * Test admin logout via API.
     */
    

     public function test_admin_logout()
    {
        $admin = Admin::factory()->create(['password' => Hash::make('password123')]);
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJsonPath('message', 'Admin logged out successfully.');

        $this->expectException(\Tymon\JWTAuth\Exceptions\TokenInvalidException::class);
        JWTAuth::setToken($token)->authenticate();
    }

    public function test_user_logout()
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJsonPath('message', 'User logged out successfully.');

        $this->expectException(\Tymon\JWTAuth\Exceptions\TokenInvalidException::class);
        JWTAuth::setToken($token)->authenticate();
    }

    /**
     * Test sending password reset link via API.
     */
    public function test_send_reset_link_success()
    {
        $admin = User::factory()->create(['email' => 'user@test.com']);

        $response = $this->postJson('/api/password/email', ['email' => 'user@test.com']);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Reset password link sent successfully.');
    }

    /**
     * Test sending password reset link via API fails for non existing email.
     */
    public function test_send_reset_link_failure_for_non_existant_email()
    {
        $response = $this->postJson('/api/password/email', ['email' => 'nonexistent@test.com']);

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'User not found.');
    }

}