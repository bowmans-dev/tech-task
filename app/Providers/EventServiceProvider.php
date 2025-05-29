<?php

namespace App\Providers;

use App\Domains\Shared\Events\DomainEvents\Users\UserCreatedEvent;
use App\Domains\Shared\Events\DomainEvents\Users\UserUpdatedEvent;
use App\Domains\Shared\Events\DomainEvents\Users\UserDeletedEvent;

use App\Domains\Shared\Events\Listeners\Users\PersistUserOnUserCreated;
use App\Domains\Shared\Events\Listeners\Users\PersistUserOnUserUpdated;
use App\Domains\Shared\Events\Listeners\Users\HandleUserAccountDeletionOnUserDeleted;

use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreatedEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\GroupDeletedEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroupEvent;
use App\Domains\Shared\Events\DomainEvents\Groups\UserRemovedFromGroupEvent;

use App\Domains\Shared\Events\Listeners\Groups\PersistGroupOnGroupCreated;
use App\Domains\Shared\Events\Listeners\Groups\HandleGroupDeleted;
use App\Domains\Shared\Events\Listeners\Groups\PersistUserOnUserAddedToGroup;
use App\Domains\Shared\Events\Listeners\Groups\HandleUserRemovedFromGroup;

use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryCreatedEvent;
use App\Domains\Shared\Events\Listeners\Calendar\PersistCalendarEntryOnCreated;
use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryDeletedEvent;
use App\Domains\Shared\Events\Listeners\Calendar\HandleCalendarEventDeleted;

use App\Domains\Shared\Events\DomainEvents\Messages\MessageSent;
use App\Domains\Shared\Events\Listeners\Messages\NotifyTeamMembersOnMessageSent;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageReacted;
use App\Domains\Shared\Events\Listeners\Messages\NotifyTeamMembersOnReactionSent;
use App\Domains\Shared\Events\DomainEvents\Messages\PollVoted;
use App\Domains\Shared\Events\Listeners\Messages\NotifyTeamMembersOnPollVote;




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
        GroupCreatedEvent::class => [
            PersistGroupOnGroupCreated::class,
        ],
        GroupDeletedEvent::class => [
            HandleGroupDeleted::class,
        ],
        UserAddedToGroupEvent::class => [
            PersistUserOnUserAddedToGroup::class,
        ],
        UserRemovedFromGroupEvent::class => [
            HandleUserRemovedFromGroup::class,
        ],
        CalendarEntryCreatedEvent::class => [
            PersistCalendarEntryOnCreated::class,
        ],
        CalendarEntryDeletedEvent::class => [
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

    ];
}
