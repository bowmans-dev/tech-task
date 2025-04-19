<?php

namespace App\Domains\Core\Aggregates;

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
        return new self(new User($data));
    }


    public function update(array $data): void
    {
        $this->user->update($data);
    }


    public function getProcessedData(): array
    {
        return $this->user->toArray();
    }

}