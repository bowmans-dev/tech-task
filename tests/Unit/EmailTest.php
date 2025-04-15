<?php

namespace Tests\Unit;

use App\Domains\Core\ValueObjects\Email;
use Tests\TestCase;

class EmailTest extends TestCase
{
    public function test_can_create_email_with_valid_format()
    {
        // Arrange
        $validEmail = 'test@example.com';

        // Act
        $email = new Email($validEmail);

        // Assert
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals($validEmail, $email->getValue());
    }

    public function test_throws_exception_for_invalid_email_format()
    {
        // Arrange
        $invalidEmail = 'invalid-email';

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format.');

        // Act
        new Email($invalidEmail);
    }

    public function test_throws_exception_for_empty_email()
    {
        // Arrange
        $emptyEmail = '';

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format.');

        // Act
        new Email($emptyEmail);
    }

    public function test_is_equal_returns_true_for_same_email()
    {
        // Arrange
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');

        // Act
        $result = $email1->isEqual($email2);

        // Assert
        $this->assertTrue($result);
    }

    public function test_is_equal_returns_false_for_different_emails()
    {
        // Arrange
        $email1 = new Email('test@example.com');
        $email2 = new Email('another@example.com');

        // Act
        $result = $email1->isEqual($email2);

        // Assert
        $this->assertFalse($result);
    }
}
