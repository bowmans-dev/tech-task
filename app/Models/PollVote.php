<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    protected $fillable = ['message_id', 'poll_option_id', 'voter_id', 'voter_type'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function option()
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }

    public function voter()
    {
        return $this->morphTo();
    }
}
