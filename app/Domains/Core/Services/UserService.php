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

    /**
     * Get validation rules for user creation or update.
     *
     * @param  string  $context  'create' or 'update'
     */
    private function getUserValidationRules(string $context = 'create', ?int $userId = null): array
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string',
            'country' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed|min:8', // Password is required
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        if ($context === 'update' && $userId) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,'.$userId;
            $rules['password'] = 'nullable|string|confirmed|min:8'; // Password is not required for updates
        }

        return $rules;
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data)
    {
        $rules = $this->getUserValidationRules('create');
        Validator::make($data, $rules)->validate();

        // Handle profile picture via ImageService
        if (isset($data['profile_picture'])) {
            $data['profile_picture'] = $this->imageService->upload($data['profile_picture']);
        }

        // Create UserAggregate
        $userAggregate = UserAggregate::create($data);

        // Publish a domain event
        DomainEventPublisher::publish(new UserCreatedEvent($userAggregate));  
        
        return $userAggregate->getProcessedData();
    }


    public function updateUser(User $user, array $data)
    {
        $rules = $this->getUserValidationRules('update', $user->id);
        Validator::make($data, $rules)->validate();

        if (empty($data['password'])) {
            unset($data['password']); // Remove null or empty passwords
        }

        if (isset($data['profile_picture'])) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                $this->imageService->delete($user->profile_picture);
            }
            $data['profile_picture'] = $this->imageService->upload($data['profile_picture']);
        }

        // Fetch the existing UserAggregate
        $userAggregate = $this->userRepository->findById($user->id);

        if (!$userAggregate) {
            throw new \Exception("User not found.");
        }

        // Publish a domain event directly with the current aggregate and new data
        DomainEventPublisher::publish(new UserUpdatedEvent($userAggregate, $data));

        return $userAggregate->getProcessedData();

    }


    /**
     * Delete a user.
     *
     * @param  string  $userId
     */
    public function deleteUser(User $user, string $authType): void
    {

        $userAggregate = $this->userRepository->findById($user->id);

        // Check if the user has a profile picture and delete it from storage
        $profilePicture = $userAggregate->getProcessedData()['profile_picture'];
        if ($profilePicture && \Storage::disk('public')->exists($profilePicture)) {
            \Storage::disk('public')->delete($profilePicture);
        }

        // Publish a domain event to delete the user
        DomainEventPublisher::publish(new UserDeletedEvent($userAggregate));
        
    }


    /**
     * Get all users (Admin listing).
     */
    public function listUsers(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return User::paginate($this->paginationCount);
    }


    /**
     * Filter users (Admin-only functionality).
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
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


    /**
     * Show authenticated user's profile.
     */
    public function showProfile(): User
    {
        return auth('web')->user();
    }


    /**
     * Delete authenticated user's profile.
     */
    public function deleteProfile(): void
    {
        $profile = auth('web')->user();

        if ($profile->profile_picture && Storage::disk('public')->exists($profile->profile_picture)) {
            Storage::disk('public')->delete($profile->profile_picture);
        }

        $profile->delete();
        auth('web')->logout();
    }
}
