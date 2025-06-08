<?php

namespace App\Domains\Shared\Events\DomainEvents\Messages;
use Illuminate\Support\Collection;

use App\Models\Message;
use App\Models\TaskList;

class MessageSent
{
    public Message $message;
    public string $eventName;
    public Collection $options;
    public Collection $tasks;

    public function __construct(Message $message, string $eventName, Collection $options, Collection $tasks)
    {
        $this->message = $message;
        $this->eventName = $eventName;
        $this->options = $options ?? collect();
        $this->tasks = $tasks ?? collect();
    }
}