<?php

namespace App\Domains\Core\ValueObjects;

class Phone
{
    private string $phone;

    public function __construct(string $phone)
    {

        if (! self::validatePhone($phone)) {
            throw new \InvalidArgumentException('Invalid phone number format or length.');
        }

        $this->phone = $phone;
    }


    private static function validatePhone(string $phone): bool
    {

        if (trim($phone) === '') {
            return false;
        }

        // Match a phone number starting with an optional "+" followed by digits, spaces, or dashes, or a sequence of 7–20 digits
        $isValidFormat = preg_match('/^(\+?[0-9\- ]+|[0-9]{7,20})$/', $phone);

        return $isValidFormat === 1 && strlen($phone) >= 7 && strlen($phone) <= 20;
    }

    
    public function getValue(): string
    {
        return $this->phone;
    }
}
