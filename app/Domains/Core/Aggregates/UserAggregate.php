<?php

namespace App\Domains\Core\Aggregates;

use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\Entities\User;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\ProfilePicture;

class UserAggregate
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public static function create(array $data): self
    {
        // Use the factory method to simplify creation
        $userData = UserData::fromArray($data);

        $user = new User($userData);

        return new self($user);
    }

    public function update(array $data): void
    {
        if (isset($data['first_name'])) {
            $this->user->setFirstName($data['first_name']);
        }

        if (isset($data['last_name'])) {
            $this->user->setLastName($data['last_name']);
        }

        if (isset($data['email'])) {
            $this->user->setEmail(new Email($data['email']));
        }

        if (isset($data['password'])) {
            $this->user->setPassword(new Password($data['password']));
        }

        if (isset($data['phone'])) {
            $this->user->setPhone(new Phone($data['phone']));
        }

        if (isset($data['country'])) {
            $this->user->setCountry(new Country($data['country']));
        }

        if (isset($data['gender'])) {
            $this->user->setGender($data['gender']);
        }

        if (isset($data['profile_picture'])) {
            $this->user->setProfilePicture(new ProfilePicture($data['profile_picture']));
        }
    } 

    public function getProcessedData(): array
    {
        return $this->user->toArray();
    }

    public function getId(): string
    {
        return $this->getProcessedData()['id'];
    }
}
