<?php

namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupDeletedEvent;
use Illuminate\Support\Facades\Log;

class HandleGroupDeleted
{
    public function handle(GroupDeletedEvent $event)
    {
        $groupId = $event->groupId;

        try {

            $group = Group::findOrFail($groupId);

            $group->delete();

        } catch (\Exception $e) {
            Log::error('Failed to delete group.', [
                'group_id' => $groupId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}