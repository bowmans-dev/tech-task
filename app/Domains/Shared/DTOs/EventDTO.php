<?php

namespace App\Domains\Shared\DTOs;

use App\Models\Calendar;

class EventDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $start,
        public bool $allDay,
        public array $extendedProps
    ) {}

    
    public static function fromCalendar(Calendar $event): self
    {
        return new self(
            id: $event->id,
            title: $event->event_name,
            start: $event->all_day ? $event->event_date : "{$event->event_date}T{$event->event_time}",
            allDay: $event->all_day,
            extendedProps: [
                'files' => $event->files,
                'time' => $event->event_time,
                'eventOwnerDetails' => [
                    'eventOwnerId' => $event->user_id,
                    'firstName' => $event->user->first_name,
                    'lastName' => $event->user->last_name,
                    'profilePicture' => $event->user->profile_picture,
                ],
                'team_members' => $event->teamMembers->map(fn($user) => [
                    'userId' => $user->id,
                    'profilePicture' => $user->profile_picture ?? '/storage/default_profile_image.webp',
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                ]),
            ],
        );
    }
}