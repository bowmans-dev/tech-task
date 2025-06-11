<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\TaskCompletion;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }


    public function messageReactions()
    {
        return $this->hasMany(MessageReaction::class);
    }


    public function pollVotes()
    {
        return $this->morphMany(PollVote::class, 'voter');
    }


    public function completedTasks()
    {
        return $this->morphMany(TaskCompletion::class, 'worker');
    }

}
