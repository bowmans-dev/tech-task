<?php

namespace App\Infrastructures\Persistence\Repositories;

use App\Domains\Core\Aggregates\UserAggregate;
use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\Entities\User;
use App\Domains\Core\Repositories\UserRepositoryInterface;
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
        $userModel = EloquentUserModel::find($userId);

        if (!$userModel) {
            return null;
        }

        $userData = UserData::fromModel($userModel);
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

        return $userModels->map(fn($userModel) => new UserAggregate(
            new User(UserData::fromModel($userModel))
        ))->toArray();
    }
}
