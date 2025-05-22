<?php

namespace App\Domains\Shared\Events\DomainEvents\Messages;

use App\Models\Message;

class MessageSent
{
    public Message $message;
    public string $eventName;

    public function __construct(Message $message, string $eventName)
    {
        $this->message = $message;
        $this->eventName = $eventName;
    }
}