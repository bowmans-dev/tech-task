<?php

namespace App\Domains\Supporting\Websocket;

use App\Models\Admin;

class WorkerPayloadHelper
{
    public static function format($worker): array
    {
        return [
            'worker_id' => $worker->id,
            'worker_type' => class_basename(get_class($worker)),
            'profile_picture' => asset($worker->profile_picture ? 'storage/' . $worker->profile_picture : 'storage/default_profile_image.webp'),
            'name' => $worker instanceof Admin
                ? '(Admin) ' . $worker->name
                : "{$worker->first_name} {$worker->last_name}",
        ];
    }


    public static function formatTaskCompletions($tasks): array
    {
        return $tasks->mapWithKeys(fn ($task) => [
            $task->id => $task->taskCompletions->map(fn ($completion) => self::format($completion->worker))
        ])->toArray();
    }

    
    public static function getCompletedTaskIds($tasks, $currentUser): array
    {
        return $tasks->filter(fn($task) =>
            $task->taskCompletions->contains(fn($completion) =>
                $completion->worker_id === $currentUser->id &&
                $completion->worker_type === get_class($currentUser)
            )
        )->pluck('id')->toArray();
    }

}