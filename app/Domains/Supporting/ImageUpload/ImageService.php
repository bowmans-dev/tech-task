<?php

namespace App\Domains\Supporting\ImageUpload;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ImageService
{
    public function upload($imageFile): string
    {
        try {

            if ($imageFile->isValid()) {

                $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver);

                $image = $manager->read($imageFile->getPathname());

                $imageName = uniqid().'.webp';

                $path = 'profile_pictures/'.$imageName;
                $image->toWebp()->save(storage_path('app/public/profile_pictures/'.$imageName));

                return $path;
            }

        } catch (\Exception $e) {
            throw new \Exception('Invalid image file.');
        }
    }

    public function delete($profilePicturePath): void
    {
        Storage::disk('public')->delete($profilePicturePath);
    }
}
