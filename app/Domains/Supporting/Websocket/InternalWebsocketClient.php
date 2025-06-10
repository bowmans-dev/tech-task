<?php

namespace App\Domains\Supporting\Websocket;

use Illuminate\Support\Facades\Log;

class InternalWebsocketClient
{
    private $socket;

    public function __construct(string $host = "localhost", int $port = 8080)
    {
        $this->socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 5, STREAM_CLIENT_CONNECT);

        if (!$this->socket) {
            Log::error("WebSocket connection failed", ['error' => "{$errno}: {$errstr}"]);
            return;
        }

        $this->handshake($host, $port);
    }

    private function handshake(string $host, int $port): void
    {
        $key = base64_encode(random_bytes(16));
        $handshake = "GET /internal HTTP/1.1\r\n"
            . "Host: {$host}:{$port}\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Key: {$key}\r\n"
            . "Sec-WebSocket-Version: 13\r\n\r\n";

        fwrite($this->socket, $handshake);
        fread($this->socket, 1500); // Read handshake response
    }

    public function send(array $payload): void
    {
        if (!$this->socket) return;

        $data = json_encode($payload);
        fwrite($this->socket, $this->createFrame($data));
        fclose($this->socket);
    }

    private function createFrame(string $data): string
    {
        $dataLength = strlen($data);
        $frameHead = [0x81]; // FIN + text frame opcode

        if ($dataLength <= 125) {
            $frameHead[] = $dataLength | 0x80;
        } elseif ($dataLength <= 65535) {
            $frameHead[] = 126 | 0x80;
            $frameHead[] = ($dataLength >> 8) & 0xFF;
            $frameHead[] = $dataLength & 0xFF;
        } else {
            $frameHead[] = 127 | 0x80;
            for ($i = 7; $i >= 0; $i--) {
                $frameHead[] = ($dataLength >> (8 * $i)) & 0xFF;
            }
        }

        $mask = pack("N", rand(0, 0xFFFFFFFF));
        $frameHead = array_merge($frameHead, unpack("C*", $mask));

        return pack("C*", ...$frameHead) . $this->maskPayload($data, $mask);
    }

    private function maskPayload(string $data, string $mask): string
    {
        $maskedData = '';
        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $maskedData .= $data[$i] ^ $mask[$i % 4];
        }
        return $maskedData;
    }
}