<?php

namespace App\Domains\Shared\Events;

use App\Domains\Core\Aggregates\UserAggregate;

class UserUpdatedEvent
{
    public UserAggregate $userAggregate;
    private array $updatedData;

    public function __construct(UserAggregate $userAggregate, array $updatedData)
    {
        $this->userAggregate = $userAggregate;
        $this->updatedData = $updatedData;
    }

    public function getUserAggregate(): UserAggregate
    {
        return $this->userAggregate;
    }

    public function getUpdatedData(): array
    {
        return $this->updatedData;
    }
}  