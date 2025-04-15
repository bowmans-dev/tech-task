<?php
namespace App\Domains\Core\ValueObjects;

class Password
{
    private ?string $password; // Allow null values

    public function __construct(?string $password, bool $isHashed = false)
    {
        if ($password === null) {
            $this->password = null; // Set to null if no password is provided
            return;
        }

        if (!$isHashed && strlen($password) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long.');
        }

        $this->password = $isHashed ? $password : $this->hashPassword($password);
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getValue(): ?string
    {
        return $this->password; // Return null if no password is set
    }

    public function verify(string $plainPassword): bool
    {
        if ($this->password === null) {
            throw new \LogicException('Cannot verify a null password.');
        }

        return password_verify($plainPassword, $this->password);
    }
}
