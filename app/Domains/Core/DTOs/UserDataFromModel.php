<?php

namespace App\Domains\Core\DTOs;

use App\Models\User as EloquentUserModel;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\ProfilePicture;

class UserDataFromModel
{
    public static function transform(EloquentUserModel $model): UserData
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
