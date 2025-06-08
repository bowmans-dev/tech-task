<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Domains\Shared\Events\DomainEvents\Messages\TaskCompleted;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class NotifyTeamMembersOnTaskCompleted
{
    public function handle(TaskCompleted $event)
    {
        $completion = $event->completion->load('worker', 'task.taskList.message');
        $task = $completion->task;
        $worker = $completion->worker;
        \Log::info("Task Completion Worker:", ['worker' => $completion->worker]);
        $message = $task->taskList->message;

        $profilePic = $worker->profile_picture
            ? "/storage/{$worker->profile_picture}"
            : "/storage/default_profile_image.webp";

        $userPayload = [
            'id' => $worker->id,
            'type' => class_basename(get_class($worker)),
            'profile_picture' => $profilePic,
        ];

        if ($worker instanceof \App\Models\Admin) {
            $userPayload['name'] = $worker->name;
        } else {
            $userPayload['first_name'] = $worker->first_name;
            $userPayload['last_name'] = $worker->last_name;
        }

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
            ? $message->taskList->tasks->flatMap(fn ($task) => $task->taskCompletions->pluck('worker_id'))->toArray() 
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

        $json = json_encode($payload);
        $frame = createWebSocketFrame($json);

        $socket = stream_socket_client("tcp://localhost:8080", $errno, $errstr, 5);
        if (!$socket) return;

        $handshake = "GET / HTTP/1.1\r\n"
            . "Host: localhost:8080\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Key: " . base64_encode(random_bytes(16)) . "\r\n"
            . "Sec-WebSocket-Version: 13\r\n\r\n";

        fwrite($socket, $handshake);
        fread($socket, 1500); // handshake response
        fwrite($socket, $frame);
        fclose($socket);
    }
}

// Format Websocket frames
function createWebSocketFrame($data)
{
    $dataLength = strlen($data);
    $frameHead = [];
    $frameHead[0] = 0x81; // FIN + text frame opcode

    if ($dataLength <= 125) {
        $frameHead[1] = $dataLength | 0x80; // Mask bit must be set
    } elseif ($dataLength <= 65535) {
        $frameHead[1] = 126 | 0x80;
        $frameHead[] = ($dataLength >> 8) & 0xFF;
        $frameHead[] = $dataLength & 0xFF;
    } else {
        $frameHead[1] = 127 | 0x80;
        for ($i = 7; $i >= 0; $i--) {
            $frameHead[] = ($dataLength >> (8 * $i)) & 0xFF;
        }
    }

    $mask = pack("N", rand(0, 0xFFFFFFFF)); // 4-byte mask
    $frameHead = array_merge($frameHead, unpack("C*", $mask));

    // Apply the mask to the payload
    $maskedData = '';
    for ($i = 0; $i < $dataLength; $i++) {
        $maskedData .= $data[$i] ^ $mask[$i % 4];
    }

    return pack("C*", ...$frameHead) . $maskedData;
}