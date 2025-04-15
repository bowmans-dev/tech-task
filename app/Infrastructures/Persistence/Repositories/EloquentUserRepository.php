<?php

namespace App\Infrastructures\Persistence\Repositories;

use App\Domains\Core\Aggregates\UserAggregate;
use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\Entities\User;
use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\ProfilePicture;
use App\Models\User as EloquentUserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Save a UserAggregate to the database.
     */
    public function save(UserAggregate $userAggregate): void
    {

        $userData = $userAggregate->getProcessedData();

        // Save or update the user in the database
        EloquentUserModel::updateOrCreate(['id' => $userData['id']], $userData);
    }

    /**
     * Find a UserAggregate by its unique identifier.
     */
    public function findById(string $userId): ?UserAggregate
    {
        // Query the user model
        $userModel = EloquentUserModel::find($userId);

        if (! $userModel) {
            return null; // User not found
        }

        // Create a UserData DTO from the model data
        $userData = new UserData(
            id: $userModel->id,
            firstName: $userModel->first_name,
            lastName: $userModel->last_name,
            gender: $userModel->gender,
            email: new Email($userModel->email),
            password: new Password($userModel->password, true), // Password already hashed
            phone: $userModel->phone ? new Phone($userModel->phone) : null,
            country: $userModel->country ? new Country($userModel->country) : null,
            profilePicture: $userModel->profile_picture ? new ProfilePicture($userModel->profile_picture) : null
        );

        // Create a User entity from the DTO and wrap it in a UserAggregate
        $userEntity = new User($userData);

        return new UserAggregate($userEntity);
    }

    /**
     * Delete a UserAggregate by its unique identifier.
     */
    public function delete(string $userId): void
    {
        EloquentUserModel::destroy($userId);
    }

    /**
     * List all UserAggregates with optional pagination.
     */
    public function list(int $perPage = 10): array
    {
        $userModels = EloquentUserModel::paginate($perPage);

        return $userModels->map(function ($userModel) {
            // Create a UserData DTO for each user model
            $userData = new UserData(
                id: $userModel->id,
                firstName: $userModel->first_name,
                lastName: $userModel->last_name,
                gender: $userModel->gender,
                email: new Email($userModel->email),
                password: new Password($userModel->password, true), // Password already hashed
                phone: $userModel->phone ? new Phone($userModel->phone) : null,
                country: $userModel->country ? new Country($userModel->country) : null,
                profilePicture: $userModel->profile_picture ? new ProfilePicture($userModel->profile_picture) : null
            );

            // Create a User entity and wrap it in a UserAggregate
            $userEntity = new User($userData);

            return new UserAggregate($userEntity);
        })->toArray();
    }
}
