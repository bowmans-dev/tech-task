<?php

namespace App\Domains\Core\ValueObjects;

class Phone
{
    private string $phone;

    /**
     * Constructor for Phone Value Object.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $phone)
    {
        // Validate phone number
        if (! self::validatePhone($phone)) {
            throw new \InvalidArgumentException('Invalid phone number format or length.');
        }

        $this->phone = $phone;
    }

    /**
     * Validate the phone number format and length.
     */
    private static function validatePhone(string $phone): bool
    {
        // Reject empty strings after trimming
        if (trim($phone) === '') {
            return false;
        }

        // Ensure the phone number matches the desired format
        $isValidFormat = preg_match('/^(\+?[0-9\- ]+|[0-9]{7,20})$/', $phone);

        // Return true only if the length and format are valid
        return $isValidFormat === 1 && strlen($phone) >= 7 && strlen($phone) <= 20;
    }

    /**
     * Get the value of the phone number.
     */
    public function getValue(): string
    {
        return $this->phone;
    }
}
