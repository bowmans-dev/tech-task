<?php

namespace App\Http\Controllers\Web;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hotwired\TurboLaravel\Turbo;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageSent;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageReacted;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\MessageReaction;



class MessageController extends Controller 
{
    private function formatMessageDate($createdAt)
    {
        $date = Carbon::parse($createdAt);
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $lastWeekStart = $startOfWeek->copy()->subWeek();

        if ($date->isToday()) {
            return 'Today';
        } elseif ($date->isYesterday()) {
            return 'Yesterday';
        } elseif ($date->greaterThanOrEqualTo($lastWeekStart) && $date->lessThan($today)) {
            return $date->format('l'); // day of the week
        } else {
            return $date->format('d M Y'); // e.g. 12 May 2025
        }
    }

    public function store(Request $request)
    {
        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sender = $auth->user();

        $eventName = $request->input('event_name');

        $message = Message::create([
            'sender_id' => $sender->id,
            'sender_type' => get_class($sender), // App\Models\User or App\Models\Admin
            'content' => $request->input('content'),
            'event_id' => $request->input('event_id'),
        ]);

        // Dispatch domain event via the publisher
        DomainEventPublisher::publish(new MessageSent($message, $eventName));

        // Eager-load sender for the view
        $message->load(['sender', 'reactions.user']);

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

        // Now apply the reaction logic with proper authentication
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


    public function fetchMessages(Request $request, $eventId)
    {
        $messages = Message::where('event_id', $eventId)
            ->with(['sender', 'reactions.user'])
            ->orderBy('created_at', 'asc')
            ->get();


        $streams = '';

        $currentSenderId = auth('web')->check() ? auth('web')->id() : auth('admin')->id();
        $currentSenderType = auth('web')->check() ? \App\Models\User::class : \App\Models\Admin::class;

        
        $groupedMessages = [];

        foreach ($messages as $message) {
            $dateGroup = $this->formatMessageDate($message->created_at);
            $groupedMessages[$dateGroup][] = $message;
        }

        foreach ($groupedMessages as $dateLabel => $groupMessages) {

            // Insert date label as a Turbo Stream partial
            $streams .= turbo_stream()->append(
                'messages',
                view('Components.messages._date_separator', ['dateLabel' => $dateLabel])
            );

            foreach ($groupMessages as $message) {
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
        }


        return response($streams, 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
    }

} 