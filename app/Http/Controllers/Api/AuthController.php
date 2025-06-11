<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordNotification;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request; // Add logging
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        try {

            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $isAdmin = Admin::where('email', $credentials['email'])->exists();
            $isUser  = User::where('email', $credentials['email'])->exists();

            if ($isAdmin) {
                $guard = 'admin:api';
            } 
            if ($isUser) {
                $guard = 'user:api';
            } 

            if (!isset($guard)) {
                return $this->errorResponse('Invalid credentials provided.', 401);
            }

            auth()->shouldUse($guard);

            if (!$token = JWTAuth::attempt($credentials)) {
                Log::warning('JWT Attempt Failed', ['credentials' => $credentials['email']]);
                return $this->errorResponse('Invalid credentials provided.', 401);
            }

            return $this->successResponse(ucfirst($guard) . ' logged in successfully.', 200, null, $token);
            
        } catch (JWTException $e) {
            Log::error('JWT Exception:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Could not create token.', 500, $e->getMessage());

        } catch (\Exception $e) {
            Log::error('Login Exception:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Login failed.', 500, $e->getMessage());
        }
    }


    public function logout(Request $request)
    {
        try {

            $token = $request->bearerToken();

            if (!$token) {
                return $this->errorResponse('Token not provided.', 401);
            }
            
            // Bind the token to the JWTAuth instance.
            JWTAuth::setToken($token);
            

            $admin = Auth::guard('admin:api')->user();

            if ($admin) {
                Log::info('Admin authenticated for logout:', ['user' => $admin]);
                JWTAuth::invalidate($token); // Invalidate the provided token.
                return $this->successResponse('Admin logged out successfully.', 200);
            }

            $user = Auth::guard('user:api')->user();

            if ($user) {
                Log::info('User authenticated for logout:', ['user' => $user]);
                JWTAuth::invalidate($token);
                return $this->successResponse('User logged out successfully.', 200);
            }

            Log::warning('Unauthorized logout attempt.');
            return $this->errorResponse('Unauthorized.', 401);
            
        } catch (JWTException $e) {
            Log::error('Logout failed due to invalid token:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Invalid or expired token.', 401);

        } catch (\Exception $e) {
            Log::error('Logout exception:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Logout failed.', 500, $e->getMessage());
        }
    }
  
    
    public function sendResetLink(Request $request)
    {
        try {
            Log::info('Entering sendResetLink method');

            $request->validate(['email' => 'required|email']);
            Log::info('Email validated:', ['email' => $request->email]);

            $broker = $this->broker();
            $user = $broker->getUser($request->only('email'));

            if (! $user) {
                Log::info('User not found:', ['email' => $request->email]);

                return $this->errorResponse('User not found.', 404);
            }

            $token = $broker->createToken($user);
            Log::info('Reset token created:', ['token' => $token]);

            $user->notify(new ResetPasswordNotification($token));
            Log::info('Notification sent');

            return $this->successResponse('Reset password link sent successfully.', 200);

        } catch (\Exception $e) {
            
            Log::error('SendResetLink exception:', ['message' => $e->getMessage()]);

            return $this->errorResponse('Failed to send reset password link.', 500, $e->getMessage());
        }
    }


    public function resetPassword(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|confirmed|min:8',
                'token' => 'required',
            ]);

            $response = $this->broker()->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    Log::info('Updating user password');
                    $user->password = bcrypt($password);
                    $user->save();
                }
            );

            if ($response == Password::PASSWORD_RESET) {
                Log::info('Password reset successful');

                return $this->successResponse('Password reset successfully.', 200);
            }

            return $this->errorResponse('Failed to reset password.', 400, trans($response));

        } catch (\Exception $e) {

            return $this->errorResponse('An error occurred while resetting the password.', 500, $e->getMessage());
        }
    }


    public function showResetForm($token)
    {
        Log::info('Displaying reset form with token:', ['token' => $token]);

        return $this->successResponse(['token' => $token], 'Reset token retrieved successfully.', 200);
    }


    protected function broker()
    {
        Log::info('Using default password broker');

        return Password::broker();
    }
}
