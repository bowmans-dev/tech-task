<?php

namespace App\Domains\Shared\Events\DomainEvents\Messages;

use App\Models\TaskCompletion;

class TaskCompleted
{
    public TaskCompletion $completion;

    public function __construct(TaskCompletion $completion)
    {
        $this->completion = $completion;
    }
}