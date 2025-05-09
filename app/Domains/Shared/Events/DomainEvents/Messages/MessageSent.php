<?php

namespace App\Domains\Shared\Events\DomainEvents\Messages;

use App\Models\Message;

class MessageSent
{
    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}