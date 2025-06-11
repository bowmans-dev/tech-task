<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domains\Core\Services\UserService;
use App\Models\User;
use App\Models\Group;
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
        return view('role.admin.pages.create');
    }


    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();

        $this->userService->createUser($data);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }


    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->validated();

        $this->userService->updateUser($user, $data);

        return redirect()->route('users.show', $user->id)->with('success', 'User updated successfully!');
    }


    public function show(User $user)
    {
        return view('role.admin.pages.manage', compact('user'));
    }


    public function index()
    {
        $users = $this->userService->listUsers(); // Fetch paginated users
        
        $groups = Group::with('users')->get(); // Fetch all groups with their users

        // Include the groups for each user
        foreach ($users as $user) {
            $user->groupIds = $user->groups->pluck('id')->toArray(); // Get IDs of groups the user belongs to
        }

        return view('role.admin.pages.index', compact('users', 'groups')); // Pass both to the view
    }


    public function filter(Request $request)
    {
        $users = $this->userService->filterUsers($request->query('search'));

        $groups = Group::with('users')->get(); // Fetch all groups with their users

        // Include the groups for each user
        foreach ($users as $user) {
            $user->groupIds = $user->groups->pluck('id')->toArray(); // Get IDs of groups the user belongs to
        }

        return view('Components.List.user-list', compact('users', 'groups'));
    }


    public function filterModal(Request $request)
    {
        $users = $this->userService->filterUsers($request->query('search'));

        $groups = Group::with('users')->get(); // Fetch all groups with their users

        // Include the groups for each user
        foreach ($users as $user) {
            $user->groupIds = $user->groups->pluck('id')->toArray(); // Get IDs of groups the user belongs to
        }

        return view('Components.List.user-list-modal', compact('users', 'groups'));
    }

    
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
    
}