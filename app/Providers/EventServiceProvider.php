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

    ];
}
