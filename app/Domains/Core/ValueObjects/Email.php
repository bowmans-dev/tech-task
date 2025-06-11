<?php

namespace App\Domains\Core\ValueObjects;

class Email
{
    private string $email;

    public function __construct(string $email)
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format.');
        }
        $this->email = $email;
    }


    public function getValue(): string
    {
        return $this->email;
    }


    public function isEqual(Email $otherEmail): bool
    {
        return $this->email === $otherEmail->getValue();
    }
}
