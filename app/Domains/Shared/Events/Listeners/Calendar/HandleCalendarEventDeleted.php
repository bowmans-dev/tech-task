<?php

namespace App\Domains\Shared\Events\Listeners\Calendar;

use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryDeleted;
use App\Domains\Shared\Services\CalendarServices\CalendarEventHelper;

class HandleCalendarEventDeleted
{
    public function handle(CalendarEntryDeleted $calendarEvent)
    {
        CalendarEventHelper::deleteEventData($calendarEvent->eventId, $calendarEvent->event);
    }
}

