<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Domains\Shared\Events\DomainEvents\Messages\TaskCompleted;
use App\Domains\Supporting\Websocket\WebsocketClient;
use App\Domains\Supporting\Websocket\UserPayloadHelper;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class NotifyTeamMembersOnTaskCompleted
{
    public function handle(TaskCompleted $event)
    {
        $completion = $event->completion->load('worker', 'task.taskList.message');
        $task = $completion->task;
        $worker = $completion->worker;
        $message = $task->taskList->message;

        $userPayload = UserPayloadHelper::format($completion->worker);

        // Load fresh task completion data
        $message->load('taskList.tasks.taskCompletions.worker');

        $taskCompletions = $message->is_task_list 
            ? $message->taskList->tasks->mapWithKeys(fn ($task) => [$task->id => $task->taskCompletions->map(fn ($completion) => [
                'worker_id' => $completion->worker_id,
                'worker_type' => class_basename(get_class($completion->worker)),
                'profile_picture' => $completion->worker->profile_picture
                    ? asset('storage/' . $completion->worker->profile_picture)
                    : asset('storage/default_profile_image.webp'),
                'name' => $completion->worker instanceof \App\Models\Admin
                    ? '(Admin) ' . $completion->worker->name
                    : $completion->worker->first_name . ' ' . $completion->worker->last_name,
            ])]) 
            : [];


        $completedTaskIds = $message->is_task_list 
            ? $message->taskList->tasks->filter(fn ($task) => $task->taskCompletions->count() > 0)->pluck('id')->toArray() 
            : [];

        $taskHtml = View::make('Components.messages.task-list-tasks', [
            'message' => $message,
            'tasks' => $message->is_task_list ? $message->taskList->tasks->map(fn ($task) => [
                'id' => $task->id,
                'text' => $task->task_text,
            ]) : [],

            'taskCompletions' => $taskCompletions,
            'completedTaskIds' => $completedTaskIds,
        ])->render();

        $payload = [
            'action' => 'task_completed_broadcast',
            'message_id' => $message->id,
            'task_id' => $task->id,
            'event_id' => $message->event_id,
            'user' => $userPayload,
            'html' => $taskHtml,
        ];

        (new WebsocketClient())->send($payload);
    }
}