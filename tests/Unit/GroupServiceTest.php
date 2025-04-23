<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Group;
use App\Domains\Shared\Services\GroupService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GroupServiceTest extends TestCase
{
    use DatabaseTransactions;

    private GroupService $groupService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupService = app(GroupService::class);
    }

    public function testAddUserToGroup()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $response = $this->groupService->addUserToGroup($user->id, $group->id);

        $this->assertDatabaseHas('user_groups', [
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]); 

        $this->assertStringContainsString('turbo-stream', $response->getContent());
    }

    public function testCreateGroup()
    {
        $groupName = 'New Group';

        $response = $this->groupService->createGroup($groupName);

        // Assertions
        $this->assertDatabaseHas('groups', ['name' => $groupName]); 
        $this->assertStringContainsString('turbo-stream', $response->getContent());
        $this->assertJson($response->getContent());
    }

    public function testDeleteGroup()
    {
        $group = Group::factory()->create();

        $response = $this->groupService->deleteGroup($group->id);

        $this->assertDatabaseMissing('groups', ['id' => $group->id]);

        $content = $response->toHtml(); 
        $this->assertStringContainsString('groups-accordion', $content);
    }

    public function testRemoveUserFromGroup()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($user);

        $response = $this->groupService->removeUserFromGroup($user->id, $group->id);

        $this->assertDatabaseMissing('user_groups', [
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);

        $content = $response->toHtml();
        $this->assertStringContainsString('turbo-stream', $content);
        $this->assertStringContainsString('groups-accordion', $content);
    }
}