<?php

namespace App\Domains\Core\Factories;

use App\Models\User as EloquentUserModel;
use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\ValueObjects\{Email, Password, Phone, Country, ProfilePicture};

class UserModelFactory
{
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
            profilePicture: new ProfilePicture($model->profile_picture ?? 'default_profile_image.webp')
        );
    }
}