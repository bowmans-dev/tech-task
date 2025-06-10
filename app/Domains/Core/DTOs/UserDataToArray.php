<?php

namespace App\Domains\Core\DTOs;

use App\Domains\Core\DTOs\UserData;

class UserDataToArray
{
    public static function toArray(UserData $data): array
    {
        return [
            'id' => $data->id,
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'gender' => $data->gender,
            'email' => $data->email->getValue(),
            'password' => $data->password->getValue(),
            'phone' => $data->phone?->getValue(),
            'country' => $data->country?->getValue(),
            'profile_picture' => $data->profilePicture?->getPath(),
        ];
    }
}