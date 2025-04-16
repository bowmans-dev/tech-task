<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Domains\Core\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;


class AdminController extends Controller
{
    use ApiResponseTrait;

    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Create a new user (admin action).
     *
     * Validates incoming data and handles user creation via service layer.
     *
     * @param \App\Http\Requests\UserStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserStoreRequest $request)
    {
        try {
            $data = $request->validated(); // Automatically validate the request data
            $user = $this->userService->createUser($data);

            // Use successResponse from ApiResponseTrait
            return $this->successResponse($user, 'User created successfully!', 201);
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());

            // Use errorResponse from ApiResponseTrait
            return $this->errorResponse('Internal server error', 500, $e->getMessage());
        }
    }


    /**
     * Update a user's details.
     *
     * @param \App\Http\Requests\UserUpdateRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $data = $request->validated(); // Automatically validates the request data
            $updatedUser = $this->userService->updateUser($user, $data);

            // Use successResponse from ApiResponseTrait
            return $this->successResponse($updatedUser, 'User updated successfully!', 200);
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());

            // Use errorResponse from ApiResponseTrait
            return $this->errorResponse('Failed to update user.', 500, $e->getMessage());
        }
    }
    

    /**
     * Show the details of a specific user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        try {
            return $this->successResponse($user, 'User details retrieved successfully.', 200);
        } catch (\Exception $e) {
            Log::error('Error showing user: '.$e->getMessage());

            return $this->errorResponse('Failed to fetch user details.', 500, $e->getMessage());
        }
    }


    /**
     * Display a paginated list of all users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $users = $this->userService->listUsers();

            return $this->successResponse($users, 'Users retrieved successfully.', 200);

        } catch (\Exception $e) {
            Log::error('Error retrieving users: '.$e->getMessage());

            return $this->errorResponse('Failed to fetch users.', 500, $e->getMessage());
        }
    }


    /**
     * Filter users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        try {
            $users = $this->userService->filterUsers($request->query('search'));

            return $this->successResponse($users, 'Users filtered successfully.', 200);

        } catch (\Exception $e) {
            Log::error('Error filtering users: '.$e->getMessage());

            return $this->errorResponse('Failed to filter users.', 500, $e->getMessage());
        }
    }


    /**
     * Delete a user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        try {
            // Call the service layer to delete the user
            $this->userService->deleteUser($user, 'admin');

            return $this->successResponse(null, 'User deleted successfully!', 200);

        } catch (\Exception $e) {
            Log::error('Error deleting user: '.$e->getMessage());

            return $this->errorResponse('Failed to delete user.', 500, $e->getMessage());
        }
    }
    
}