<?php

namespace App\Domains\Shared\Services\CalendarServices;

use App\Models\{CalendarEventTeamMember, Message, CalendarFile};
use Illuminate\Support\Facades\{Log, Storage, DB};

class CalendarEventHelper
{
    public static function processTeamMembers(array|string|null $teamMembers, int $eventId): void
    {
        if (is_string($teamMembers)) {
            $teamMembers = json_decode($teamMembers, true);
        }

        foreach ($teamMembers ?? [] as $member) {
            if ($userId = $member['userId'] ?? null) {
                CalendarEventTeamMember::create([
                    'calendar_event_id' => $eventId,
                    'user_id' => $userId,
                ]);
            } else {
                Log::warning('Invalid team member data:', $member);
            }
        }
    }

    
    public static function deleteEventData(int $eventId, $event): void
    {
        DB::beginTransaction();
        try {
            CalendarEventTeamMember::where('calendar_event_id', $eventId)->delete();
            Message::where('event_id', $eventId)->delete();

            $directory = "events/{$eventId}";
            if (Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }

            CalendarFile::where('calendar_id', $eventId)->delete();
            $event->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting event ID $eventId: " . $e->getMessage());
        }
    }
}