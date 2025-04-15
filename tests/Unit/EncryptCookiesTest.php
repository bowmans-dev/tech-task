<?php

namespace Tests\Unit;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class EncryptCookiesTest extends TestCase
{
    public function test_encrypts_cookies_excluding_excepted_cookies()
    {
        // Arrange: Create an Encrypter instance
        $encrypter = new Encrypter(Str::random(32), config('app.cipher'));
        $middleware = new EncryptCookies($encrypter);

        // Use reflection to set the protected $except property
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);
        $property->setValue($middleware, ['plain_cookie']); // Exclude this cookie from encryption

        $request = Request::create('/', 'GET');
        $request->cookies->set('encrypted_cookie', 'value_to_encrypt');
        $request->cookies->set('plain_cookie', 'plain_value');

        $response = new Response;
        $response->headers->setCookie(cookie('encrypted_cookie', 'value_to_encrypt'));
        $response->headers->setCookie(cookie('plain_cookie', 'plain_value'));

        // Act: Apply the middleware
        $handledResponse = $middleware->handle($request, function ($req) use ($response) {
            return $response;
        });

        // Assert: Verify the encryption behavior
        $encryptedCookies = $handledResponse->headers->getCookies();
        $encryptedCookieFound = false;
        $plainCookieFound = false;

        foreach ($encryptedCookies as $cookie) {
            if ($cookie->getName() === 'encrypted_cookie') {
                $encryptedCookieFound = true;
                $this->assertNotEquals('value_to_encrypt', $cookie->getValue()); // Should be encrypted
            }

            if ($cookie->getName() === 'plain_cookie') {
                $plainCookieFound = true;
                $this->assertEquals('plain_value', $cookie->getValue()); // Should not be encrypted
            }
        }

        $this->assertTrue($encryptedCookieFound, 'Encrypted cookie should be present and encrypted.');
        $this->assertTrue($plainCookieFound, 'Plain cookie should be present and not encrypted.');
    }

    public function test_middleware_invokes_parent_handle_method()
    {
        // Arrange: Create an Encrypter instance
        $encrypter = new Encrypter(Str::random(32), config('app.cipher'));

        // Instantiate the middleware with the Encrypter
        $middleware = new EncryptCookies($encrypter);

        // Simulate a request and response
        $request = Request::create('/', 'GET');
        $response = new Response;

        // Act
        $handledResponse = $middleware->handle($request, function ($req) use ($response) {
            return $response;
        });

        // Assert: Verify the response is passed through unchanged
        $this->assertSame($response, $handledResponse, 'Middleware should invoke the parent handle method.');
    }
}
