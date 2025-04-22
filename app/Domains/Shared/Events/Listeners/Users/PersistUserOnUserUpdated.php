<?php

namespace App\Domains\Shared\Events\Listeners\Users;

use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Events\DomainEvents\Users\UserUpdatedEvent;
use Illuminate\Support\Facades\Log;

class PersistUserOnUserUpdated
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(UserUpdatedEvent $event): void
    {
        $userData = $event->userAggregate->getProcessedData();
        
        Log::info('Domain Event Listener triggered for UserUpdatedEvent.', [
            'user_id' => $userData['id'],
            'event' => UserCreatedEvent::class,
            'user_data' => $userData,
        ]);

        // Retrieve the aggregate and updated data from the event
        $userAggregate = $event->userAggregate;
        $updatedData = $event->getUpdatedData();

        // Use the aggregate's update method
        $userAggregate->update($updatedData);

        // Persist the updated aggregate
        $this->userRepository->save($userAggregate);

        Log::info('User has been saved updated.', [
            'user_id' => $userData['id'],
            'user_data' => $userData,
        ]);
    }
} 