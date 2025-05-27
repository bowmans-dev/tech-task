<?php

namespace App\Domains\Shared\Events\DomainEvents\Messages;

use App\Models\MessageReaction;

class MessageReacted
{
    public MessageReaction $reaction;

    public function __construct(MessageReaction $reaction)
    {
        $this->reaction = $reaction;
    }
}
