<?php

namespace App\Domains\Supporting\Websocket;

use Illuminate\Support\Facades\Log;

abstract class BaseWebsocketClient
{
    protected $socket;
    protected string $host;
    protected int $port;
    protected string $path;
    protected ?string $protocol; // Sec-WebSocket-Protocol header (internal jwt service token header, optional protocol strings)

    public function __construct(string $url, ?string $protocol = null)
    {
        $this->protocol = $protocol;

        $parts = parse_url($url);
        if (!$parts || !isset($parts['host'])) {
            throw new \InvalidArgumentException("Invalid URL: $url");
        }

        $scheme = $parts['scheme'] ?? 'ws';
        $this->host = $parts['host'];
        $this->port = $parts['port'] ?? ($scheme === 'wss' ? 443 : 80);
        $this->path = $parts['path'] ?? '/';

        $transport = $scheme === 'wss' ? 'ssl' : 'tcp';

        $context = null;
        if ($transport === 'ssl') {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,      // disable in dev, enable in prod with proper CA
                    'verify_peer_name' => false,
                ],
            ]);
        }

        $this->socket = @stream_socket_client("{$transport}://{$this->host}:{$this->port}", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);

        if (!$this->socket) {
            Log::error("WebSocket connection failed", ['error' => "{$errno}: {$errstr}"]);
            return;
        }

        $this->handshake();
    }


    protected function handshake(): void
    {
        $key = base64_encode(random_bytes(16));
        $handshake = "GET {$this->path} HTTP/1.1\r\n"
            . "Host: {$this->host}:{$this->port}\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Key: {$key}\r\n"
            . "Sec-WebSocket-Version: 13\r\n";

        if ($this->protocol) {
            $handshake .= "Sec-WebSocket-Protocol: {$this->protocol}\r\n";
        }

        $handshake .= "\r\n";

        fwrite($this->socket, $handshake);
        fread($this->socket, 1500);
    }

    public function send(array $payload): void
    {
        if (!$this->socket) return;
        // Log::debug('WebSocket outgoing payload', $payload);

        $data = json_encode($payload);
        fwrite($this->socket, $this->createFrame($data));
        fclose($this->socket);
    }

    protected function createFrame(string $data): string
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

    protected function maskPayload(string $data, string $mask): string
    {
        $maskedData = '';
        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $maskedData .= $data[$i] ^ $mask[$i % 4];
        }
        return $maskedData;
    }
}
