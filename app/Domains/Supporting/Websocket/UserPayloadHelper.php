<?php

namespace App\Domains\Supporting\Websocket;

use App\Models\Admin;

class UserPayloadHelper
{
    public static function format($user): array
    {
        return [
            'id' => $user->id,
            'type' => class_basename(get_class($user)),
            'profile_picture' => self::getProfilePicture($user),
            ...self::getNameFields($user),
            'display_name' => self::getDisplayName($user),
        ];
    }

    private static function getProfilePicture($user): string
    {
        return $user->profile_picture
            ? "/storage/{$user->profile_picture}"
            : "/storage/default_profile_image.webp";
    }

    private static function getNameFields($user): array
    {
        return $user instanceof Admin
            ? ['name' => $user->name]
            : ['first_name' => $user->first_name, 'last_name' => $user->last_name];
    }

    private static function getDisplayName($user): string
    {
        return $user instanceof Admin
            ? $user->name
            : "{$user->first_name} {$user->last_name}";
    }
}