<?php

namespace App\Http\Controllers\Web;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hotwired\TurboLaravel\Turbo;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageSent;
use App\Models\Admin;


class MessageController extends Controller 
{

    public function store(Request $request)
    {
        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sender = $auth->user();

        $message = Message::create([
            'sender_id' => $sender->id,
            'sender_type' => get_class($sender), // App\Models\User or App\Models\Admin
            'content' => $request->input('content'),
            'event_id' => $request->input('event_id'),
        ]);

        // Dispatch domain event via the publisher
        DomainEventPublisher::publish(new MessageSent($message));

        // Eager-load sender for the view
        $message->load('sender');

        $isAdmin = $sender instanceof \App\Models\Admin;

        $displayName = $isAdmin 
            ? '(Admin) ' . $sender->name 
            : $sender->first_name . ' ' . $sender->last_name;

        $profilePicture = $sender->profile_picture
            ? asset('storage/' . $sender->profile_picture)
            : asset('storage/default_profile_image.png');

        $isSender = true;

        return response()->turboStream()
            ->append('messages', view('Components.messages._message', [
                'message' => $message,
                'displayName' => $displayName,
                'profilePicture' => $profilePicture,
                'isSender' => $isSender,
            ]));
    }


    public function fetchMessages(Request $request, $eventId)
    {
        $messages = Message::where('event_id', $eventId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        $streams = '';

        $currentSenderId = auth('web')->check() ? auth('web')->id() : auth('admin')->id();
        $currentSenderType = auth('web')->check() ? \App\Models\User::class : \App\Models\Admin::class;

        foreach ($messages as $message) {
            $sender = $message->sender;
            $isAdmin = $sender instanceof \App\Models\Admin;

            $displayName = $isAdmin 
                ? '(Admin) ' . $sender->name 
                : $sender->first_name . ' ' . $sender->last_name;

            $profilePicture = $sender->profile_picture
                ? asset('storage/' . $sender->profile_picture)
                : asset('storage/default_profile_image.png');

            $isSender = $message->sender_id === $currentSenderId && $message->sender_type === $currentSenderType;

            $streams .= turbo_stream()->append(
                'messages',
                view('Components.messages._message', [
                    'message' => $message,
                    'displayName' => $displayName,
                    'profilePicture' => $profilePicture,
                    'isSender' => $isSender,
                ])
            );
        }


        return response($streams, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }




} 