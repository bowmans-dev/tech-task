<?php

namespace App\Domains\Core\DTOs;

use App\Models\User as EloquentUserModel;
use App\Domains\Core\DTOs\BaseDTO;
use App\Domains\Core\DTOs\UserDataFromArray;
use App\Domains\Core\DTOs\UserDataFromModel;
use App\Domains\Core\DTOs\UserDataToArray;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\ProfilePicture;

class UserData extends BaseDTO
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
    ) {
        // Basic content validation beyond type checks
        Validators::notEmptyString($this->firstName, 'firstName');
        Validators::notEmptyString($this->lastName, 'lastName');
        Validators::notEmptyString($this->gender, 'gender');
        Validators::instanceOf($this->email, Email::class, 'email');
        Validators::instanceOf($this->password, Password::class, 'password');
        Validators::instanceOf($this->phone, Phone::class, 'phone');
        Validators::instanceOf($this->country, Country::class, 'country');
        Validators::instanceOf($this->profilePicture, ProfilePicture::class, 'profilePicture');

        parent::__construct();
    }

    public static function fromArray(array $data): self
    {
        return UserDataFromArray::transform($data);
    }

    public function toArray(): array
    {
        return UserDataToArray::toArray($this);
    }

    public static function fromModel(EloquentUserModel $model): self
    {
        return UserDataFromModel::transform($model);
    }

    public function getId(): string { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getGender(): string { return $this->gender; }
    public function getEmail(): Email { return $this->email; }
    public function getPassword(): Password { return $this->password; }
    public function getPhone(): ?Phone { return $this->phone; }
    public function getCountry(): ?Country { return $this->country; }
    public function getProfilePicture(): ?ProfilePicture { return $this->profilePicture; }
}