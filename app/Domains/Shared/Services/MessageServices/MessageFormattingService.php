<?php

namespace App\Domains\Shared\Services\MessageServices;

use App\Models\Admin;
use Carbon\Carbon;

class MessageFormattingService
{
    public function getDisplayName($sender)
    {
        return $sender instanceof Admin ? '(Admin) ' . $sender->name : $sender->first_name . ' ' . $sender->last_name;
    }


    public function getProfilePicture($sender)
    {
        return $sender->profile_picture ? asset('storage/' . $sender->profile_picture) : asset('storage/default_profile_image.webp');
    }


    public function groupMessagesByDate($messages)
    {
        $groupedMessages = [];

        foreach ($messages as $message) {
            $dateGroup = $this->formatMessageDate($message->created_at);
            $groupedMessages[$dateGroup][] = $message;
        }

        return $groupedMessages;
    }

    
    public function formatMessageDate($createdAt)
    {
        $date = Carbon::parse($createdAt);
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $lastWeekStart = $startOfWeek->copy()->subWeek();

        if ($date->isToday()) {
            return 'Today';
        } elseif ($date->isYesterday()) {
            return 'Yesterday';
        } elseif ($date->greaterThanOrEqualTo($lastWeekStart) && $date->lessThan($today)) {
            return $date->format('l'); // day of the week
        } else {
            return $date->format('d M Y'); // e.g. 12 May 2025
        }
    }
}