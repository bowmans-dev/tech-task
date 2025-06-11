<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domains\Generic\Authentication\AuthenticationService;
use App\Domains\Generic\Authentication\PasswordResetService;
use App\Domains\Core\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;


class AuthController extends Controller
{
    protected AuthenticationService $authService;

    protected PasswordResetService $passwordService;

    protected UserService $userService;

    public function __construct(AuthenticationService $authService, PasswordResetService $passwordService, UserService $userService)
    {
        $this->authService = $authService;
        $this->passwordService = $passwordService;
        $this->userService = $userService;
    }


    public function store(UserStoreRequest $request)
    {

        $data = $request->validated();

        $this->userService->createUser($data);


        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }


    public function showRegistrationForm()
    {
        return view('shared.pages.user.register');
    }


    public function showLoginForm()
    {
        return view('shared.pages.auth.sign-in');
    }


    public function login(Request $request)
    {

        if ($request->isMethod('get')) {
            return view('shared.pages.auth.sign-in');
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($this->authService->login($credentials, 'admin')) {
            $request->session()->regenerate();

            return redirect('/users');
        }

        if ($this->authService->login($credentials, 'web')) {
            $request->session()->regenerate();

            return redirect('/profile');
        }


        $errorMessage = 'The provided credentials are incorrect.';
        return back()
            ->withErrors(['email' => $errorMessage])
            ->with('auth.failed', $errorMessage)
            ->withInput();
    }


    public function logout(Request $request)
    {
        $guard = Auth::guard('admin')->check() ? 'admin' : 'web';
        $this->authService->logout($guard);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }


    protected function broker()
    {
        return Password::broker();
    }


    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $this->passwordService->sendResetLink($request->input('email'));

            return back()->with('status', 'Password reset link sent.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }


    public function resetPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required',
        ]);

        $response = $this->passwordService->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
        );

        return $response === \Password::PASSWORD_RESET
            ? redirect('/users')->with('success', 'Password has been reset.')
            : back()->withErrors(['email' => 'Unable to reset password.']);
    }


    public function showResetForm($token)
    {
        return view('shared.pages.auth.passwords.reset', ['token' => $token]);
    }
}
