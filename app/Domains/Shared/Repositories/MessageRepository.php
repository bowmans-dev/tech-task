<?php

namespace App\Domains\Shared\Repositories;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageRepository
{
    public function createMessage(Request $request)
    {
        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sender = $auth->user();
        
        return Message::create([
            'sender_id' => $sender->id,
            'sender_type' => get_class($sender),
            'content' => $request->input('content'),
            'event_id' => $request->input('event_id'),
            'is_poll' => $request->boolean('is_poll', false),
            'is_task_list' => $request->boolean('is_task_list', false),
        ]);
    }

    
    public function getMessagesByEvent($eventId)
    {
        return Message::where('event_id', $eventId)
            ->with(['sender', 'reactions.user', 'pollOptions.votes.voter', 'taskList.tasks.taskCompletions.worker'])
            ->orderBy('created_at', 'asc')
            ->get();
    }
}