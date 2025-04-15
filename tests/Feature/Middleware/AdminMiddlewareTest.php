<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Models\Admin; // Adjust this if the Admin model is in a different namespace
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase; // Needed for authentication mocking

class AdminMiddlewareTest extends TestCase
{
    /**
     * Test that the middleware allows access for authenticated admins.
     */
    public function test_admin_middleware_allows_authenticated_admin()
    {
        // Simulate an authenticated admin user
        $admin = Admin::factory()->create(); // Assumes you have a factory for Admin
        Auth::guard('admin')->login($admin);

        $middleware = new AdminMiddleware;
        $request = Request::create('/admin', 'GET');

        // Define a "next" closure
        $next = function ($request) {
            return new Response('OK', 200);
        };

        $response = $middleware->handle($request, $next);

        // Assert that the middleware allows access
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Test that the middleware returns JSON response for unauthenticated API requests.
     */
    public function test_admin_middleware_returns_json_for_unauthorised_api_request()
    {
        // Simulate an unauthenticated request expecting JSON
        $request = Request::create('/api/admin', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

        $middleware = new AdminMiddleware;

        // Define a "next" closure (not called due to authentication failure)
        $next = function ($request) {
            return new Response('OK', 200);
        };

        $response = $middleware->handle($request, $next);

        // Assert that the response is a 403 JSON response
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals(['message' => 'Unauthorized'], json_decode($response->getContent(), true));
    }

    /**
     * Test that the middleware redirects unauthenticated web requests to login.
     */
    public function test_admin_middleware_redirects_unauthenticated_web_request()
    {
        // Simulate an unauthenticated web request
        $request = Request::create('/admin', 'GET');

        $middleware = new AdminMiddleware;

        // Define a "next" closure (not called due to authentication failure)
        $next = function ($request) {
            return new Response('OK', 200);
        };

        $response = $middleware->handle($request, $next);

        // Assert that the response is a redirect to the login page
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString(route('login'), $response->headers->get('Location'));
        $this->assertEquals(session('error'), 'You must log in as an admin to access this page.');
    }
}
