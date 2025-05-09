<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        '/broadcasting/auth',
    ];
    

    public function handle($request, Closure $next)
    {
        // \Log::info('VerifyCsrfToken middleware invoked.');
        return parent::handle($request, $next);
    }
}
