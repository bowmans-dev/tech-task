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

    /**
     * Store the user-group relationship in the pivot table.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request type
        $validated = $request->validate([
            'type' => 'required|string|in:add-user,add-group',
        ]);

        if ($validated['type'] === 'add-user') {
            // Validate and delegate adding a user to a group
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'group_id' => 'required|exists:groups,id',
            ]);

            return $this->groupService->addUserToGroup($data['user_id'], $data['group_id']);
            
        } elseif ($validated['type'] === 'add-group') {
            // Validate and delegate creating a new group
            $data = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            return $this->groupService->createGroup($data['name']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request type.'], 400);
    }

    /**
     * Delete a group.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteGroup(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        // Delegate to the GroupService and return the response directly
        return $this->groupService->deleteGroup($validated['group_id']);
    }


    /**
     * Remove a user from a group.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function removeUser(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
        ]);

        return $this->groupService->removeUserFromGroup($validated['user_id'], $validated['group_id']);
    }
}