<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Domains\Shared\Events\DomainEvents\Messages\PollVoted;
use App\Domains\Supporting\Websocket\WebsocketClient;
use App\Domains\Supporting\Websocket\UserPayloadHelper;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class NotifyTeamMembersOnPollVote
{
    public function handle(PollVoted $event)
    {
        $vote = $event->vote;
        $message = $vote->message;
        $currentUser = auth('admin')->check() ? auth('admin')->user() : auth('web')->user();
        $currentUserClass = get_class($currentUser);
        $currentUserId = $currentUser->id;

        $userPayload = UserPayloadHelper::format($vote->voter);

        $message->load('pollOptions.votes.voter');

        $selectedOptionId = null;
        if ($message->is_poll) {
            foreach ($message->pollOptions as $option) {
                foreach ($option->votes as $vote) {
                    if (
                        $vote->voter_id === $currentUserId &&
                        $vote->voter_type === $currentUserClass
                    ) {
                        $selectedOptionId = $option->id;
                        break 2;
                    }
                }
            }
        }

        $pollHtml = View::make('Components.messages.poll-options', [
            'message' => $message,
            'isPoll' => $message->is_poll,
            'options' => $message->is_poll ? $message->pollOptions : [],
            'selectedOptionId' => $selectedOptionId,
        ])->render();

        $payload = [
            'action' => 'vote_broadcast',
            'message_id' => $message->id,
            'event_id' => $message->event_id,
            'user' => $userPayload,
            'html' => $pollHtml,
        ];

        (new WebsocketClient())->send($payload);
    }
}