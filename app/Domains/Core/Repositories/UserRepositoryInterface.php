<?php

namespace App\Domains\Core\Repositories;

use App\Domains\Core\Aggregates\UserAggregate;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{

    public function save(UserAggregate $userAggregate): void;


    public function findById(string $userId): ?UserAggregate;

    
    public function delete(string $userId): void;


    public function list(int $perPage): LengthAwarePaginator;

    
    public function filter(?string $search, int $perPage): LengthAwarePaginator;
    
}
