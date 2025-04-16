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
        // Call the service to retrieve the logged-in user's profile
        $profile = $this->userService->showProfile();

        // Pass the profile to the view
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
        // Retrieve validated data from the form request
        $data = $request->validated();

        // Delegate the update logic to the user service
        $this->userService->updateUser(
            auth()->user(), // Current user
            $data,          // Validated input data
            'user',         // Scope
            auth()->id()    // Authenticated user ID
        );

        // Redirect back to the profile page with a success message
        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    
    /**
     * Delete the authenticated user's profile.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProfile()
    {
        // Call the service to delete the authenticated user's profile
        $this->userService->deleteProfile();

        // Redirect to the login page after logout and deletion
        return redirect()->route('login')->with('success', 'Your profile has been deleted successfully.');
    }
}
