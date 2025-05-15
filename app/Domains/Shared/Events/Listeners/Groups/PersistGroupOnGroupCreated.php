<?php
namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Models\User;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreatedEvent;
use HotwiredLaravel\TurboLaravel\Facades\TurboStream;
use Illuminate\Support\Facades\Log;

class PersistGroupOnGroupCreated
{
    public function handle(GroupCreatedEvent $event)
    {
        $group = $event->group;

        $group->save();

        Log::info('Group created and persisted successfully.', [
            'group_id' => $group->id,
            'group_name' => $group->name,
        ]);
    }
}

