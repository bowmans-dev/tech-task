<?php

namespace Tests\Unit;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Register a route to test the middleware
        Route::middleware(AdminMiddleware::class)->get('/admin/test', function () {
            return response()->json(['message' => 'Welcome, Admin!']);
        });
    }

    public function test_redirects_to_login_for_web_requests_when_not_authenticated()
    {
        // Simulate an unauthenticated request
        Auth::shouldReceive('guard->check')->andReturn(false);

        $response = $this->get('/admin/test');

        // Assert that the user is redirected to the login page
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'You must log in as an admin to access this page.');
    }

    public function test_returns_unauthorized_json_for_api_requests_when_not_authenticated()
    {
        // Simulate an unauthenticated API request
        Auth::shouldReceive('guard->check')->andReturn(false);

        $response = $this->getJson('/admin/test');

        // Assert that the response is a 403 Unauthorized error with a JSON body
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Unauthorized']);
    }

    public function test_allows_request_to_proceed_when_authenticated()
    {
        // Simulate an authenticated admin user
        Auth::shouldReceive('guard->check')->andReturn(true);

        $response = $this->get('/admin/test');

        // Assert that the request proceeds to the next middleware
        $response->assertOk();
        $response->assertJson(['message' => 'Welcome, Admin!']);
    }
}
