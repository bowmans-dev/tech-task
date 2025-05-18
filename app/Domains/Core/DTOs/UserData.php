<?php

namespace App\Domains\Core\DTOs;

use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\ProfilePicture;
use App\Models\User as EloquentUserModel;

class UserData
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $gender,
        public Email $email,
        public Password $password,
        public Phone $phone,
        public Country $country,
        public ?ProfilePicture $profilePicture = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? uniqid(),
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            gender: $data['gender'],
            email: new Email($data['email']),
            password: new Password($data['password']),
            phone: new Phone($data['phone']),
            country: new Country($data['country']),
            profilePicture: isset($data['profile_picture']) && !empty($data['profile_picture'])
                ? new ProfilePicture($data['profile_picture'])
                : new ProfilePicture('default_profile_image.webp')
        );
    }

    public static function fromModel(EloquentUserModel $model): self
    {
        return new self(
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
