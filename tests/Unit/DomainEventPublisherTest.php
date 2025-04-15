<?php

namespace Tests\Unit;

use App\Domains\Shared\Events\DomainEventPublisher;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

// Define TestEvent outside the DomainEventPublisherTest class
class TestEventDispatch
{
    public string $property = 'Test Event Dispatch';
}
class TestEventUserCreated
{
    public string $property = 'Test Event User Created';
}

class TestIgnoresUnsubscribedEvent
{
    public string $property = 'Test Ignores Unsubscribed Events';
}

class DomainEventPublisherTest extends TestCase
{
    public function test_publish_method_dispatches_domain_event_successfully()
    {
        // Arrange
        $mockEvent = new TestEventDispatch;

        // Fake Laravel's Event facade
        Event::fake();

        // Act: Publish the event
        DomainEventPublisher::publish($mockEvent);

        // Assert: Event was dispatched via Laravel's Event dispatcher
        Event::assertDispatched(TestEventDispatch::class, function ($event) use ($mockEvent) {
            return $event === $mockEvent;
        });
    }

    public function test_subscriber_is_invoked_when_event_is_published()
    {
        // Arrange
        $mockEvent = new TestEventUserCreated;

        $callbackWasCalled = false;

        // Subscribe to the event
        DomainEventPublisher::subscribe(TestEventUserCreated::class, function ($event) use (&$callbackWasCalled, $mockEvent) {
            $callbackWasCalled = $event === $mockEvent;
        });

        // Act: Publish the event
        DomainEventPublisher::publish($mockEvent);

        // Assert: Subscriber was invoked
        $this->assertTrue($callbackWasCalled, 'The subscriber callback should have been invoked with the event.');
    }

    public function test_subscriber_is_not_invoked_for_unsubscribed_event()
    {
        // Arrange
        $mockEvent = new TestIgnoresUnsubscribedEvent;

        $callbackWasCalled = false;

        // Subscribe to a different event class
        DomainEventPublisher::subscribe(TestEventDispatch::class, function () use (&$callbackWasCalled) {
            $callbackWasCalled = true;
        });

        // Act: Publish the event
        DomainEventPublisher::publish($mockEvent);

        // Assert: Subscriber was not invoked
        $this->assertFalse($callbackWasCalled, 'The subscriber callback should not have been invoked for an unsubscribed event.');
    }
}
