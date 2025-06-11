<?php

namespace App\Domains\Core\Entities;

use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\DTOs\UserDataFromArray;

class User
{
    private UserData $data;

    public function __construct(array|UserData $data)
    {
        $this->data = is_array($data) ? UserDataFromArray::transform($data) : $data;
    }


    public function update(array $data): void
    {
        $this->data = UserDataFromArray::transform($data, $this->data);
    }

    
    public function toArray(): array
    {
        return $this->data->toArray();
    }

}