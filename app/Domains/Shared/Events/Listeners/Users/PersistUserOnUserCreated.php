<?php

namespace App\Domains\Shared\Events\Listeners\Users;

use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Events\DomainEvents\Users\UserCreatedEvent;

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
        $this->userRepository->save($event->userAggregate);
    }
}
