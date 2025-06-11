<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskList extends Model
{
    use HasFactory;

    protected $fillable = ['message_id', 'topic'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    
    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}