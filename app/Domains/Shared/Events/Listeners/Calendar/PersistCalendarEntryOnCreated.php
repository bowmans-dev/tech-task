<?php

namespace App\Domains\Shared\Events\Listeners\Calendar;

use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryCreatedEvent;
use App\Models\Calendar;
use App\Models\CalendarFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PersistCalendarEntryOnCreated
{
    public function handle(CalendarEntryCreatedEvent $calendarEvent)
    {

        $event = $calendarEvent->event;
        $data = $calendarEvent->data;

        Log::info('Calendar Event Created:', $event->toArray());

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