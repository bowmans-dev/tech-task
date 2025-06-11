<?php

namespace App\Domains\Core\DTOs;

use App\Models\User as EloquentUserModel;
use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\Factories\UserModelFactory;

class UserDataFromModel
{
    public static function transform(EloquentUserModel $model): UserData
    {
        return UserModelFactory::createFromModel($model);
    }
}