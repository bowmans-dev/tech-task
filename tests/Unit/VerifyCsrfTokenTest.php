<?php

namespace Tests\Unit;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Tests\TestCase;

class VerifyCsrfTokenTest extends TestCase
{
    private $encrypter;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Encrypter dependency
        $this->encrypter = $this->createMock(Encrypter::class);
    }

    public function test_middleware_invocation()
    {
        // Given: A request with a valid CSRF token
        $request = Request::create('/some-uri', 'POST', [], [], [], ['HTTP_X_CSRF_TOKEN' => csrf_token()]);
        $next = function ($req) {
            return response('OK');
        };

        // When: The middleware is invoked
        $middleware = new VerifyCsrfToken($this->encrypter);
        $response = $middleware->handle($request, $next);

        // Then: The middleware should allow the request through
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_uri_excluded_from_csrf_verification()
    {
        // Given: A middleware instance
        $middleware = new VerifyCsrfToken($this->encrypter);

        // Use reflection to access the protected `$except` property
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);
        $property->setValue($middleware, ['/excluded-uri']); // Set excluded URIs

        // And: A request to an excluded URI
        $request = Request::create('/excluded-uri', 'POST');

        $next = function ($req) {
            return response('OK');
        };

        // When: The middleware is invoked
        $response = $middleware->handle($request, $next);

        // Then: The middleware should allow the request without checking for CSRF
        $this->assertEquals('OK', $response->getContent());
    }
}
