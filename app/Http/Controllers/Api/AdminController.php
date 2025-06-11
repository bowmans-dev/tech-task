<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use App\Domains\Core\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AdminController extends Controller
{
    use ApiResponseTrait;

    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function store(UserStoreRequest $request)
    {
        try {

            $admin = $request->user();

            if (!$admin) {
                return $this->errorResponse('Unauthorized access.', 401);
            }

            $data = $request->validated();
            $user = $this->userService->createUser($data);

            return $this->successResponse('User created successfully!', 201, $user);

        }  catch (\Exception $e) {
            Log::error('Error creating user:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Internal server error', 500, $e->getMessage());
        }
    }


    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $admin = $request->user();

            if (!$admin) {
                return $this->errorResponse('Unauthorized access.', 401);
            }

            $data = $request->validated();
            $updatedUser = $this->userService->updateUser($user, $data);

            return $this->successResponse('User updated successfully!', 200, $updatedUser);

        } catch (\Exception $e) {
            Log::error('Error updating user:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Failed to update user.', 500, $e->getMessage());
        }
    }
    

    public function show(Request $request, User $user)
    {
        try {
            $admin = $request->user();

            if (!$admin) {
                return $this->errorResponse('Unauthorized access.', 401);
            }

            return $this->successResponse('User details retrieved successfully.', 200, $user);

        } catch (\Exception $e) {
            Log::error('Error showing user: '.$e->getMessage());

            return $this->errorResponse('Failed to fetch user details.', 500, $e->getMessage());
        }
    }


    public function index(Request $request)
    {

        try {
            $admin = $request->user();

            if (!$admin) {
                return $this->errorResponse('Unauthorized access.', 401);
            }

            $users = $this->userService->listUsers();

            return $this->successResponse('Users retrieved successfully.', 200, $users);

        } catch (\Exception $e) {
            Log::error('Error retrieving users: '.$e->getMessage());

            return $this->errorResponse('Failed to fetch users.', 500, $e->getMessage());
        }
    }


    public function filter(Request $request)
    {
        try {
            $admin = $request->user();

            if (!$admin) {
                return $this->errorResponse('Unauthorized access.', 401);
            }
        
            $users = $this->userService->filterUsers($request->query('search'));

            return $this->successResponse('Users filtered successfully.', 200, $users);
        
        } catch (\Exception $e) {
            Log::error('Error filtering users:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Failed to filter users.', 500, $e->getMessage());
        }
    }
    

    public function destroy(Request $request, User $user)
    {
        try {
            $admin = $request->user();

            if (!$admin) {
                return $this->errorResponse('Unauthorized access.', 401);
            }

            $this->userService->deleteUser($user, 'admin');

            return $this->successResponse('User deleted successfully!', 200);

        } catch (\Exception $e) {
            Log::error('Error deleting user:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Failed to delete user.', 500, $e->getMessage());
        }
    }
    
}