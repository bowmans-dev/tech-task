<?php

namespace App\Domains\Shared\Events\Listeners\Messages;

use App\Domains\Shared\Events\DomainEvents\Messages\PollVoted;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class NotifyTeamMembersOnPollVote
{
    public function handle(PollVoted $event)
    {
        $vote = $event->vote;
        $message = $vote->message;
        $voter = $vote->voter;

        $currentUser = auth('admin')->check() ? auth('admin')->user() : auth('web')->user();
        $currentUserClass = get_class($currentUser);
        $currentUserId = $currentUser->id;

        $userPayload = [
            'id' => $voter->id,
            'type' => class_basename(get_class($voter)),
            'profile_picture' => $voter->profile_picture
                ? "/storage/{$voter->profile_picture}"
                : "/storage/default_profile_image.webp",
        ];

        if ($voter instanceof \App\Models\Admin) {
            $userPayload['name'] = $voter->name;
        } else {
            $userPayload['first_name'] = $voter->first_name;
            $userPayload['last_name'] = $voter->last_name;
        }

        $message->load('pollOptions.votes.voter');

        $selectedOptionId = null;
        if ($message->is_poll) {
            foreach ($message->pollOptions as $option) {
                foreach ($option->votes as $vote) {
                    if (
                        $vote->voter_id === $currentUserId &&
                        $vote->voter_type === $currentUserClass
                    ) {
                        $selectedOptionId = $option->id;
                        break 2;
                    }
                }
            }
        }

        $pollHtml = View::make('Components.messages.poll-options', [
            'message' => $message,
            'isPoll' => $message->is_poll,
            'options' => $message->is_poll ? $message->pollOptions : [],
            'selectedOptionId' => $selectedOptionId,
        ])->render();

        $payload = [
            'action' => 'vote_broadcast',
            'message_id' => $message->id,
            'event_id' => $message->event_id,
            'user' => $userPayload,
            'html' => $pollHtml,
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
