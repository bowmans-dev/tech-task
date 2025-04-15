<?php

namespace App\Domains\Core\ValueObjects;

use Illuminate\Support\Facades\Storage;

class ProfilePicture
{
    private ?string $path;

    /**
     * Constructor for ProfilePicture Value Object.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(?string $path)
    {
        // Allow null values for cases when no profile picture is provided
        if ($path !== null) {
            // Validate file format
            if (! preg_match('/\.(webp)$/i', $path)) {
                throw new \InvalidArgumentException('Profile picture must be in WebP format.');
            }
        }

        $this->path = $path;
    }

    /**
     * Get the URL of the profile picture.
     */
    public function getUrl(): string
    {
        return $this->path
            ? asset('storage/'.$this->path)
            : asset('storage/default_profile_image.png');
    }

    /**
     * Get the path of the profile picture.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
