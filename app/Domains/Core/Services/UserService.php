<?php

namespace App\Domains\Core\Services;

use App\Models\User;
use App\Domains\Core\Aggregates\UserAggregate;
use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Users\UserCreatedEvent;
use App\Domains\Shared\Events\DomainEvents\Users\UserUpdatedEvent;
use App\Domains\Shared\Events\DomainEvents\Users\UserDeletedEvent;
use App\Domains\Supporting\ImageUpload\ImageService;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    private $imageService;

    private UserRepositoryInterface $userRepository;

    private int $paginationCount = 10;

    public function __construct(ImageService $imageService, UserRepositoryInterface $userRepository)
    {
        $this->imageService = $imageService;
        $this->userRepository = $userRepository;
    }



    public function createUser(array $data)
    {

        $this->imageService->uploadProfilePicture($data);

        $userAggregate = UserAggregate::create($data);

        DomainEventPublisher::publish(new UserCreatedEvent($userAggregate));

        return $userAggregate->getProcessedData();
    }
 



    public function updateUser(User $user, array $data)
    {

        $this->imageService->replaceProfilePicture($data, $user->profile_picture);

        $userAggregate = $this->userRepository->findById($user->id);

        DomainEventPublisher::publish(new UserUpdatedEvent($userAggregate, $data));

        return $userAggregate->getProcessedData();
    }



    public function deleteUser(User $user): void
    {

        $userAggregate = $this->userRepository->findById($user->id);

        $this->imageService->deleteProfilePicture($userAggregate->getProcessedData()['profile_picture']);

        DomainEventPublisher::publish(new UserDeletedEvent($userAggregate));
    }



    public function deleteProfile($user): void
    {
        $profile = $user;

        $userAggregate = $this->userRepository->findById($profile->id);

        $this->imageService->deleteProfilePicture($profile->profile_picture);

        auth('web')->logout();

        DomainEventPublisher::publish(new UserDeletedEvent($userAggregate));
    }



    public function showProfile(): User
    {
        return auth('web')->user();
    }




    public function listUsers(): LengthAwarePaginator
    {
        return $this->userRepository->list($this->paginationCount);
    }



    public function filterUsers(?string $search): LengthAwarePaginator
    {
        return $this->userRepository->filter($search, $this->paginationCount);
    }
    

}
