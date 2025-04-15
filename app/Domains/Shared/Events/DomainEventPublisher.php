<?php

namespace App\Domains\Shared\Events;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class DomainEventPublisher
{
    private static array $subscribers = []; // Event subscribers

    /**
     * Subscribe to an event class.
     *
     * @param  string  $eventClass  Event class name
     * @param  callable  $callback  Callback function
     */
    public static function subscribe(string $eventClass, callable $callback): void
    {
        self::$subscribers[$eventClass][] = $callback;
    }

    /**
     * Publish an event.
     *
     * @param  object  $event  The event object
     */
    public static function publish(object $event): void
    {
        $eventClass = get_class($event);
        $eventId = spl_object_id($event); // Get unique ID for the event object

        Log::info('Publishing event', [
            'eventClass' => $eventClass,
        ]);

        // Publish via Laravel's event dispatcher
        Event::dispatch($event);

        // Invoke custom subscribers
        if (isset(self::$subscribers[$eventClass])) {
            foreach (self::$subscribers[$eventClass] as $subscriber) {
                $subscriber($event);
            }
        }
    }
}
