<?php

namespace App\Domains\Supporting\Websocket;

use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Admin;

class InternalWebsocketTokenGenerator
{
    public static function generate(): string
    {
        $customClaims = [
            'iss' => 'internal-websocket-client',
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addMinutes(5)->timestamp,
        ];

        $admin = Admin::find(1);

        if (!$admin) {
            throw new \Exception('System user not found');
        }

        return JWTAuth::customClaims($customClaims)->fromUser($admin);
    }
}