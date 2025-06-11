<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;
    
    protected $fillable = ['name'];

    public $timestamps = false;

    protected static function booted()
    {
        static::deleting(function ($group) {
            $group->users()->detach();
        });
    }


    public function users()
    {
        return $this->belongsToMany(User::class, 'user_groups', 'group_id', 'user_id');
    }


    public function hasUser($userId)
    {
        return $this->users()->where('id', $userId)->exists();
    }

}