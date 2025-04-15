<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        try {
            // Call the service layer to create the user
            $user = $this->userService->createUser($request->all(), 'admin');

            // Return a standardized success response
            return $this->successResponse($user, 'User created successfully!', 201);

        } catch (ValidationException $e) {

            // Return validation errors as a 422 response
            return response()->json(['errors' => $e->errors()], 422);

        } catch (\Exception $e) {

            // Log the error and return a standardized error response
            Log::error('Error creating user: '.$e->getMessage());

            return $this->errorResponse('Failed to create user.', 500, $e->getMessage());
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
     * Update a user's details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        try {
            $updatedUser = $this->userService->updateUser($user, $request->all(), 'admin');

            return $this->successResponse($updatedUser, 'User updated successfully!', 200);

        } catch (\Exception $e) {
            Log::error('Error updating user: '.$e->getMessage());

            return $this->errorResponse('Failed to update user.', 500, $e->getMessage());
        }
    }
}
