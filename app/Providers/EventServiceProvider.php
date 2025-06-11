<?php

namespace App\Providers;

use App\Domains\Shared\Events\DomainEvents\Users\UserCreatedEvent;
use App\Domains\Shared\Events\DomainEvents\Users\UserUpdatedEvent;
use App\Domains\Shared\Events\DomainEvents\Users\UserDeletedEvent;

use App\Domains\Shared\Events\Listeners\Users\PersistUserOnUserCreated;
use App\Domains\Shared\Events\Listeners\Users\PersistUserOnUserUpdated;
use App\Domains\Shared\Events\Listeners\Users\HandleUserAccountDeletionOnUserDeleted;

use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreated;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupDeleted;
use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroup;
use App\Domains\Shared\Events\DomainEvents\Groups\UserRemovedFromGroup;

use App\Domains\Shared\Events\Listeners\Groups\PersistGroupOnGroupCreated;
use App\Domains\Shared\Events\Listeners\Groups\HandleGroupDeleted;
use App\Domains\Shared\Events\Listeners\Groups\PersistUserOnUserAddedToGroup;
use App\Domains\Shared\Events\Listeners\Groups\HandleUserRemovedFromGroup;

use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryCreatedOrUpdated;
use App\Domains\Shared\Events\Listeners\Calendar\PersistCalendarEntryOnCreatedOrUpdated;
use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryDeleted;
use App\Domains\Shared\Events\Listeners\Calendar\HandleCalendarEventDeleted;

use App\Domains\Shared\Events\DomainEvents\Messages\MessageSent;
use App\Domains\Shared\Events\Listeners\Messages\NotifyTeamMembersOnMessageSent;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageReacted;
use App\Domains\Shared\Events\Listeners\Messages\NotifyTeamMembersOnReactionSent;
use App\Domains\Shared\Events\DomainEvents\Messages\PollVoted;
use App\Domains\Shared\Events\Listeners\Messages\NotifyTeamMembersOnPollVote;
use App\Domains\Shared\Events\DomainEvents\Messages\TaskCompleted;
use App\Domains\Shared\Events\Listeners\Messages\NotifyTeamMembersOnTaskCompleted;



use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserCreatedEvent::class => [
            PersistUserOnUserCreated::class,
        ],
        UserUpdatedEvent::class => [
            PersistUserOnUserUpdated::class,
        ],
        UserDeletedEvent::class => [
            HandleUserAccountDeletionOnUserDeleted::class,
        ],
        GroupCreated::class => [
            PersistGroupOnGroupCreated::class,
        ],
        GroupDeleted::class => [
            HandleGroupDeleted::class,
        ],
        UserAddedToGroup::class => [
            PersistUserOnUserAddedToGroup::class,
        ],
        UserRemovedFromGroup::class => [
            HandleUserRemovedFromGroup::class,
        ],
        CalendarEntryCreatedOrUpdated::class => [
            PersistCalendarEntryOnCreatedOrUpdated::class,
        ],
        CalendarEntryDeleted::class => [
            HandleCalendarEventDeleted::class,
        ],
        MessageSent::class => [
            NotifyTeamMembersOnMessageSent::class,
        ],
        MessageReacted::class => [
            NotifyTeamMembersOnReactionSent::class,
        ],
        PollVoted::class => [
            NotifyTeamMembersOnPollVote::class,
        ],
        TaskCompleted::class => [
            NotifyTeamMembersOnTaskCompleted::class,
        ]
    ];
}
