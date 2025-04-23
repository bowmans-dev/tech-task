<?php

namespace App\Domains\Shared\Events\Listeners\Groups;

use App\Models\Group;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupDeletedEvent;
use Illuminate\Support\Facades\Log;

class HandleGroupDeleted
{
    /**
     * Handle the group deleted event.
     *
     * @param \App\Domains\Shared\Events\DomainEvents\Groups\GroupDeletedEvent $event
     * @return void
     */
    public function handle(GroupDeletedEvent $event)
    {
        // Retrieve the group ID from the event
        $groupId = $event->groupId;

        try {
            // Find the group by ID
            $group = Group::findOrFail($groupId);

            // Perform the deletion
            $group->delete();

            // Optionally log the action
            Log::info('Group deleted successfully.', [
                'group_id' => $groupId,
                'group' => $group->name,
            ]);
        } catch (\Exception $e) {
            // Log any errors encountered during deletion
            Log::error('Failed to delete group.', [
                'group_id' => $groupId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}