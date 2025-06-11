<?php

namespace Tests\Unit;

use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroup;
use App\Models\User;
use App\Models\Group;
use Tests\TestCase;

class UserAddedToGroupEventTest extends TestCase
{
 

    public function test_it_sets_user_and_group_properties_correctly()
    {
        $user = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'johndoe@example.com']);
        $group = Group::factory()->create(['name' => 'Test Group']);

        $event = new UserAddedToGroup($user, $group);

        $this->assertInstanceOf(User::class, $event->user);
        $this->assertInstanceOf(Group::class, $event->group);
        $this->assertSame($user, $event->user); 
        $this->assertSame($group, $event->group);
        $this->assertEquals('John Doe', $event->user->first_name . ' ' . $event->user->last_name);
        $this->assertEquals('Test Group', $event->group->name);
    }
}