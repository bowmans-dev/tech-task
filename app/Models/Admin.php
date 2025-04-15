<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Extend for authentication

class Admin extends Authenticatable
{
    use HasFactory;

    // Mass-assignable attributes
    protected $fillable = [
        'name',
        'email',
        'password',
        'permissions',
    ];

    // Casts for specific attributes
    protected $casts = [
        'permissions' => 'array', // Automatically decode/encode JSON permissions
    ];

    // Hide sensitive attributes
    protected $hidden = [
        'password',
    ];
}
