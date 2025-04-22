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

        // Attach user to group in the database
        $user->groups()->attach($group);

        // Optionally log the action
        Log::info('User added to group and persisted successfully.', [
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
    }
}