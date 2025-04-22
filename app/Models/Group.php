<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;
    
    public $timestamps = false; // Disable automatic timestamps

    // Define the many-to-many relationship with User
    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'user_groups');
    // }
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_groups', 'group_id', 'user_id');
    }

    // Allow mass assignment for the 'name' attribute
    protected $fillable = ['name'];

    // Check if the group has a specific user
    public function hasUser($userId)
    {
        return $this->users()->where('id', $userId)->exists();
    }

    // Event: When deleting a group, detach users
    protected static function booted()
    {
        static::deleting(function ($group) {
            $group->users()->detach();
        });
    }

}