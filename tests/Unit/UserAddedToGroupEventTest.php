<?php

namespace Tests\Unit;

use App\Domains\Shared\Events\DomainEvents\Groups\UserAddedToGroupEvent;
use App\Models\User;
use App\Models\Group;
use Tests\TestCase;

class UserAddedToGroupEventTest extends TestCase
{
 

public function test_it_sets_user_and_group_properties_correctly()
{
    // Arrange: Use factories to create a User and Group instance
    $user = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'johndoe@example.com']);
    $group = Group::factory()->create(['name' => 'Test Group']);

    // Act: Instantiate the UserAddedToGroupEvent with the User and Group
    $event = new UserAddedToGroupEvent($user, $group);

    // Assert: Check if the event's properties match the given User and Group instances
    $this->assertInstanceOf(User::class, $event->user);
    $this->assertInstanceOf(Group::class, $event->group);
    $this->assertSame($user, $event->user); // Ensure the exact User object is passed
    $this->assertSame($group, $event->group); // Ensure the exact Group object is passed
    $this->assertEquals('John Doe', $event->user->first_name . ' ' . $event->user->last_name); // Verify the User's name
    $this->assertEquals('Test Group', $event->group->name); // Verify the Group's name
}
}