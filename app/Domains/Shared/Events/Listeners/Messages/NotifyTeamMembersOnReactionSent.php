<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Models\User;
use App\Utils\HtmlMinifier;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageReacted;
use App\Domains\Supporting\Websocket\{UserPayloadHelper, EventScopedConnectionsInternalClient};
use Illuminate\Support\Facades\View;
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

        $minifiedHtml = HtmlMinifier::minify($reactionHtml);

        // Construct broadcast payload
        $payload = [
            'action' => 'reaction_broadcast',
            'message_id' => $reaction->message_id,
            'event_id' => $message->event_id,
            'emoji' => $reaction->emoji,
            'user' => $userPayload,
            'html' => $minifiedHtml,
        ];

        (new EventScopedConnectionsInternalClient)->send($payload);
    }
}