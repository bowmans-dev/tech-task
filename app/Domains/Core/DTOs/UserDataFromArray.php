<?php

namespace App\Domains\Core\DTOs;

use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\Factories\UserDataFactory; // Import the factory

class UserDataFromArray
{
    public static function transform(array $data, ?UserData $existingUserData = null): UserData
    {
        return UserDataFactory::createFromArray($data, $existingUserData);
    }
}