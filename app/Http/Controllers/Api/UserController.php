<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Core\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponseTrait;

    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }



    /**
     * Show the authenticated user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showProfile()
    {
        try {
            // Call the service to retrieve the user's profile
            $profile = $this->userService->showProfile();

            return $this->successResponse($profile, 'Profile retrieved successfully.', 200);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve profile.', 500, $e->getMessage());
        }
    }



    /**
     * Update the authenticated user's profile.
     *
     * @param \App\Http\Requests\UserUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(UserUpdateRequest $request)
    {
        try {

            $data = $request->validated();

            $updatedUser = $this->userService->updateUser(
                auth()->user(), 
                $data          
            );

            return $this->successResponse($updatedUser, 'Profile updated successfully.', 200);

        } catch (\Exception $e) {

            Log::error('Error updating profile: ' . $e->getMessage());
            return $this->errorResponse('Failed to update profile.', 500, $e->getMessage());
        }
    }



    /**
     * Delete the authenticated user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProfile()
    {
        try {
            $this->userService->deleteProfile();

            return $this->successResponse(null, 'Your profile has been deleted successfully.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete profile.', 500, $e->getMessage());
        }
    }
}
