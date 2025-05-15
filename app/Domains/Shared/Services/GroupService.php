<?php

namespace App\Domains\Shared\Services;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreatedEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupDeletedEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroupEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\UserRemovedFromGroupEvent;
use App\Domains\Shared\Events\DomainEventPublisher;

class GroupService
{

    /**
     * Publish an event for creating a group.
     *
     * @param string $name
     * @return JsonResponse
     * 
     * Returns JSON response to provide the frontend with
     * the new group ID alongside the turbo stream
     */
    public function createGroup($name)
    {
        $group = new Group(['name' => $name]);

        DomainEventPublisher::publish(new GroupCreatedEvent($group));

        $groups = Group::with('users')->get();

        return response([
            'turbo_stream' => turbo_stream()
                ->target('groups-accordion')
                ->action('replace')
                ->view('components.navigation.group-accordion', compact('groups'))
                ->render(),
            'new_group_id' => $group->id,
        ], 200)->header('Content-Type', 'application/json');

    }

    /**
     * Publish an event for adding a user to a group.
     *
     * @param int $userId
     * @param int $groupId
     * @return PendingTurboStreamResponse
     */
    public function addUserToGroup($userId, $groupId)
    {
        $user = User::findOrFail($userId);
        $group = Group::findOrFail($groupId);

        DomainEventPublisher::publish(new UserAddedToGroupEvent($user, $group));

        $groups = Group::with('users')->get();

        return turbo_stream()
            ->target('groups-accordion')
            ->action('replace')
            ->view('components.navigation.group-accordion', compact('groups', 'user', 'group'));

    }

    /**
     * Delete a group and publish an event for its deletion.
     *
     * @param int $groupId
     * @return PendingTurboStreamResponse
     */
    public function deleteGroup($groupId)
    {

        DomainEventPublisher::publish(new GroupDeletedEvent($groupId));

        $groups = Group::with('users')->get();

        return turbo_stream()
            ->target('groups-accordion')
            ->action('replace')
            ->view('components.navigation.group-accordion', compact('groups'));
    }
    

    /**
     * Publish an event for removing a user from a group.
     *
     * @param int $userId
     * @param int $groupId
     * @return PendingTurboStreamResponse
     */
    public function removeUserFromGroup($userId, $groupId)
    {
        $user = User::findOrFail($userId);
        $group = Group::findOrFail($groupId);

        DomainEventPublisher::publish(new UserRemovedFromGroupEvent($user, $group));

        $groups = Group::with('users')->get();

        return turbo_stream()
            ->target('groups-accordion')
            ->action('replace')
            ->view('components.navigation.group-accordion', compact('groups'));
    }
}