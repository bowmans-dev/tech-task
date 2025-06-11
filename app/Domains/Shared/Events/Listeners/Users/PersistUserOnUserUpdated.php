<?php

namespace App\Domains\Shared\Events\Listeners\Users;

use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Events\DomainEvents\Users\UserUpdatedEvent;

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

        // Retrieve the aggregate and updated data from the event
        $userAggregate = $event->userAggregate;
        $updatedData = $event->getUpdatedData();

        // Use the aggregate's update method
        $userAggregate->update($updatedData);

        // Persist the updated aggregate
        $this->userRepository->save($userAggregate);
    }
} 