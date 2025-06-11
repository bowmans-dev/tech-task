<?php

namespace App\Domains\Core\DTOs;

use App\Models\User as EloquentUserModel;
use App\Domains\Core\DTOs\{BaseDTO, UserDataFromArray, UserDataFromModel, UserDataToArray};
use App\Domains\Core\ValueObjects\{Email, Password, Phone, Country, ProfilePicture};

class UserData extends BaseDTO
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $gender,
        public Email $email,
        public Password $password,
        public ?Phone $phone = null,
        public ?Country $country = null,
        public ?ProfilePicture $profilePicture = new ProfilePicture('default_profile_image.webp')
    ) {
        $this->validate();
        parent::__construct();
    }


    protected function validate(): void
    {
        foreach (['firstName', 'lastName', 'gender'] as $field) {
            Validators::notEmptyString($this->$field, $field);
        }

        foreach ([
            'email' => Email::class,
            'password' => Password::class,
            'phone' => Phone::class,
            'country' => Country::class,
            'profilePicture' => ProfilePicture::class
        ] as $field => $type) {
            Validators::instanceOf($this->$field, $type, $field);
        }
    }

    
    public static function fromArray(array $data): self { return UserDataFromArray::transform($data); }
    public static function fromModel(EloquentUserModel $model): self { return UserDataFromModel::transform($model); }
    public function toArray(): array { return UserDataToArray::toArray($this); }
}