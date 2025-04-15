<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Core\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $data = $request->all();

            // Preserve password_confirmation for validation
            if ($request->has('password') && $request->has('password_confirmation')) {
                $data['password_confirmation'] = $request->input('password_confirmation');
            }

            $updatedUser = $this->userService->updateUser(
                auth()->user(),
                $data, // Pass all fields including password_confirmation
                'user',
                auth()->id()
            );

            return $this->successResponse($updatedUser, 'Profile updated successfully.', 200);

        } catch (\Exception $e) {
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
