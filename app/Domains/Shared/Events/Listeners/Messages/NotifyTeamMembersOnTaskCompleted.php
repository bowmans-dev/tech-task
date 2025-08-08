<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Utils\HtmlMinifier;
use App\Domains\Shared\Events\DomainEvents\Messages\TaskCompleted;
use App\Domains\Supporting\Websocket\{UserPayloadHelper, WorkerPayloadHelper, EventScopedConnectionsInternalClient};
use Illuminate\Support\Facades\{View, Log};

class NotifyTeamMembersOnTaskCompleted
{
    public function handle(TaskCompleted $event)
    {
        $completion = $event->completion->load('worker', 'task.taskList.message');
        $message = $completion->task->taskList->message->load('taskList.tasks.taskCompletions.worker');

        $userPayload = UserPayloadHelper::format($completion->worker);

        $tasksCollection = $message->is_task_list ? $message->taskList->tasks : collect();
        $taskCompletions = WorkerPayloadHelper::formatTaskCompletions($tasksCollection);

        $completedTaskIds = $tasksCollection
            ->filter(fn($task) => $task->taskCompletions->isNotEmpty())
            ->pluck('id')
            ->toArray();

        $tasks = $tasksCollection->map(fn($task) => [
            'id' => $task->id,
            'text' => $task->task_text,
        ]);

        $taskHtml = View::make('Components.messages.task-list-tasks', [
            'message' => $message,
            'tasks' => $tasks,
            'taskCompletions' => $taskCompletions,
            'completedTaskIds' => $completedTaskIds,
        ])->render();

        $minifiedHtml = HtmlMinifier::minify($taskHtml);

        $payload = [
            'action' => 'task_completed_broadcast',
            'message_id' => $message->id,
            'task_id' => $completion->task->id,
            'event_id' => $message->event_id,
            'user' => $userPayload,
            'html' => $minifiedHtml,
        ];

        (new EventScopedConnectionsInternalClient)->send($payload);
    }
}