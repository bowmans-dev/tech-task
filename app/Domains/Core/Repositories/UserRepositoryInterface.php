<?php

namespace App\Domains\Core\Repositories;

use App\Domains\Core\Aggregates\UserAggregate;

interface UserRepositoryInterface
{
    /**
     * Save a UserAggregate to the repository.
     */
    public function save(UserAggregate $userAggregate): void;

    /**
     * Find a UserAggregate by its unique identifier.
     */
    public function findById(string $userId): ?UserAggregate;

    /**
     * Delete a UserAggregate by its unique identifier.
     */
    public function delete(string $userId): void;

    /**
     * List all UserAggregates with optional pagination.
     */
    public function list(int $perPage = 10): array;
}
