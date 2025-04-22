<?php

namespace Tests\Unit;

use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreatedEvent;
use App\Models\Group;
use Tests\TestCase;

class GroupCreatedEventTest extends TestCase
{

    public function test_it_sets_the_group_property_correctly()
    {
        // Arrange: Create a Group instance
        $group = new Group([
            'name' => 'Test Group',
        ]);

        // Act: Instantiate the GroupCreatedEvent with the Group
        $event = new GroupCreatedEvent($group);

        // Assert: Check if the event's group property matches the given Group instance
        $this->assertInstanceOf(Group::class, $event->group);
        $this->assertSame($group, $event->group); // Ensure the same object is passed
        $this->assertEquals('Test Group', $event->group->name); // Check specific properties
    }
}