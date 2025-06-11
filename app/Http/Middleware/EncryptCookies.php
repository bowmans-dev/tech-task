<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        // \Log::info('EncryptCookies middleware invoked.');
        return parent::handle($request, $next);
    }
}
