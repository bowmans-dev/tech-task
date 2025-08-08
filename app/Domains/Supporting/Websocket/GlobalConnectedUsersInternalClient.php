<?php

namespace App\Domains\Supporting\Websocket;

use App\Domains\Supporting\Websocket\BaseWebsocketClient;
use App\Domains\Supporting\Websocket\InternalWebsocketTokenGenerator;

class GlobalConnectedUsersInternalClient extends BaseWebsocketClient
{
    public function __construct()
    {
        $token = InternalWebsocketTokenGenerator::generate();
        parent::__construct('wss://localhost:8080/internal', $token);
    }

}