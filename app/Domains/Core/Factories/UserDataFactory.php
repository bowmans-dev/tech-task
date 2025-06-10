<?php
namespace App\Domains\Core\Factories;

use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\ProfilePicture;

class UserDataFactory
{
    public static function createFromArray(array $data, ?UserData $existingUserData = null): UserData
    {
        return new UserData(
            id: $data['id'] ?? ($existingUserData ? $existingUserData->id : uniqid()),  
            firstName: $data['first_name'] ?? ($existingUserData ? $existingUserData->firstName : null),
            lastName: $data['last_name'] ?? ($existingUserData ? $existingUserData->lastName : null),
            gender: $data['gender'] ?? ($existingUserData ? $existingUserData->gender : null),
            email: new Email($data['email'] ?? ($existingUserData ? $existingUserData->email->getValue() : null)),
            password: new Password($data['password'] ?? ($existingUserData ? $existingUserData->password->getValue() : null)),
            phone: new Phone($data['phone'] ?? ($existingUserData ? $existingUserData->phone->getValue() : null)),
            country: new Country($data['country'] ?? ($existingUserData ? $existingUserData->country->getValue() : null)),
            profilePicture: new ProfilePicture($data['profile_picture'] ?? ($existingUserData ? $existingUserData->profilePicture->getPath() : 'default_profile_image.webp'))
        );
    }
}