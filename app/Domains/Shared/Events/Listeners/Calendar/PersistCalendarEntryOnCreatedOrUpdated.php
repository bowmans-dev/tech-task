<?php

namespace App\Domains\Shared\Events\Listeners\Calendar;

use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryCreatedOrUpdated;
use App\Models\{CalendarEventTeamMember, CalendarFile};
use App\Domains\Shared\Services\CalendarServices\{CalendarEventHelper, CalendarFileHelper};

use Illuminate\Support\Facades\{Log, Storage};

class PersistCalendarEntryOnCreatedOrUpdated
{
    public function handle(CalendarEntryCreatedOrUpdated $calendarEvent)
    {

        $event = $calendarEvent->event;
        $data = $calendarEvent->data;

        CalendarEventHelper::processTeamMembers($data['team_members'] ?? null, $event->id);
        CalendarFileHelper::processFiles($data['files'] ?? [], $event->id, $data['user_id']);
    }
}