<?php

namespace App\Domains\Shared\Events\DomainEvents\Messages;
use Illuminate\Support\Collection;

use App\Models\Message;

class MessageSent
{
    public Message $message;
    public string $eventName;
    public Collection $options;

    public function __construct(Message $message, string $eventName, Collection $options)
    {
        $this->message = $message;
        $this->eventName = $eventName;
        $this->options = $options ?? collect();
    }
}