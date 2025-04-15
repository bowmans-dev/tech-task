<?php

namespace Tests\Unit;

use App\Domains\Core\ValueObjects\Password;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    public function test_password_is_hashed_on_creation()
    {
        // Given: A plain password
        $plainPassword = 'strongpassword';

        // When: The Password value object is created
        $password = new Password($plainPassword);

        // Then: The password should be hashed
        $this->assertNotEquals($plainPassword, $password->getValue());
        $this->assertTrue(password_verify($plainPassword, $password->getValue()));
    }

    public function test_hashed_password_is_accepted()
    {
        // Given: A pre-hashed password
        $hashedPassword = password_hash('strongpassword', PASSWORD_DEFAULT);

        // When: The Password value object is created with `isHashed = true`
        $password = new Password($hashedPassword, true);

        // Then: The password value should match the hashed password directly
        $this->assertEquals($hashedPassword, $password->getValue());
    }

    public function test_exception_is_thrown_for_short_password()
    {
        // Given: A password shorter than 8 characters
        $shortPassword = 'short';

        // Expect: An InvalidArgumentException to be thrown
        $this->expectException(\InvalidArgumentException::class);

        // When: The Password value object is created
        new Password($shortPassword);
    }

    public function test_verify_plain_password()
    {
        // Given: A plain password
        $plainPassword = 'securepassword';

        // When: The Password value object is created
        $password = new Password($plainPassword);

        // Then: `verify` should return true for the matching plain password
        $this->assertTrue($password->verify($plainPassword));

        // And: `verify` should return false for non-matching passwords
        $this->assertFalse($password->verify('wrongpassword'));
    }
}
