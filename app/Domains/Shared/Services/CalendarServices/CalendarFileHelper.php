<?php

namespace App\Domains\Shared\Services\CalendarServices;

use App\Models\CalendarFile;
use Illuminate\Support\Facades\{Log, Storage};

class CalendarFileHelper
{
    public static function processFiles(array $files, int $eventId, int $userId): void
    {
        foreach ($files as $file) {
            if (!$file->isValid()) {
                Log::error('Invalid file upload:', ['error' => $file->getError()]);
                continue;
            }

            $filePath = "events/{$eventId}/{$userId}/{$file->getClientOriginalName()}";
            $storedPath = $file->storeAs('', $filePath, 'public');

            CalendarFile::create([
                'calendar_id' => $eventId,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'uploaded_at' => now(),
            ]);
        }
    }
}