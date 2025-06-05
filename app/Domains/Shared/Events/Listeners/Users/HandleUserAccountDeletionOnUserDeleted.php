<?php

namespace App\Domains\Shared\Events\Listeners\Users;

use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Events\DomainEvents\Users\UserDeletedEvent;
use Illuminate\Support\Facades\Log;

class HandleUserAccountDeletionOnUserDeleted
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(UserDeletedEvent $event): void
    {
        $userData = $event->userAggregate->getProcessedData();

        $this->userRepository->delete($userData['id']);
    }
}
