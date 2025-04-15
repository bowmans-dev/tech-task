<?php

namespace App\Domains\Shared\Events;

use App\Domains\Core\Aggregates\UserAggregate;

class UserCreatedEvent
{
    public UserAggregate $userAggregate;

    public function __construct(UserAggregate $userAggregate)
    {
        $this->userAggregate = $userAggregate;
    }
}
