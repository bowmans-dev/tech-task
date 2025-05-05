<?php

namespace App\Http\Controllers\Web;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hotwired\TurboLaravel\Turbo;

class MessageController extends Controller 
{

    public function store(Request $request)
    {
        $message = Message::create([
            'sender_id' => auth('web')->id(),
            'content' => $request->input('content'),
            'event_id' => $request->input('event_id'),
        ]);

        // Broadcast the Turbo Stream update
        return response()->turboStream()
            ->append('messages', view('Components.messages._message', compact('message')));
    }

    public function fetchMessages(Request $request, $eventId)
    {
        $messages = Message::where('event_id', $eventId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Return a Turbo Stream response to replace the #messages container
        return response()->turboStream()
            ->append('messages', view('Components.messages.messages', compact('messages')));
    }

} 