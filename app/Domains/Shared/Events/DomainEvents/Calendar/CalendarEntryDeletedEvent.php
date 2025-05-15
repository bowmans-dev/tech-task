<?php

namespace App\Domains\Shared\Events\DomainEvents\Calendar;

use App\Models\Calendar;

class CalendarEntryDeletedEvent
{
    public $event;
    public $eventId;

    public function __construct(Calendar $event, $eventId)
    {
        $this->event = $event;
        $this->eventId = $eventId;
    }
}
