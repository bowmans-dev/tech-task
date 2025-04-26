<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\CalendarService;

class CalendarController extends Controller 
{
    private $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function saveCalendarEvent(Request $request)
    {
        $data = $request->only(['id', 'event_name', 'user_id', 'date', 'time', 'allDay', 'files']);

        return $this->calendarService->saveCalendarEvent($data);
    }

    public function showCalendar()
    {
        return $this->calendarService->showCalendar();
    }


    public function getAllEvents(Request $request)
    {
        return $this->calendarService->getAllEvents($request);
    }

}