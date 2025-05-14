<?php

namespace App\Domains\Shared\Services;

// use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryDeletedEvent;
use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryCreatedEvent;
use App\Domains\Shared\Events\DomainEventPublisher;

use App\Models\Calendar;
use App\Models\CalendarFile;
use App\Models\CalendarEventTeamMember;
use App\Models\Message;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Domains\Core\Services\UserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


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

    public function deleteCalendarEvent(Request $request, $eventId)
    {
        // Get the requester’s user id
        $currentUserId = $request->input('currentUserId');

        // Retrieve the event from the calendar table.
        $event = Calendar::find($eventId);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found.']);
        }

        // Check that the current user is the creator of the event.
        if ($event->user_id != $currentUserId) {
            return response()->json(['success' => false, 'message' => 'Only the creator can delete the event.']);
        }

        // Begin a transaction
        DB::beginTransaction();
        try {

            // Remove team member records
            CalendarEventTeamMember::where('calendar_event_id', $eventId)->delete();

            // Delete all messages for this event.
            Message::where('event_id', $eventId)->delete();

            // Check and delete the directory on the 'public' disk:
            $directory = 'events/' . $eventId;
            if (Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }

            // Delete file records from the database.
            CalendarFile::where('calendar_id', $eventId)->delete();

            // Permanently delete the event.
            Calendar::destroy($eventId);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error deleting event.'], 500);
        }
    }

    public function showCalendar()
    {
        $users = User::all();

        $groups = Group::with('users')->get();

        return view('role.admin.pages.calendar', compact('groups', 'users'));
    }

    public function getAllEvents(Request $request)
    {
        $events = Calendar::with(['files', 'user', 'teamMembers'])->get()->map(function ($event) {
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
                    'team_members' => $event->teamMembers->map(function ($user) {
                        return [
                            'userId' => $user->id,
                            'profilePicture' => $user->profile_picture ?? '/storage/default_profile_image.png',
                            'firstName' => $user->first_name,
                            'lastName' => $user->last_name,
                        ];
                    }),
                ],
            ];
        });

        return response()->json($events);
    }

    public function getUserEvents($userId)
    {
        // Fetch events where the user is either the creator (user_id in Calendar) or a team member (CalendarEventTeamMember)
        $events = Calendar::with(['files', 'user', 'teamMembers'])
            ->where('user_id', $userId) // Events created by the user
            ->orWhereHas('teamMembers', function ($query) use ($userId) {
                $query->where('user_id', $userId); // Events where the user is a team member
            })
            ->get()
            ->map(function ($event) {
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
                        'team_members' => $event->teamMembers->map(function ($user) {
                            return [
                                'userId' => $user->id,
                                'profilePicture' => $user->profile_picture ?? '/storage/default_profile_image.png',
                                'firstName' => $user->first_name,
                                'lastName' => $user->last_name,
                            ];
                        }),
                    ],
                ];
            });

        return response()->json($events);
    }
}