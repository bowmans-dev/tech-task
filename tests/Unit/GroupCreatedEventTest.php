<?php

namespace Tests\Unit;

use App\Domains\Shared\Events\DomainEvents\Groups\GroupCreatedEvent;
use App\Models\Group;
use Tests\TestCase;

class GroupCreatedEventTest extends TestCase
{

    public function test_it_sets_the_group_property_correctly()
    {
        $group = new Group([
            'name' => 'Test Group',
        ]);

        $event = new GroupCreatedEvent($group);

        $this->assertInstanceOf(Group::class, $event->group);
        $this->assertSame($group, $event->group);
        $this->assertEquals('Test Group', $event->group->name);
    }
}