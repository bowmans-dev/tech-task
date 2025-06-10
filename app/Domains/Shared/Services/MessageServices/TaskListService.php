<?php

namespace App\Domains\Shared\Services\MessageServices;

use App\Models\TaskList;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Messages\TaskCompleted;
use Illuminate\Http\Request;

class TaskListService
{
    public function createTaskList($messageId, $topic, $tasks)
    {
        $taskList = TaskList::create([
            'message_id' => $messageId,
            'topic' => $topic
        ]);

        $taskCollection = collect();
        foreach ($tasks as $taskText) {
            $taskCollection->push(Task::create([
                'task_list_id' => $taskList->id,
                'task_text' => $taskText,
            ]));
        }

        return ['taskList' => $taskList, 'tasks' => $taskCollection];
    }

    public function getTaskData($message, $currentUser)
    {
        $tasks = [];
        $taskCompletions = [];
        $completedTaskIds = [];

        if ($message->is_task_list && $message->taskList) {
            foreach ($message->taskList->tasks as $task) {
                $tasks[] = ['id' => $task->id, 'text' => $task->task_text];

                $taskCompletions[$task->id] = [];
                foreach ($task->taskCompletions as $completion) {
                    $worker = $completion->worker;
                    $taskCompletions[$task->id][] = [
                        'worker_id' => $worker->id,
                        'worker_type' => class_basename(get_class($worker)),
                        'profile_picture' => $worker->profile_picture ? asset('storage/' . $worker->profile_picture) : asset('storage/default_profile_image.webp'),
                        'name' => $worker instanceof Admin ? '(Admin) ' . $worker->name : $worker->first_name . ' ' . $worker->last_name,
                    ];

                    if ($completion->worker_id === $currentUser->id && $completion->worker_type === get_class($currentUser)) {
                        $completedTaskIds[] = $task->id;
                    }
                }
            }
        }

        return compact('tasks', 'taskCompletions', 'completedTaskIds');
    }

    public function complete(Request $request)
    {
        $auth = auth('admin')->check() ? auth('admin') : auth('web');

        if (!$auth->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $worker = $auth->user();
        $taskId = $request->input('task_id');

        $existingCompletion = TaskCompletion::where([
            'task_id' => $taskId,
            'worker_id' => $worker->id,
            'worker_type' => get_class($worker),
        ])->first();

        if ($existingCompletion) {
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
}