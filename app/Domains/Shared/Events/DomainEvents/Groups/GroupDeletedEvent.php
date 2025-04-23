<?php

namespace App\Domains\Shared\Events\DomainEvents\Groups;

class GroupDeletedEvent
{
    public int $groupId;

    /**
     * Constructor for the event.
     *
     * @param int $groupId
     */
    public function __construct(int $groupId)
    {
        $this->groupId = $groupId;
    }
}