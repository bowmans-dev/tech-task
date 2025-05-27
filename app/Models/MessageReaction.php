<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Message;
use App\Models\User;

class MessageReaction extends Model
{
    protected $fillable = ['message_id', 'user_id', 'user_type', 'emoji'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->morphTo();
    }

}