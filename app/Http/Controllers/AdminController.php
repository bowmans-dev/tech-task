<?php

namespace App\Http\Controllers;

use App\Domains\Core\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;


class AdminController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
 

    public function showCreateUserForm()
    {
        return view('user.create');
    }

    /**
     * Create a new user (admin action).
     *
     * @param \App\Http\Requests\UserStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();

        $this->userService->createUser($data);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }



    /**
     * Update a user's details.
     *
     * @param \App\Http\Requests\UserUpdateRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->validated();

        $this->userService->updateUser($user, $data);

        return redirect()->route('users.show', $user->id)->with('success', 'User updated successfully!');
    }



    /**
     * Show the details of a specific user.
     *
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        return view('user.manage', compact('user'));
    }



    /**
     * Display a paginated list of all users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    { 
        $users = $this->userService->listUsers();

        return view('user.index', compact('users'));
    }



    /**
     * Filter users.
     *
     * @return \Illuminate\View\View
     */
    public function filter(Request $request)
    {
        $users = $this->userService->filterUsers($request->query('search'));

        return view('Components.user-list', compact('users'));
    }

    

    /**
     * Delete a user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
    
}