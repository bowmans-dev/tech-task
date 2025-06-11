<?php

namespace App\Domains\Shared\Services\CalendarServices;

use App\Domains\Core\Services\UserService;
use App\Domains\Shared\Events\DomainEventPublisher;
use App\Domains\Shared\Events\DomainEvents\Calendar\{CalendarEntryCreatedOrUpdated, CalendarEntryDeleted};
use App\Models\{Calendar, User, Group};
use App\Domains\Shared\DTOs\EventDTO;
use Illuminate\Http\Request;

class CalendarService
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function saveCalendarEvent($data)
    {
        
        $event = !empty($data['id']) ? Calendar::findOrFail($data['id']) : null;
        
        if (auth('web')->id()) {
            $data['user_id'] = $event ? $event->user_id : auth()->id();
        }

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

        DomainEventPublisher::publish(new CalendarEntryCreatedOrUpdated($event,$data));

        return response()->json(['message' => 'Event saved successfully!', 'event' => $event], 200);
    }
    

    public function deleteCalendarEvent(Request $request, $eventId)
    {
        $currentUserId = $request->input('currentUserId');

        $event = Calendar::find($eventId);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found.']);
        }

        if ($event->user_id != $currentUserId) {
            return response()->json(['success' => false, 'message' => 'Only the creator can delete the event.']);
        }

        DomainEventPublisher::publish(new CalendarEntryDeleted($event, $eventId));

        return response()->json(['success' => true, 'message' => 'Event deletion triggered.']);
    }


    public function showCalendar()
    {
        $users = User::all();

        $groups = Group::with('users')->get();

        return view('role.admin.pages.calendar', compact('groups', 'users'));
    }


    public function getAllEvents(Request $request)
    {
        $events = Calendar::with(['files', 'user', 'teamMembers'])
            ->get()
            ->map(fn($event) => EventDTO::fromCalendar($event));

        return response()->json($events);
    }

    
    public function getUserEvents($userId)
    {
        $events = Calendar::with(['files', 'user', 'teamMembers'])
            ->where('user_id', $userId)
            ->orWhereHas('teamMembers', fn($query) => $query->where('user_id', $userId))
            ->get()
            ->map(fn($event) => EventDTO::fromCalendar($event));

        return response()->json($events);
    }
}