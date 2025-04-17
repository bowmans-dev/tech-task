<?php

namespace App\Domains\Generic\Authentication;

use Illuminate\Support\Facades\Auth;

class AuthenticationService
{
    public function login(array $credentials, string $guard): bool
    {
        return Auth::guard($guard)->attempt($credentials);
    }



    public function logout(string $guard): void
    {
        Auth::guard($guard)->logout();
    }


    
    public function regenerateSession(): void
    {
        session()->regenerate(); // Ensure the session is fully regenerated
        session()->invalidate(); // Invalidate old session data
        session()->regenerateToken(); // Regenerate CSRF token
    }
}
