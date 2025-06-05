<?php

namespace App\Domains\Core\DTOs;

use App\Models\User as EloquentUserModel;
use App\Domains\Core\DTOs\UserDataFromArray;
use App\Domains\Core\DTOs\UserDataFromModel;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\ProfilePicture;

class UserData
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $gender,
        public Email $email,
        public Password $password,
        public ?Phone $phone,
        public ?Country $country,
        public ?ProfilePicture $profilePicture = null
    ) {}

    public static function fromArray(array $data): self
    {
        return UserDataFromArray::transform($data);
    }

    public static function fromModel(EloquentUserModel $model): self
    {
        return UserDataFromModel::transform($model);
    }
}
