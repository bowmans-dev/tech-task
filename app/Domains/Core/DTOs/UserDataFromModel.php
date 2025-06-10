<?php

namespace App\Domains\Core\DTOs;

use App\Models\User as EloquentUserModel;
use App\Domains\Core\Factories\UserDataFactory;

class UserDataFromModel
{
    public static function transform(EloquentUserModel $model): UserData
    {
        return UserDataFactory::createFromModel($model);
    }
}