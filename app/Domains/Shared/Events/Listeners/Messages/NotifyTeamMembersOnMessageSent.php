<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Domains\Shared\Events\DomainEvents\Messages\MessageSent;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotifyTeamMembersOnMessageSent
{
    public function handle(MessageSent $event)
    {
        $message = $event->message;
        $eventName = $event->eventName;
        $options = $event->options;
        $tasks = $event->tasks;

        $teamMembers = User::whereHas('calendarEvents', function ($query) use ($message) {
            $query->where('calendar_event_id', $message->event_id);
        })->get();

        $socket = stream_socket_client("tcp://localhost:8080", $errno, $errstr, 5, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            Log::error("Websocket connection failed", ['error' => "{$errno}: {$errstr}"]);
            return;
        }

        // Websocket handshake
        $key = base64_encode(random_bytes(16));
        $handshake  = "GET /internal HTTP/1.1\r\n";
        $handshake .= "Host: localhost:8080\r\n";
        $handshake .= "Upgrade: websocket\r\n";
        $handshake .= "Connection: Upgrade\r\n";
        $handshake .= "Sec-WebSocket-Key: $key\r\n";
        $handshake .= "Sec-WebSocket-Version: 13\r\n\r\n";
        fwrite($socket, $handshake);

        // Read handshake response for optional debugging
        $response = fread($socket, 1500);

        $sender = $message->sender;
        $senderType = class_basename(get_class($sender));
        $profilePicture = $sender->profile_picture 
                ? "/storage/{$sender->profile_picture}" 
                : "/storage/default_profile_image.webp";

        $userPayload = [
            'id' => $sender->id,
            'type' => $senderType,
            'profile_picture' => $profilePicture,
        ];

        if ($senderType === 'Admin') {
            $userPayload['name'] = $sender->name;
            $displayName = $sender->name;
        } else {
            $userPayload['first_name'] = $sender->first_name;
            $userPayload['last_name'] = $sender->last_name;
            $displayName = $sender->first_name . " " . $sender->last_name;
        }

        $taskData = [];
        if ($message->is_task_list && $tasks->isNotEmpty()) {
            foreach ($tasks as $task) {
                $taskData[] = [
                    'id' => $task->id,
                    'text' => $task->task_text,
                ];
            }
        }


        // Render the full Blade message component to HTML
        $html = view('Components.messages._message', [
            'message' => $message, 
            'profilePicture' => $profilePicture, 
            'displayName' => $displayName, 
            'isSender' => null,
            'isPoll' => $message->is_poll,
            'options' => $message->is_poll ? $options : [],
            'selectedOptionId' => null,
            'isTaskList' => $message->is_task_list,
            'tasks' => $message->is_task_list ? $taskData : [],
        ])->render();

        // Final payload with both user info and Blade-rendered HTML
        $data = json_encode([
            'action'      => 'message_broadcast',
            'message_id'  => $message->id,
            'message' => $message->content,
            'created_at'  => $message->created_at,
            'event_id'    => $message->event_id,
            'event_name'  => $eventName,
            'sender_id'   => $message->sender_id,
            'user'        => $userPayload,
            'is_task_list' => $message->is_task_list,
            'tasks'       => $message->is_task_list ? $taskData : [],
            'html'        => $html,
        ]);


        $webSocketFrame = createWebSocketFrame($data);

        fwrite($socket, $webSocketFrame);
        fflush($socket);
        usleep(500000);
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