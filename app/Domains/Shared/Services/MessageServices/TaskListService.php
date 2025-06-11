<?php

namespace App\Domains\Shared\Services\MessageServices;

use App\Models\{TaskList, Task, TaskCompletion};
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Messages\TaskCompleted;
use App\Domains\Supporting\Websocket\WorkerPayloadHelper;
use Illuminate\Http\Request;

class TaskListService
{
    public function createTaskList($messageId, $topic, $tasks)
    {
        $taskList = TaskList::create(['message_id' => $messageId, 'topic' => $topic]);

        $taskCollection = collect($tasks)->map(fn($taskText) => Task::create([
            'task_list_id' => $taskList->id,
            'task_text' => $taskText,
        ]));

        return ['taskList' => $taskList, 'tasks' => $taskCollection];
    }


    public function getTaskData($message, $currentUser)
    {
        if (!$message->is_task_list || !$message->taskList) {
            return ['tasks' => [], 'taskCompletions' => [], 'completedTaskIds' => []];
        }

        return [
            'tasks' => $message->taskList->tasks->map(fn($task) => ['id' => $task->id, 'text' => $task->task_text]),
            'taskCompletions' => WorkerPayloadHelper::formatTaskCompletions($message->taskList->tasks),
            'completedTaskIds' => WorkerPayloadHelper::getCompletedTaskIds($message->taskList->tasks, $currentUser),
        ];
    }


    public function complete(Request $request)
    {
        $auth = $this->getAuthenticatedUser();
        if (!$auth) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $worker = $auth->user();
        $taskId = $request->input('task_id');

        if (TaskCompletion::where(['task_id' => $taskId, 'worker_id' => $worker->id, 'worker_type' => get_class($worker)])->exists()) {
            return response()->json(['message' => 'Task already completed.'], 200);
        }

        $completion = TaskCompletion::create([
            'task_id' => $taskId,
            'worker_id' => $worker->id,
            'worker_type' => get_class($worker),
        ]);

        DomainEventPublisher::publish(new TaskCompleted($completion));

        return response()->json(['message' => 'Task marked as completed.']);
    }

    
    private function getAuthenticatedUser()
    {
        return (auth('admin')->check() ? auth('admin') : auth('web')->check()) ? auth('web') : null;
    }
}