<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordNotification;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request; // Add logging
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle login requests for admins and users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        try {
            Log::info('Entering login method'); // Log entry

            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            Log::info('Credentials provided:', $credentials); // Log credentials

            // Attempt admin login
            if (Auth::guard('admin')->attempt($credentials)) {
                Log::info('Admin login successful'); // Log admin login success
                $request->session()->regenerate();

                return $this->successResponse(null, 'Admin logged in successfully.', 200);
            }

            // Attempt user login
            if (Auth::guard('web')->attempt($credentials)) {
                Log::info('User login successful'); // Log user login success
                $request->session()->regenerate();

                return $this->successResponse(null, 'User logged in successfully.', 200);
            }

            // Authentication failed
            Log::info('Login failed: Invalid credentials'); // Log failure

            return $this->errorResponse('Invalid credentials provided.', 401);

        } catch (\Exception $e) {
            Log::error('Login exception:', ['message' => $e->getMessage()]); // Log exception

            return $this->errorResponse('Login failed.', 500, $e->getMessage());
        }
    }

    /**
     * Handle logout for both admin and user guards.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            Log::info('Entering logout method'); // Log entry

            // Logout admin guard if authenticated
            if (Auth::guard('admin')->check()) {
                Log::info('Admin guard detected, logging out'); // Log admin logout
                Auth::guard('admin')->logout();
            } else {
                // Logout web guard if authenticated
                Log::info('Web guard detected, logging out'); // Log user logout
                Auth::guard('web')->logout();
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();
            Log::info('Session invalidated and token regenerated'); // Log session actions

            return $this->successResponse(null, 'Logged out successfully.', 200);
        } catch (\Exception $e) {
            Log::error('Logout exception:', ['message' => $e->getMessage()]); // Log exception

            return $this->errorResponse('Logout failed.', 500, $e->getMessage());
        }
    }

    /**
     * Send reset password link.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLink(Request $request)
    {
        try {
            Log::info('Entering sendResetLink method'); // Log entry

            $request->validate(['email' => 'required|email']);
            Log::info('Email validated:', ['email' => $request->email]); // Log email validation

            $broker = $this->broker();
            $user = $broker->getUser($request->only('email'));

            if (! $user) {
                Log::info('User not found:', ['email' => $request->email]); // Log user not found

                return $this->errorResponse('User not found.', 404);
            }

            $token = $broker->createToken($user);
            Log::info('Reset token created:', ['token' => $token]); // Log token creation

            $user->notify(new ResetPasswordNotification($token));
            Log::info('Notification sent'); // Log notification

            return $this->successResponse(null, 'Reset password link sent successfully.', 200);
        } catch (\Exception $e) {
            Log::error('SendResetLink exception:', ['message' => $e->getMessage()]); // Log exception

            return $this->errorResponse('Failed to send reset password link.', 500, $e->getMessage());
        }
    }

    /**
     * Handle password reset requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        try {
            Log::info('Entering resetPassword method'); // Log entry

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|confirmed|min:8',
                'token' => 'required',
            ]);
            Log::info('Reset request validated:', $request->all()); // Log validation

            $response = $this->broker()->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    Log::info('Updating user password'); // Log password update
                    $user->password = bcrypt($password);
                    $user->save();
                }
            );

            if ($response == Password::PASSWORD_RESET) {
                Log::info('Password reset successful'); // Log success

                return $this->successResponse(null, 'Password reset successfully.', 200);
            }

            Log::info('Password reset failed:', ['response' => $response]); // Log failure

            return $this->errorResponse('Failed to reset password.', 400, trans($response));
        } catch (\Exception $e) {
            Log::error('ResetPassword exception:', ['message' => $e->getMessage()]); // Log exception

            return $this->errorResponse('An error occurred while resetting the password.', 500, $e->getMessage());
        }
    }

    /**
     * Display the password reset form.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function showResetForm($token)
    {
        Log::info('Displaying reset form with token:', ['token' => $token]); // Log token

        return $this->successResponse(['token' => $token], 'Reset token retrieved successfully.', 200);
    }

    /**
     * Use the default broker (users).
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    protected function broker()
    {
        Log::info('Using default password broker'); // Log broker usage

        return Password::broker();
    }
}
