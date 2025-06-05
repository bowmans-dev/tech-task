<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Domains\Shared\Events\DomainEvents\Messages\MessageReacted;
use Illuminate\Support\Facades\View;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotifyTeamMembersOnReactionSent
{
    public function handle(MessageReacted $event)
    {

        $reaction = $event->reaction;
        $message = $reaction->message;
        $user = $reaction->user;

        // Determine user type
        $userType = class_basename(get_class($user));

        // Construct user payload
        $userPayload = [
            'id' => $user->id,
            'type' => $userType,
            'profile_picture' => $user->profile_picture 
                ? "/storage/{$user->profile_picture}" 
                : "/storage/default_profile_image.webp", 
        ];

        if ($userType === 'Admin') {
            $userPayload['name'] = $user->name;
        } else {
            $userPayload['first_name'] = $user->first_name;
            $userPayload['last_name'] = $user->last_name;
        }

        $teamMembers = User::whereHas('calendarEvents', function ($query) use ($message) {
            $query->where('calendar_event_id', $message->event_id);
        })->get();

        // Render the updated reactions HTML
        $reactionHtml = View::make('Components.messages._reactions', [
            'message' => $message,
        ])->render();

        // Construct broadcast payload
        $reactionData = [
            'action' => 'reaction_broadcast',
            'message_id' => $reaction->message_id,
            'event_id' => $message->event_id,
            'emoji' => $reaction->emoji,
            'user' => $userPayload,
            'html' => $reactionHtml,
        ];


        $json = json_encode($reactionData);

        $frame = createWebSocketFrame($json);

        $socket = stream_socket_client("tcp://localhost:8080", $errno, $errstr, 5);
        if (!$socket) {
            Log::error("[WS] Connection failed", compact('errno', 'errstr'));
            return;
        }

        $key = base64_encode(random_bytes(16));
        $handshake  = "GET / HTTP/1.1\r\n";
        $handshake .= "Host: localhost:8080\r\n";
        $handshake .= "Upgrade: websocket\r\n";
        $handshake .= "Connection: Upgrade\r\n";
        $handshake .= "Sec-WebSocket-Key: $key\r\n";
        $handshake .= "Sec-WebSocket-Version: 13\r\n\r\n";

        fwrite($socket, $handshake);

        $response = fread($socket, 1500);

        if (!str_contains($response, '101 Switching Protocols')) {
            Log::error('[WS] WebSocket handshake failed.');
            fclose($socket);
            return;
        }

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
