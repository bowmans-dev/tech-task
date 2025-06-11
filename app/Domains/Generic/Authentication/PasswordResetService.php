<?php

namespace App\Domains\Generic\Authentication;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;

class PasswordResetService
{
    public function sendResetLink(string $email): void
    {
        $broker = Password::broker();
        $user = $broker->getUser(['email' => $email]);

        if (! $user) {
            throw new \Exception('User not found');
        }

        $token = $broker->createToken($user);
        $user->notify(new ResetPasswordNotification($token));
    }

 
    public function resetPassword(array $credentials, \Closure $callback): string
    {
        return Password::broker()->reset($credentials, $callback);
    }
}
