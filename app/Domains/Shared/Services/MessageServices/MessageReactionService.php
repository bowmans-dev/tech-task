<?php

namespace App\Domains\Shared\Services\MessageServices;

use App\Models\MessageReaction;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageReacted;
use Illuminate\Http\Request;

class MessageReactionService {

    public function react(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:messages,id',
            'emoji' => 'required|string|max:20'
        ]);

        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) { 
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = $auth->user();

        $reaction = MessageReaction::updateOrCreate(
            [
                'message_id' => $request->message_id, 
                'user_id' => $user->id, 
                'user_type' => get_class($user)
            ],
            ['emoji' => $request->emoji]
        );


        $reaction->load('user', 'message');

        DomainEventPublisher::publish(new MessageReacted($reaction));

        return response()->json(['success' => true]);
    }

}

