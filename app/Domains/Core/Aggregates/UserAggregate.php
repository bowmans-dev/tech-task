<?php

namespace App\Domains\Core\Aggregates;

use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\Entities\User;


class UserAggregate
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public static function create(array $data): self
    {
        $userData = UserData::fromArray($data);
        $user = new User($userData);
        return new self($user);
    }


    public function update(array $data): void
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Update only the fields provided in $data, keeping all other existing fields unchanged.
        $updatedData = array_merge($this->user->toArray(), $data);
        $this->user->update(UserData::fromArray($updatedData));
    }


    public function getProcessedData(): array
    {
        return $this->user->toArray();
    }

    
    public function getId(): string
    {
        return $this->user->toArray()['id'];
    }
}