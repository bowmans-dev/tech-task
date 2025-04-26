<?php

namespace App\Domains\Shared\Services;

// use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryDeletedEvent;
use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryCreatedEvent;
use App\Domains\Shared\Events\DomainEventPublisher;

use App\Models\Calendar;
use App\Models\CalendarFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Domains\Core\Services\UserService;
use Illuminate\Support\Facades\Log;

class CalendarService
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function saveCalendarEvent($data)
    {
        $event = Calendar::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'event_name' => $data['event_name'],
                'user_id' => $data['user_id'],
                'event_date' => $data['date'],
                'event_time' => $data['time'] ?? null,
                'all_day' => $data['allDay'],
            ]
        );


        DomainEventPublisher::publish(new CalendarEntryCreatedEvent($event,$data));

        return response()->json(['message' => 'Event saved successfully!', 'event' => $event], 200);
    }

    public function showCalendar()
    {

        $groups = Group::with('users')->get();

        return view('pages.calendar.calendar', compact('groups'));
    }

    public function getAllEvents(Request $request)
    {
        $events = Calendar::with(['files', 'user'])->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->event_name,
                'start' => $event->all_day
                    ? $event->event_date
                    : $event->event_date . 'T' . $event->event_time,
                'allDay' => $event->all_day,
                'extendedProps' => [
                    'user_id' => $event->user_id,
                    'files' => $event->files,
                    'time' => $event->event_time,
                    'user' => [
                        'first_name' => $event->user->first_name,
                        'last_name' => $event->user->last_name,
                        'profile_picture' => $event->user->profile_picture,
                    ],
                ],
            ];
        });

        return response()->json($events);
    }
}