<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Domains\Shared\Events\DomainEvents\Messages\MessageReacted;
use App\Domains\Supporting\Websocket\WebsocketClient;
use App\Domains\Supporting\Websocket\UserPayloadHelper;
use Illuminate\Support\Facades\View;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotifyTeamMembersOnReactionSent
{
    public function handle(MessageReacted $event)
    {

        $reaction = $event->reaction;
        $message = $reaction->message;
        $user = $reaction->user;

        $userPayload = UserPayloadHelper::format($reaction->user);

        $teamMembers = User::whereHas('calendarEvents', function ($query) use ($message) {
            $query->where('calendar_event_id', $message->event_id);
        })->get();

        // Render the updated reactions HTML
        $reactionHtml = View::make('Components.messages._reactions', [
            'message' => $message,
        ])->render();

        // Construct broadcast payload
        $payload = [
            'action' => 'reaction_broadcast',
            'message_id' => $reaction->message_id,
            'event_id' => $message->event_id,
            'emoji' => $reaction->emoji,
            'user' => $userPayload,
            'html' => $reactionHtml,
        ];

        (new WebsocketClient())->send($payload);
    }
}