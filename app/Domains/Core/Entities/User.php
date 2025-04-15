<?php

namespace App\Domains\Core\Entities;

use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\ProfilePicture;

class User
{
    private string $id;

    private string $firstName;

    private string $lastName;

    private string $gender;

    private Email $email;

    private Password $password;

    private Phone $phone;

    private Country $country;

    private ?ProfilePicture $profilePicture;

    public function __construct(UserData $data)
    {
        $this->id = $data->id;
        $this->firstName = $data->firstName;
        $this->lastName = $data->lastName;
        $this->gender = $data->gender;
        $this->email = $data->email;
        $this->password = $data->password;
        $this->phone = $data->phone;
        $this->country = $data->country;
        $this->profilePicture = $data->profilePicture;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function setPassword(Password $password): void
    {
        $this->password = $password;
    }

    public function setPhone(?Phone $phone): void
    {
        $this->phone = $phone;
    }

    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    public function setProfilePicture(?ProfilePicture $profilePicture): void
    {
        $this->profilePicture = $profilePicture;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'gender' => $this->gender,
            'email' => $this->email->getValue(),
            'password' => $this->password->getValue(),
            'phone' => $this->phone?->getValue(),
            'country' => $this->country?->getValue(),
            'profile_picture' => $this->profilePicture?->getPath(),
        ];
    }
}
