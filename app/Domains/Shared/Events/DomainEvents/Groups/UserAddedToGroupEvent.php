<?php

namespace App\Domains\Shared\Events\DomainEvents\Groups;

use App\Models\User;
use App\Models\Group;

class UserAddedToGroupEvent
{
    public User $user;
    public Group $group;

    public function __construct(User $user, Group $group)
    {
        $this->user = $user;
        $this->group = $group;
    }
}