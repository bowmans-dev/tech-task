<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Models\User;
use App\Domains\Shared\Events\DomainEvents\Messages\MessageSent;
use App\Domains\Supporting\Websocket\{UserPayloadHelper, InternalWebsocketClient};

class NotifyTeamMembersOnMessageSent
{
    public function handle(MessageSent $event)
    {
        $message = $event->message;
        $eventName = $event->eventName;
        $userPayload = UserPayloadHelper::format($message->sender);
        $teamMembers = User::whereHas('calendarEvents', fn($query) => $query->where('calendar_event_id', $message->event_id))->get();
        $options = $event->options;
        $tasks = $event->tasks;
        $taskData = collect($tasks)->map(fn($task) => ['id' => $task->id, 'text' => $task->task_text])->toArray();

        $html = view('Components.messages._message', [
            'message' => $message, 
            'profilePicture' => $userPayload['profile_picture'], 
            'displayName' => $userPayload['display_name'], 
            'isSender' => null,
            'isPoll' => $message->is_poll,
            'options' => $message->is_poll ? $options : [],
            'selectedOptionId' => null,
            'isTaskList' => $message->is_task_list,
            'tasks' => $message->is_task_list ? $taskData : [],
        ])->render();

        $payload = [
            'action'      => 'message_broadcast',
            'message_id'  => $message->id,
            'message'     => $message->content,
            'created_at'  => $message->created_at,
            'event_id'    => $message->event_id,
            'event_name'  => $eventName,
            'sender_id'   => $message->sender_id,
            'user'        => $userPayload,
            'is_task_list'=> $message->is_task_list,
            'tasks'       => $taskData,
            'html'        => $html,
        ];

        (new InternalWebsocketClient())->send($payload);
    }
}