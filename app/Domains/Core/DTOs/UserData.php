<?php

namespace App\Domains\Core\DTOs;

use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\ProfilePicture;

class UserData
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $gender,
        public Email $email,
        public Password $password,
        public Phone $phone,
        public Country $country,
        public ?ProfilePicture $profilePicture = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? uniqid(), // Generate ID if not provided
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            gender: $data['gender'],
            email: new Email($data['email']),
            password: new Password($data['password']),
            phone: new Phone($data['phone']),
            country: new Country($data['country']),
            profilePicture: isset($data['profile_picture']) ? new ProfilePicture($data['profile_picture']) : null
        );
    }
}
