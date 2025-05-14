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
        Log::info("Sending message update", ['message' => $message->toArray()]);

        $teamMembers = User::whereHas('calendarEvents', function ($query) use ($message) {
            $query->where('calendar_event_id', $message->event_id);
        })->get();
        Log::info("Team members:", ['members' => $teamMembers]);

        foreach ($teamMembers as $user) {
            Log::info("Sending message update to user {$user->id}");
        }

        $socket = stream_socket_client("tcp://localhost:8080", $errno, $errstr, 5, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            Log::error("Websocket connection failed", ['error' => "{$errno}: {$errstr}"]);
            return;
        }
        Log::info("Websocket connection established successfully.");

        // Websocket handshake
        $key = base64_encode(random_bytes(16));
        $handshake  = "GET /internal HTTP/1.1\r\n";
        $handshake .= "Host: localhost:8080\r\n";
        $handshake .= "Upgrade: websocket\r\n";
        $handshake .= "Connection: Upgrade\r\n";
        $handshake .= "Sec-WebSocket-Key: $key\r\n";
        $handshake .= "Sec-WebSocket-Version: 13\r\n\r\n";
        fwrite($socket, $handshake);

        // Read handshake response for debugging
        $response = fread($socket, 1500);
        Log::info("🤝 Handshake response:", ['response' => $response]);

        $data = json_encode([
            'action'   => 'message_broadcast',
            'event_id' => $message->event_id,
            'message'  => $message->content,
            'user'     => [
                'id'             => $message->sender->id,
                'first_name'     => $message->sender->first_name,
                'last_name'      => $message->sender->last_name,
                'profile_picture'=> $message->sender->profile_picture 
                    ? "/storage/{$message->sender->profile_picture}" 
                    : "/storage/default_profile_image.png"
            ],
        ]);

        $webSocketFrame = createWebSocketFrame($data);
        Log::info("WebSocket Frame Being Sent:", ['frame' => bin2hex($webSocketFrame)]);

        fwrite($socket, $webSocketFrame);
        fflush($socket);
        usleep(500000);
        fclose($socket);

        Log::info("WebSocket message sent successfully:", ['data' => $data]);
    }
}

/**
 * Format Websocket frames correctly
 */
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