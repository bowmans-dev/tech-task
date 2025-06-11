<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'worker_id', 'worker_type'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    
    public function worker()
    {
        return $this->morphTo();
    }
}