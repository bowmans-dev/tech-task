<?php

namespace App\Providers;

use App\Domains\Shared\Events\Listeners\PersistUserOnUserCreated;
use App\Domains\Shared\Events\Listeners\PersistUserOnUserUpdated;
use App\Domains\Shared\Events\Listeners\HandleUserAccountDeletionOnUserDeleted;
use App\Domains\Shared\Events\UserCreatedEvent;
use App\Domains\Shared\Events\UserUpdatedEvent;
use App\Domains\Shared\Events\UserDeletedEvent;
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
    ];
}
