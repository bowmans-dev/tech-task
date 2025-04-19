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



    public function uploadProfilePicture(array &$data): void
    {
        if (isset($data['profile_picture'])) {
            $data['profile_picture'] = $this->upload($data['profile_picture']);
        }
    }



    public function replaceProfilePicture(array &$data, ?string $currentPicture): void
    {
        if (isset($data['profile_picture'])) {
            if ($currentPicture && Storage::disk('public')->exists($currentPicture)) {
                $this->delete($currentPicture);
            }
            $data['profile_picture'] = $this->upload($data['profile_picture']);
        }
    }



    public function deleteProfilePicture(?string $profilePicture): void
    {
        if ($profilePicture && Storage::disk('public')->exists($profilePicture)) {
            $this->delete($profilePicture);
        }
    }
}