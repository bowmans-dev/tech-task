<?php
namespace App\Domains\Core\Factories;

use App\Models\User as EloquentUserModel;
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
            email: new Email($data['email'] ?? ($existingUserData ? $existingUserData->getEmail()->getValue() : null)),
            password: new Password($data['password'] ?? ($existingUserData ? $existingUserData->getPassword()->getValue() : null)),
            phone: new Phone($data['phone'] ?? ($existingUserData ? $existingUserData->getPhone()->getValue() : null)),
            country: new Country($data['country'] ?? ($existingUserData ? $existingUserData->getCountry()->getValue() : null)),
            profilePicture: new ProfilePicture($data['profile_picture'] ?? ($existingUserData ? $existingUserData->getProfilePicture()->getPath() : 'default_profile_image.webp'))
        );
    }

    public static function createFromModel(EloquentUserModel $model): UserData
    {
        return new UserData(
            id: $model->id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            gender: $model->gender,
            email: new Email($model->email),
            password: new Password($model->password, true), // Already hashed
            phone: $model->phone ? new Phone($model->phone) : null,
            country: $model->country ? new Country($model->country) : null,
            profilePicture: $model->profile_picture
                ? new ProfilePicture($model->profile_picture)
                : new ProfilePicture('default_profile_image.webp')
        );
    }
}