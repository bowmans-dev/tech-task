<?php

namespace App\Services;

// use App\Domains\Core\Entities\User;
// use App\Domains\Core\ValueObjects\Password;
// use App\Domains\Core\Repositories\UserRepository;
use App\Domains\Supporting\ImageUpload\ImageService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class UserService
{
    private $imageService;

    private $paginationCount = 10; // Default pagination count

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
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
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string',
            'gender' => 'nullable|string',
            'password' => 'nullable|string|confirmed|min:8',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        if ($context === 'update' && $userId) {
            // Ensure the email is unique, excluding the current user's email based on their ID
            $rules['email'] = 'required|string|email|max:255|unique:users,email,'.$userId;
        }

        return $rules;
    }

    /**
     * Create a new user.
     *
     * @param  string  $authType  'admin' or 'user'
     */
    public function createUser(array $data, string $authType): User
    {
        // Validation rules
        $rules = $this->getUserValidationRules('create');

        validator()->make($data, $rules)->validate();

        // Handle profile picture via ImageService
        if (isset($data['profile_picture'])) {
            $data['profile_picture'] = $this->imageService->upload($data['profile_picture']);
        }

        // Hash password
        $data['password'] = Hash::make($data['password']);

        // Create and return the user
        return User::create($data);
    }

    /**
     * Update a user (shared for admin and users).
     *
     * @param  string  $authType  'admin' or 'user'
     */
    public function updateUser(User $user, array $data, string $authType, ?int $authUserId = null): User
    {
        // Authorization for regular users
        if ($authType === 'user' && $user->id !== $authUserId) {
            abort(403, 'Unauthorized to update this user.');
        }

        // Validation rules
        $rules = $this->getUserValidationRules('update', $user->id);

        // \Log::info('Data before validation in updateUser:', $data);
        validator()->make($data, $rules)->validate();

        // Handle profile picture via ImageService
        if (isset($data['profile_picture'])) {

            // Delete old profile picture
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                $this->imageService->delete($user->profile_picture);
            }

            $data['profile_picture'] = $this->imageService->upload($data['profile_picture']);
        }

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Remove 'password' from the update data if not provided
        }

        // Update and return the user
        $user->update($data);

        return $user;
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user, string $authType, ?int $authUserId = null): void
    {
        // Authorization for regular users
        if ($authType === 'user' && $user->id !== $authUserId) {
            abort(403, 'Unauthorized to delete this user.');
        }

        // Delete profile picture
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();
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
     * Get all users (Admin listing).
     */
    public function listUsers(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return User::paginate($this->paginationCount);
    }

    /**
     * Show a user's details (Admin-specific action).
     */
    public function showUser(User $user): User
    {
        return $user;
    }

    /**
     * Show authenticated user's profile.
     */
    public function showProfile(): User
    {
        return auth()->user();
    }

    /**
     * Reset a user's password.
     */
    public function sendPasswordResetLink(User $user): void
    {
        Password::sendResetLink(['email' => $user->email]);
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
