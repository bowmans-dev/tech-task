<?php

namespace App\Domains\Shared\Events\DomainEvents\Users;

use App\Domains\Core\Aggregates\UserAggregate;

class UserDeletedEvent
{
    public UserAggregate $userAggregate;

    public function __construct(UserAggregate $userAggregate)
    {
        $this->userAggregate = $userAggregate;
    }
}
