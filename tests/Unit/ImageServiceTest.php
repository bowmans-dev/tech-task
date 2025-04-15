<?php

namespace Tests\Unit;

use App\Domains\Supporting\ImageUpload\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    private ImageService $imageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageService = new ImageService;

        // Ensure the actual storage directory exists for testing
        if (! is_dir(storage_path('app/public/profile_pictures'))) {
            mkdir(storage_path('app/public/profile_pictures'), 0755, true);
        }
    }

    public function test_upload_valid_image()
    {
        // Arrange: Simulate an image file upload
        $imageFile = UploadedFile::fake()->image('test_image.png');

        // Act: Upload the image
        $filePath = $this->imageService->upload($imageFile);

        // Assert: Verify the image was saved to the correct path
        $fullPath = storage_path('app/public/'.$filePath);
        $this->assertFileExists($fullPath);

        // Assert the file has the correct format
        $this->assertStringEndsWith('.webp', $filePath);

        // Cleanup: Delete the file after the test
        unlink($fullPath);
    }

    public function test_upload_invalid_image_throws_exception()
    {
        // Arrange: Simulate an invalid file
        $imageFile = UploadedFile::fake()->create('test_file.txt', 100, 'text/plain');

        // Expect an exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid image file.');

        // Act: Try to upload the invalid file
        $this->imageService->upload($imageFile);
    }

    public function test_delete_image()
    {
        // Arrange: Create a dummy image file
        $filePath = 'profile_pictures/test_image.webp';
        $fullPath = storage_path('app/public/'.$filePath);
        file_put_contents($fullPath, 'dummy content'); // Create a dummy file

        // Assert the file exists before deletion
        $this->assertFileExists($fullPath);

        // Act: Delete the file
        $this->imageService->delete($filePath);

        // Assert the file no longer exists
        $this->assertFileDoesNotExist($fullPath);
    }
}
