<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'sender_type', 'event_id', 'content'];

    public function sender()
    {
        return $this->morphTo();
    }

    public function event()
    {
        return $this->belongsTo(Calendar::class, 'event_id');
    }
}