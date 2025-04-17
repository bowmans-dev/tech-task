<?php

namespace App\Domains\Core\Services;

use App\Domains\Core\Aggregates\UserAggregate;
use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\UserCreatedEvent;
use App\Domains\Shared\Events\UserUpdatedEvent;
use App\Domains\Shared\Events\UserDeletedEvent;
use App\Domains\Supporting\ImageUpload\ImageService;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
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

        $this->uploadProfilePicture($data);

        $userAggregate = UserAggregate::create($data);

        DomainEventPublisher::publish(new UserCreatedEvent($userAggregate));

        return $userAggregate->getProcessedData();
    }




    public function updateUser(User $user, array $data)
    {

        $this->replaceProfilePicture($data, $user->profile_picture);

        $userAggregate = $this->userRepository->findById($user->id);

        DomainEventPublisher::publish(new UserUpdatedEvent($userAggregate, $data));

        return $userAggregate->getProcessedData();
    }



    public function deleteUser(User $user): void
    {

        $userAggregate = $this->userRepository->findById($user->id);

        $this->deleteProfilePicture($userAggregate->getProcessedData()['profile_picture']);

        DomainEventPublisher::publish(new UserDeletedEvent($userAggregate));
    }



    public function listUsers(): LengthAwarePaginator
    {
        return User::paginate($this->paginationCount);
    }



    public function filterUsers(?string $search)
    {
        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($this->paginationCount);
    }



    public function showProfile(): User
    {
        return auth('web')->user();
    }



    public function deleteProfile(): void
    {
        $profile = auth('web')->user();

        if ($profile->profile_picture && Storage::disk('public')->exists($profile->profile_picture)) {
            Storage::disk('public')->delete($profile->profile_picture);
        }

        $profile->delete();
        auth('web')->logout();
    }



    private function uploadProfilePicture(array &$data): void
    {
        if (isset($data['profile_picture'])) {
            $data['profile_picture'] = $this->imageService->upload($data['profile_picture']);
        }
    }



    private function replaceProfilePicture(array &$data, ?string $currentPicture): void
    {
        if (isset($data['profile_picture'])) {
            if ($currentPicture && Storage::disk('public')->exists($currentPicture)) {
                $this->imageService->delete($currentPicture);
            }
            $data['profile_picture'] = $this->imageService->upload($data['profile_picture']);
        }
    }



    private function deleteProfilePicture(?string $profilePicture): void
    {
        if ($profilePicture && Storage::disk('public')->exists($profilePicture)) {
            Storage::disk('public')->delete($profilePicture);
        }
    }
}
