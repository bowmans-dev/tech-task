<?php

namespace App\Domains\Shared\Events\DomainEvents\Groups;

class GroupDeleted
{
    public int $groupId;

    public function __construct(int $groupId)
    {
        $this->groupId = $groupId;
    }
}