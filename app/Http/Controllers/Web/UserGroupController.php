<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Domains\Shared\Services\GroupService;

class UserGroupController extends Controller
{
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }


    public function createGroup(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return $this->groupService->createGroup($data['name']);
    }


    public function deleteGroup(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        return $this->groupService->deleteGroup($validated['group_id']);
    }


    public function addUserToGroup(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
        ]);

        return $this->groupService->addUserToGroup($data['user_id'], $data['group_id']);

    }


    public function removeUserFromGroup(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
        ]);

        return $this->groupService->removeUserFromGroup($validated['user_id'], $validated['group_id']);
    }
}