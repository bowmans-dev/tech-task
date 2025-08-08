<?php

namespace App\Domains\Supporting\Websocket;

use App\Domains\Supporting\Websocket\BaseWebsocketClient;
use App\Domains\Supporting\Websocket\InternalWebsocketTokenGenerator;

class EventScopedConnectionsInternalClient extends BaseWebsocketClient
{
    public function __construct(string $url = "wss://localhost:8080/")
    {
        $token = InternalWebsocketTokenGenerator::generate();
        parent::__construct($url, $token);
    }
}