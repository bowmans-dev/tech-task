<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'event_id', 'content'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function event()
    {
        return $this->belongsTo(Calendar::class, 'event_id');
    }
}