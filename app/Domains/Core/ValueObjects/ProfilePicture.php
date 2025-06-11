<?php

namespace App\Domains\Core\ValueObjects;

use Illuminate\Support\Facades\Storage;

class ProfilePicture
{
    private ?string $path;

    public function __construct(?string $path)
    {

        if ($path !== null) {
            // Validate file format is webp
            if (! preg_match('/\.webp$/i', $path)) {
                throw new \InvalidArgumentException('Profile picture must be in WebP format.');
            }
        }

        $this->path = $path;
    }


    public function getUrl(): string
    {
        return $this->path
            ? asset('storage/'.$this->path)
            : asset('storage/default_profile_image.webp');
    }


    public function getPath(): ?string
    {
        return $this->path;
    }
}
