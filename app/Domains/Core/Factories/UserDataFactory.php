<?php
namespace App\Domains\Core\Factories;

use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\ValueObjects\{Email, Password, Phone, Country, ProfilePicture};

class UserDataFactory
{
    public static function createFromArray(array $data, ?UserData $existingUserData = null): UserData
    {
        return new UserData(
            id: $data['id'] ?? $existingUserData?->id ?? uniqid(),
            firstName: $data['first_name'] ?? $existingUserData?->firstName,
            lastName: $data['last_name'] ?? $existingUserData?->lastName,
            gender: $data['gender'] ?? $existingUserData?->gender,
            email: new Email($data['email'] ?? $existingUserData?->email->getValue()),
            password: new Password($data['password'] ?? $existingUserData?->password->getValue()),
            phone: new Phone($data['phone'] ?? $existingUserData?->phone?->getValue()),
            country: new Country($data['country'] ?? $existingUserData?->country?->getValue()),
            profilePicture: new ProfilePicture($data['profile_picture'] ?? $existingUserData?->profilePicture?->getPath() ?? 'default_profile_image.webp'),
        );
    }
}