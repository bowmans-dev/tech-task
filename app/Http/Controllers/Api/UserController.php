<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Core\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponseTrait;

    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function showProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Unauthorized access.', 401);
            }


            return $this->successResponse('Profile retrieved successfully.', 200, $user);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve profile.', 500, $e->getMessage());
        }
    }


    public function updateProfile(UserUpdateRequest $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Unauthorized access.', 401);
            }

            $data = $request->validated();

            $updatedUser = $this->userService->updateUser(
                $request->user(), 
                $data          
            );

            return $this->successResponse('Profile updated successfully.', 200, $updatedUser);

        } catch (\Exception $e) {

            return $this->errorResponse('Failed to update profile.', 500, $e->getMessage());
        }
    }


    public function deleteProfile(Request $request)
    {

        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Unauthorized access.', 401);
            }

            $this->userService->deleteProfile($user);

            return $this->successResponse('Your profile has been deleted successfully.', 200);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete profile.', 500, $e->getMessage());
        }
    }
}
