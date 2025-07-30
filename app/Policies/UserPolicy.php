<?php

namespace App\Policies;

use App\Models\Admin;

class UserPolicy
{
    public function create(Admin $authUser): bool
    {
        return true;
    }
}