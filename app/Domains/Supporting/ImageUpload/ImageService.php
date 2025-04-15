<?php

namespace App\Domains\Supporting\ImageUpload;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ImageService
{
    public function upload($imageFile): string
    {
        try {
            // Ensure the file is valid
            if ($imageFile->isValid()) {
                // Initialize the Image Manager
                $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver);

                // Read the image file
                $image = $manager->read($imageFile->getPathname());

                // Generate a unique name for the image
                $imageName = uniqid().'.webp';

                // Encode to WebP and save to the public disk
                $path = 'profile_pictures/'.$imageName;
                $image->toWebp()->save(storage_path('app/public/profile_pictures/'.$imageName));

                // Return the relative path to the image
                return $path;
            }

        } catch (\Exception $e) {
            throw new \Exception('Invalid image file.'); // Consistent exception message
        }
    }

    public function delete($profilePicturePath): void
    {
        Storage::disk('public')->delete($profilePicturePath);
    }
}
