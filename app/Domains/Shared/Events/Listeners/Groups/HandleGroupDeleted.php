<?php

namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupDeleted;

class HandleGroupDeleted
{
    public function handle(GroupDeleted $event)
    {
        $groupId = $event->groupId;
        $group = Group::findOrFail($groupId);
        $group->delete();
    }
}