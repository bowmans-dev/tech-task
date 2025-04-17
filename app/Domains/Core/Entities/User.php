<?php

namespace App\Domains\Core\Entities;

use App\Domains\Core\DTOs\UserData;

class User
{
    private UserData $data;

    public function __construct(array|UserData $data)
    {
        if (is_array($data)) {
            $this->data = UserData::fromArray($data); // Automatically convert array to UserData
        } else {
            $this->data = $data;
        }
    }


    public function update(array $data): void
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }
        
        $mergedData = array_merge($this->toArray(), $data);
        $this->data = UserData::fromArray($mergedData);
    }


    public function toArray(): array
    {
        return [
            'id' => $this->data->id,
            'first_name' => $this->data->firstName,
            'last_name' => $this->data->lastName,
            'gender' => $this->data->gender,
            'email' => $this->data->email->getValue(),
            'password' => $this->data->password->getValue(),
            'phone' => $this->data->phone?->getValue(),
            'country' => $this->data->country?->getValue(),
            'profile_picture' => $this->data->profilePicture?->getPath(),
        ];
    }
}