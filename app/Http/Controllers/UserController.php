<?php

namespace App\Http\Controllers;

use App\Domains\Core\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }



    /**
     * Show the authenticated user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function showProfile()
    {
        $profile = $this->userService->showProfile();

        return view('profile', compact('profile'));
    }



    /**
     * Update the authenticated user's profile.
     *
     * @param \App\Http\Requests\UserUpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(UserUpdateRequest $request)
    {
        $data = $request->validated();

        $this->userService->updateUser(
            auth()->user(),
            $data,         
        );

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

   
    
    /**
     * Delete the authenticated user's profile.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProfile()
    {
        $this->userService->deleteProfile();

        return redirect()->route('login')->with('success', 'Your profile has been deleted successfully.');
    }
}
