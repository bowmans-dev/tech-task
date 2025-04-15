<?php

namespace Tests\Unit;

use App\Domains\Core\ValueObjects\Phone;
use Tests\TestCase;

class PhoneTest extends TestCase
{
    public function test_valid_phone_number_is_accepted()
    {
        // Given: A valid phone number
        $validPhone = '07123456789';

        // When: Creating the Phone value object
        $phone = new Phone($validPhone);

        // Then: The phone value should be set correctly
        $this->assertEquals($validPhone, $phone->getValue());
    }

    public function test_invalid_phone_number_throws_exception()
    {
        // Given: An invalid phone number exceeding 20 characters
        $invalidPhone = '123456789012345678901'; // 21 characters

        // Expect: An InvalidArgumentException to be thrown
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number format or length.');

        // When: Creating the Phone value object
        new Phone($invalidPhone);
    }

    public function test_empty_phone_number_is_rejected()
    {
        // Given: An empty string as the phone number
        $nullablePhone = '';

        // Then: Expect an exception to be thrown
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number format or length.');

        // When: Attempting to create the Phone value object
        new Phone($nullablePhone);
    }

    public function test_phone_number_with_special_character_country_code_is_accepted()
    {
        // Given: A valid phone number with special characters
        $specialPhone = '+447123456789';

        // When: Creating the Phone value object
        $phone = new Phone($specialPhone);

        // Then: The phone value should be set correctly
        $this->assertEquals($specialPhone, $phone->getValue());
    }

    public function test_phone_number_with_special_character_dashes_is_accepted()
    {
        // Given: A valid phone number with special characters
        $specialPhone = '07123-456-789';

        // When: Creating the Phone value object
        $phone = new Phone($specialPhone);

        // Then: The phone value should be set correctly
        $this->assertEquals($specialPhone, $phone->getValue());
    }

    public function test_phone_number_at_maximum_length_is_accepted()
    {
        // Given: A phone number that is exactly 20 characters
        $maxLengthPhone = '12345678901234567890';

        // When: Creating the Phone value object
        $phone = new Phone($maxLengthPhone);

        // Then: The phone value should be set correctly
        $this->assertEquals($maxLengthPhone, $phone->getValue());
    }
}
