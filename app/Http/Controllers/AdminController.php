<?php

namespace App\Http\Controllers;

use App\Domains\Core\Services\UserService;
// use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Create a new user (admin action).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {

        $this->userService->createUser($request->all(), 'admin');

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    /**
     * Delete a user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {

        $this->userService->deleteUser($user, 'admin');

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
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
     * Show the details of a specific user.
     *
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        return view('user.manage', compact('user'));
    }

    /**
     * Update a user's details.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {

        $this->userService->updateUser($user, $request->all(), 'admin');

        return redirect()->route('users.show', $user->id)->with('success', 'User updated successfully!');
    }
}
