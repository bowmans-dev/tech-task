<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    protected $fillable = ['message_id', 'option_text'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }
}