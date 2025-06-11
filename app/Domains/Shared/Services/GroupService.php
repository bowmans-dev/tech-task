<?php

namespace App\Domains\Shared\Services;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreated;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupDeleted;
use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroup;
use App\Domains\Shared\Events\DomainEvents\Groups\UserRemovedFromGroup;
use App\Domains\Shared\Events\DomainEventPublisher;

class GroupService
{
    public function createGroup($name)
    {
        $group = new Group(['name' => $name]);

        DomainEventPublisher::publish(new GroupCreated($group));

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


    public function addUserToGroup($userId, $groupId)
    {
        $user = User::findOrFail($userId);
        $group = Group::findOrFail($groupId);

        DomainEventPublisher::publish(new UserAddedToGroup($user, $group));

        $groups = Group::with('users')->get();

        return turbo_stream()
            ->target('groups-accordion')
            ->action('replace')
            ->view('components.navigation.group-accordion', compact('groups', 'user', 'group'));

    }


    public function deleteGroup($groupId)
    {

        DomainEventPublisher::publish(new GroupDeleted($groupId));

        $groups = Group::with('users')->get();

        return turbo_stream()
            ->target('groups-accordion')
            ->action('replace')
            ->view('components.navigation.group-accordion', compact('groups'));
    }
    

    public function removeUserFromGroup($userId, $groupId)
    {
        $user = User::findOrFail($userId);
        $group = Group::findOrFail($groupId);

        DomainEventPublisher::publish(new UserRemovedFromGroup($user, $group));

        $groups = Group::with('users')->get();

        return turbo_stream()
            ->target('groups-accordion')
            ->action('replace')
            ->view('components.navigation.group-accordion', compact('groups'));
    }
}