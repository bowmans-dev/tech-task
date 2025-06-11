<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\CalendarServices\CalendarService;
use App\Models\CalendarEventTeamMember;

class CalendarController extends Controller 
{
    private $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }


    public function saveCalendarEvent(Request $request)
    {
        $data = $request->only(['id', 'event_name', 'user_id', 'date', 'time', 'allDay', 'files', 'team_members']);

        return $this->calendarService->saveCalendarEvent($data);
    }


    public function deleteCalendarEvent(Request $request, $eventId)
    {
        return $this->calendarService->deleteCalendarEvent($request, $eventId);
    }


    public function removeTeamMember($eventId, $userId)
    {
        $deleted = CalendarEventTeamMember::where('calendar_event_id', $eventId)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }


    public function showCalendar()
    {
        return $this->calendarService->showCalendar();
    }


    public function getAllEvents(Request $request)
    {
        return $this->calendarService->getAllEvents($request);
    }
    
    
    public function getUserEvents()
    {
        $userId = auth('web')->id();

        return $this->calendarService->getUserEvents($userId);
    }

}