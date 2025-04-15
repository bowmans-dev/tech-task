<?php

namespace Tests\Unit;

use App\Domains\Core\Aggregates\UserAggregate;
use App\Domains\Shared\Events\UserUpdatedEvent;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\ProfilePicture;
use Tests\TestCase;

class UserUpdatedEventTest extends TestCase
{
    public function test_event_can_be_instantiated_with_user_aggregate_and_updated_data()
    {
        // Given: A valid UserAggregate and updated data
        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => 'Female',
            'email' => 'jane.doe@example.com',
            'password' => 'securepassword',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
        ];
        $updatedData = [
            'first_name' => 'Janet',
            'last_name' => 'Doe-Smith',
            'email' => 'janet.doe@example.com',
            'password' => 'newsecurepassword',
            'phone' => '9876543210',
            'country' => 'United States',
            'gender' => 'Female',
        ];

        $userAggregate = UserAggregate::create($userData);

        // When: Instantiating the UserUpdatedEvent
        $event = new UserUpdatedEvent($userAggregate, $updatedData);

        // Then: The UserAggregate and updated data should be stored in the event
        $this->assertInstanceOf(UserAggregate::class, $event->getUserAggregate());
        $this->assertEquals($userAggregate, $event->getUserAggregate());
        $this->assertEquals($updatedData, $event->getUpdatedData());
    }

    public function test_event_preserves_user_aggregate_and_updates_fields()
    {
        // Given: A valid UserAggregate and updated data
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'gender' => 'Male',
            'email' => 'john.smith@example.com',
            'password' => 'securepassword',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
        ];
        $updatedData = [
            'first_name' => 'Jonathan',
            'last_name' => 'Smith-Jones',
            'gender' => 'Male',
            'email' => 'jonathan.smith@example.com',
            'password' => 'strongerpassword',
            'phone' => '0987654321',
            'country' => 'Canada',
        ];

        $userAggregate = UserAggregate::create($userData);

        // When: Instantiating the UserUpdatedEvent
        $event = new UserUpdatedEvent($userAggregate, $updatedData);

        // Then: The UserAggregate and updated data should reflect the new values
        $this->assertEquals($updatedData['first_name'], $event->getUpdatedData()['first_name']);
        $this->assertEquals($updatedData['last_name'], $event->getUpdatedData()['last_name']);
        $this->assertEquals($updatedData['email'], $event->getUpdatedData()['email']);
        $this->assertEquals($updatedData['password'], $event->getUpdatedData()['password']);
        $this->assertEquals($updatedData['phone'], $event->getUpdatedData()['phone']);
        $this->assertEquals($updatedData['country'], $event->getUpdatedData()['country']);
        $this->assertEquals($updatedData['gender'], $event->getUpdatedData()['gender']);
    }
}