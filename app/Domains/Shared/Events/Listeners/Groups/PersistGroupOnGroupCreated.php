<?php
namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreated;

class PersistGroupOnGroupCreated
{
    public function handle(GroupCreated $event)
    {
        $group = $event->group;
        $group->save();
    }
}

