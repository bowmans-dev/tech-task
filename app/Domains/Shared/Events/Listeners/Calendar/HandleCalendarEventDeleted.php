<?php

namespace App\Domains\Shared\Events\Listeners\Calendar;

use App\Domains\Shared\Events\DomainEvents\Calendar\CalendarEntryDeletedEvent;
use App\Models\CalendarFile;
use App\Models\CalendarEventTeamMember;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Calendar;

class HandleCalendarEventDeleted
{
    public function handle(CalendarEntryDeletedEvent $calendarEvent)
    {
        $event = $calendarEvent->event;
        $eventId = $calendarEvent->eventId;

        DB::beginTransaction();

        try {

            CalendarEventTeamMember::where('calendar_event_id', $eventId)->delete();
            Message::where('event_id', $eventId)->delete();

            $directory = 'events/' . $eventId;
            if (Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }

            CalendarFile::where('calendar_id', $eventId)->delete();

            $event->delete();

            DB::commit();

            $this->triggerWebSocketCleanup($eventId);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting event ID $eventId: " . $e->getMessage());
        }
    }

    private function triggerWebSocketCleanup($eventId)
    {
        try {
            $url = 'http://localhost:8080/internal';
            $payload = json_encode([
                'action' => 'message_broadcast',
                'user' => ['id' => '__SYSTEM__'],
                'event_id' => $eventId
            ]);

            $socket = stream_socket_client("tcp://localhost:8080", $errno, $errstr, 1);

            if (!$socket) {
                return;
            }

            fwrite($socket, "GET /internal HTTP/1.1\r\nHost: localhost\r\nUpgrade: websocket\r\nConnection: Upgrade\r\n\r\n");
            fwrite($socket, $payload);
            fclose($socket);

        } catch (\Exception $e) {
            Log::error("WebSocket cleanup failed for event ID $eventId: " . $e->getMessage());
        }
    }
}
