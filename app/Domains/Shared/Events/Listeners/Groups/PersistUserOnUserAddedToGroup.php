<?php
namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroup;

class PersistUserOnUserAddedToGroup
{
    public function handle(UserAddedToGroup $event)
    {
        $user = $event->user;
        $group = $event->group;
        $user->groups()->attach($group);
    }
}