<?php

namespace App\Domains\Shared\Events\DomainEvents\Messages;

use App\Models\PollVote;

class PollVoted
{
    public PollVote $vote;

    public function __construct(PollVote $vote)
    {
        $this->vote = $vote;
    }
}