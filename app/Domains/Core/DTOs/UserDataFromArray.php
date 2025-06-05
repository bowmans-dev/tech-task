<?php

namespace App\Domains\Core\DTOs;

use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\ProfilePicture;

class UserDataFromArray
{
    public static function transform(array $data): UserData
    {
        return new UserData(
            id: $data['id'] ?? uniqid(),
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            gender: $data['gender'],
            email: new Email($data['email']),
            password: new Password($data['password']),
            phone: new Phone($data['phone']),
            country: new Country($data['country']),
            profilePicture: isset($data['profile_picture']) && !empty($data['profile_picture'])
                ? new ProfilePicture($data['profile_picture'])
                : new ProfilePicture('default_profile_image.webp')
        );
    }
}
