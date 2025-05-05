<?php

namespace App\Domains\Shared\Events\Listeners\Calendar;

use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryCreatedEvent;
use App\Models\Calendar;
use App\Models\CalendarFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\CalendarEventTeamMember;

class PersistCalendarEntryOnCreated
{
    public function handle(CalendarEntryCreatedEvent $calendarEvent)
    {

        $event = $calendarEvent->event;
        $data = $calendarEvent->data;

        Log::info('Event Data Received:', $data);

        Log::info('Calendar Event Created:', $event->toArray());

        // Decode team_members if it's a JSON string
        if (isset($data['team_members']) && is_string($data['team_members'])) {
            $data['team_members'] = json_decode($data['team_members'], true);
        }

        // Handle the team members
        if (isset($data['team_members']) && is_array($data['team_members'])) {
            foreach ($data['team_members'] as $teamMember) {
                $userId = $teamMember['userId'] ?? null;

                if ($userId) {
                    Log::info('Adding user to calendar event:', ['event_id' => $event->id, 'user_id' => $userId]);

                    // Use Eloquent to insert into the pivot table
                    CalendarEventTeamMember::create([
                        'calendar_event_id' => $event->id,
                        'user_id' => $userId,
                    ]);

                } else {
                    Log::warning('Invalid team member data:', $teamMember);
                }
            }
        } else {
            Log::warning('No team members provided for calendar event:', ['event_id' => $event->id]);
        }


        if (isset($data['files']) && is_array($data['files'])) {
            foreach ($data['files'] as $file) {
                if (!$file->isValid()) {
                    Log::error('Invalid file upload:', ['error' => $file->getError()]);
                    continue;
                }
        
                Log::info('File received:', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
        
                $filePath = 'events/' . $event->id . '/' . $data['user_id'] . '/' . $file->getClientOriginalName();
        
                $path = $file->storeAs('', $filePath, 'public');
        
                CalendarFile::create([
                    'calendar_id' => $event->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'uploaded_at' => now(),
                ]);
            }
        }

    }
}