<?php

namespace App\Domains\Shared\Services;

use App\Models\User;
use App\Models\Group;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreatedEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupDeletedEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroupEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\UserRemovedFromGroupEvent;
use App\Domains\Shared\Events\DomainEventPublisher;

class GroupService
{
    /**
     * Publish an event for adding a user to a group.
     *
     * @param int $userId
     * @param int $groupId
     */
    public function addUserToGroup($userId, $groupId)
    {
        $user = User::findOrFail($userId);
        $group = Group::findOrFail($groupId);

        DomainEventPublisher::publish(new UserAddedToGroupEvent($user, $group));

        $groups = Group::with('users')->get();

        return response(
            turbo_stream()
                ->target('groups-accordion')
                ->action('replace')
                ->view('components.navigation.turbo-streams.group-accordion', compact('groups', 'user', 'group'))
        )->header('Content-Type', 'text/vnd.turbo-stream.html');

    }

    /**
     * Publish an event for creating a group.
     *
     * @param string $name
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
                ->view('components.navigation.turbo-streams.group-accordion', compact('groups'))
                ->render(),
            'new_group_id' => $group->id,
        ], 200)->header('Content-Type', 'application/json');

    }

    /**
     * Delete a group and publish an event for its deletion.
     *
     * @param int $groupId
     * @return \Illuminate\Http\Response
     */
    public function deleteGroup($groupId)
    {

        DomainEventPublisher::publish(new GroupDeletedEvent($groupId));

        $groups = Group::with('users')->get();

        return turbo_stream()
            ->target('groups-accordion')
            ->action('replace')
            ->view('components.navigation.turbo-streams.group-accordion', compact('groups'));
    }
    

    /**
     * Publish an event for removing a user from a group.
     *
     * @param int $userId
     * @param int $groupId
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
            ->view('components.navigation.turbo-streams.group-accordion', compact('groups'));

        return response()->json([
            'success' => true,
            'message' => 'Domain event published: UserRemovedFromGroup.',
        ]);
    }
}