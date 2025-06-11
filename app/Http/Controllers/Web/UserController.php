<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domains\Core\Services\UserService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function showProfile()
    {
        $users = User::all();

        $profile = $this->userService->showProfile();

        return view('role.users.pages.profile', compact('profile', 'users'));
    }


    public function editProfile()
    {
        $profile = $this->userService->editProfile();

        return view('role.users.pages.edit-profile', compact('profile'));
    }


    public function updateProfile(UserUpdateRequest $request)
    {
        $data = $request->validated();

        $this->userService->updateUser(
            auth()->user(),
            $data,         
        );

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

   
    public function deleteProfile(Request $request)
    {
        $user = $request->user();

        $this->userService->deleteProfile($user);

        return redirect()->route('login')->with('success', 'Your profile has been deleted successfully.');
    }
}
