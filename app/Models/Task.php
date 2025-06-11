<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['task_list_id', 'task_text'];

    public function taskList()
    {
        return $this->belongsTo(TaskList::class);
    }

    
    public function taskCompletions()
    {
        return $this->hasMany(TaskCompletion::class);
    }
}