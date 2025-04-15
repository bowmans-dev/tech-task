<?php

namespace App\Domains\Shared\Events\Listeners;

use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Events\UserCreatedEvent;
use Illuminate\Support\Facades\Log;

class PersistUserOnUserCreated
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(UserCreatedEvent $event): void
    {
        $userData = $event->userAggregate->getProcessedData();

        Log::info('Domain Event Listener triggered for UserCreatedEvent.', [
            'user_id' => $userData['id'],
            'event' => UserCreatedEvent::class,
            'user_data' => $userData,
        ]);

        $this->userRepository->save($event->userAggregate);

        Log::info('User has been saved successfully.', [
            'user_id' => $userData['id'],
            'user_data' => $userData,
        ]);
    }
}
