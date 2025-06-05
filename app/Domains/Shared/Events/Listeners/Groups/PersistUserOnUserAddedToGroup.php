<?php
namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Models\User;
use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroupEvent;
use Illuminate\Support\Facades\Log;

class PersistUserOnUserAddedToGroup
{
    public function handle(UserAddedToGroupEvent $event)
    {
        $user = $event->user;
        $group = $event->group;

        $user->groups()->attach($group);
    }
}