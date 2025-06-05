<?php
namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Models\User;
use App\Domains\Shared\Events\DomainEvents\Groups\UserRemovedFromGroupEvent;
use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
use Illuminate\Support\Facades\Log;

class HandleUserRemovedFromGroup
{
    public function handle(UserRemovedFromGroupEvent $event)
    {
        $user = $event->user;
        $group = $event->group;

        $user->groups()->detach($group);
    }
}