<?php

namespace App\Domains\Shared\Events\DomainEvents\Calendar;
use App\Models\Calendar;

class CalendarEntryCreatedEvent
{
    public $event;
    public $data;

    public function __construct(Calendar $event, array $data)
    {
        $this->event = $event;
        $this->data = $data;
    }
}