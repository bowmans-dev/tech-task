<?php

namespace App\Domains\Shared\Events;

use Illuminate\Support\Facades\{Log, Event};

class DomainEventPublisher
{
    private static array $subscribers = [];

    public static function subscribe(string $eventClass, callable $callback): void
    {
        self::$subscribers[$eventClass][] = $callback;
    }


    public static function publish(object $event): void
    {
        $eventClass = get_class($event);

        Log::info('Publishing event', [
            'eventClass' => $eventClass,
        ]);

        Event::dispatch($event);

        // Invoke custom subscribers
        if (isset(self::$subscribers[$eventClass])) {
            foreach (self::$subscribers[$eventClass] as $subscriber) {
                $subscriber($event);
            }
        }
    }
}
