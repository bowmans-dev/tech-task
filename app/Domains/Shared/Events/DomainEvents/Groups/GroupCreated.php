<?php

namespace App\Domains\Shared\Events\DomainEvents\Groups;

use App\Models\Group;

class GroupCreated
{
    public Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}