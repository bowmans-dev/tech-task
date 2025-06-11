<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Calendar;
use App\Models\MessageReaction;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'sender_type', 'event_id', 'content', 'is_poll', 'is_task_list'];

    protected $casts = [
        'is_poll' => 'boolean',
        'is_task_list' => 'boolean',
    ];


    public function sender()
    {
        return $this->morphTo();
    }


    public function event()
    {
        return $this->belongsTo(Calendar::class, 'event_id');
    }


    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }


    public function pollOptions()
    {
        return $this->hasMany(PollOption::class)->with('votes');
    }


    public function pollVotes()
    {
        return $this->hasMany(PollVote::class);
    }

    
    public function taskList()
    {
        return $this->hasOne(TaskList::class);
    }

}