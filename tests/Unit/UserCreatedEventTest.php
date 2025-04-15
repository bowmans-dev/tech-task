<?php

namespace Tests\Unit;

use App\Domains\Core\Aggregates\UserAggregate;
use App\Domains\Shared\Events\UserCreatedEvent;
use Tests\TestCase;

class UserCreatedEventTest extends TestCase
{
    public function test_event_can_be_instantiated_with_user_aggregate()
    {
        // Given: A valid UserAggregate
        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => 'Female',
            'email' => 'jane.doe@example.com',
            'password' => 'securepassword',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
        ];
        $userAggregate = UserAggregate::create($userData);

        // When: Instantiating the UserCreatedEvent
        $event = new UserCreatedEvent($userAggregate);

        // Then: The UserAggregate should be stored in the event's public property
        $this->assertInstanceOf(UserAggregate::class, $event->userAggregate);
        $this->assertEquals($userAggregate, $event->userAggregate);
    }

    public function test_event_preserves_user_aggregate_data()
    {
        // Given: A valid UserAggregate
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'gender' => 'Female',
            'email' => 'john.smith@example.com',
            'password' => 'securepassword',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
        ];
        $userAggregate = UserAggregate::create($userData);

        // When: Instantiating the UserCreatedEvent
        $event = new UserCreatedEvent($userAggregate);

        // Then: The UserAggregate should match the original data
        $this->assertEquals($userData['first_name'], $event->userAggregate->getProcessedData()['first_name']);
        $this->assertEquals($userData['last_name'], $event->userAggregate->getProcessedData()['last_name']);
        $this->assertEquals($userData['email'], $event->userAggregate->getProcessedData()['email']);
    }
}
