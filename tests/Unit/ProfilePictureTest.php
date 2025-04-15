<?php

namespace Tests\Unit;

use App\Domains\Core\ValueObjects\ProfilePicture;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePictureTest extends TestCase
{
    public function test_valid_profile_picture_path_is_accepted()
    {
        // Mock the Storage facade
        Storage::shouldReceive('disk->exists')
            ->with('profile_pictures/sample.webp')
            ->andReturn(true);

        // Given: A valid profile picture path
        $validPath = 'profile_pictures/sample.webp';

        // When: Creating the ProfilePicture value object
        $profilePicture = new ProfilePicture($validPath);

        // Then: The path should be set correctly
        $this->assertEquals($validPath, $profilePicture->getPath());
    }

    public function test_non_webp_format_throws_exception()
    {
        // Given: An invalid profile picture format
        $invalidPath = 'profile_pictures/sample.jpg';

        // Expect: An InvalidArgumentException to be thrown
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Profile picture must be in WebP format.');

        // When: Creating the ProfilePicture value object
        new ProfilePicture($invalidPath);
    }

    public function test_null_profile_picture_is_accepted()
    {
        // Given: A null profile picture path
        $nullablePath = null;

        // When: Creating the ProfilePicture value object
        $profilePicture = new ProfilePicture($nullablePath);

        // Then: The path should be null
        $this->assertNull($profilePicture->getPath());
    }

    public function test_get_url_returns_correct_asset_url_for_valid_profile_picture()
    {
        // Mock the Storage facade
        Storage::shouldReceive('disk->exists')
            ->with('profile_pictures/sample.webp')
            ->andReturn(true);

        // Given: A valid profile picture path
        $validPath = 'profile_pictures/sample.webp';

        // When: Creating the ProfilePicture value object
        $profilePicture = new ProfilePicture($validPath);

        // Then: The URL should point to the stored asset
        $this->assertEquals(asset('storage/'.$validPath), $profilePicture->getUrl());
    }

    public function test_get_url_returns_default_asset_url_for_null_profile_picture()
    {
        // Given: A null profile picture path
        $nullablePath = null;

        // When: Creating the ProfilePicture value object
        $profilePicture = new ProfilePicture($nullablePath);

        // Then: The URL should point to the default profile picture
        $this->assertEquals(asset('storage/default_profile_image.png'), $profilePicture->getUrl());
    }
}
