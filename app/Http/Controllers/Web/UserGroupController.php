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
     * Create a group
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function createGroup(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return $this->groupService->createGroup($data['name']);
    }

    /**
     * Delete a group.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
     * Add user to a group.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function addUserToGroup(Request $request)
    {
        // Validate the request
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
        ]);

        // Delegate to the GroupService and return the response directly
        return $this->groupService->addUserToGroup($data['user_id'], $data['group_id']);

    }


    /**
     * Remove a user from a group.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function removeUserFromGroup(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
        ]);

        return $this->groupService->removeUserFromGroup($validated['user_id'], $validated['group_id']);
    }
}