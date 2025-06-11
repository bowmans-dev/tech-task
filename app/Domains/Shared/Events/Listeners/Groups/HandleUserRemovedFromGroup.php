<?php
namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Domains\Shared\Events\DomainEvents\Groups\UserRemovedFromGroup;

class HandleUserRemovedFromGroup
{
    public function handle(UserRemovedFromGroup $event)
    {
        $user = $event->user;
        $group = $event->group;
        $user->groups()->detach($group);
    }
}