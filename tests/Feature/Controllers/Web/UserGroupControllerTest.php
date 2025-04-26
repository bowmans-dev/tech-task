<?php

namespace Tests\Feature\Controllers\Web;

use Tests\TestCase;
use Mockery;
use App\Domains\Shared\Services\GroupService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Group;

class UserGroupControllerTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    protected $mockedGroupService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockedGroupService = Mockery::mock(GroupService::class);
        $this->app->instance(GroupService::class, $this->mockedGroupService);
    }

    public function test_it_stores_a_user_to_group_relationship()
    {
        $user = User::factory()->create(); 
        $group = Group::factory()->create();
        $responsePayload = ['success' => true, 'message' => 'User added to group'];

        $this->mockedGroupService
            ->shouldReceive('addUserToGroup')
            ->once()
            ->with($user->id, $group->id)
            ->andReturn(new JsonResponse($responsePayload));

        $response = $this->postJson(route('user_groups.store'), [
            'type' => 'add-user',
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson($responsePayload);
    }

    
    public function test_it_creates_a_new_group()
    {
        $groupName = $this->faker->word();
        $responsePayload = ['success' => true, 'message' => 'Group created'];

        $this->mockedGroupService
            ->shouldReceive('createGroup')
            ->once()
            ->with($groupName)
            ->andReturn(new JsonResponse($responsePayload));

        $response = $this->postJson(route('groups.store'), [
            'type' => 'add-group',
            'name' => $groupName,
        ]);

        $response->assertStatus(200);
        $response->assertJson($responsePayload);
    }


    public function test_it_removes_a_user_from_a_group()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $responsePayload = ['success' => true, 'message' => 'User removed from group'];

        $this->mockedGroupService
            ->shouldReceive('removeUserFromGroup')
            ->once()
            ->with($user->id, $group->id)
            ->andReturn(new JsonResponse($responsePayload));

        $response = $this->postJson(route('user_groups.remove'), [
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson($responsePayload);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}