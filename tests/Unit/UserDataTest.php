<?php

namespace Tests\Unit;

use App\Models\User as UserModel;
use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\ValueObjects\Email;
use App\Domains\Core\ValueObjects\Password;
use App\Domains\Core\ValueObjects\Phone;
use App\Domains\Core\ValueObjects\Country;
use App\Domains\Core\ValueObjects\ProfilePicture;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserDataTest extends TestCase
{
    public function testFromArray()
    {
        // Define mock input data
        $data = [
            'id' => '123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'Male',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'phone' => '+1234567890',
            'country' => 'United States',
            'profile_picture' => 'profile.webp',
        ];

        // Create UserData object from array
        $userData = UserData::fromArray($data);

        // Assertions
        $this->assertEquals('123', $userData->id);
        $this->assertEquals('John', $userData->firstName);
        $this->assertEquals('Doe', $userData->lastName);
        $this->assertEquals('Male', $userData->gender);
        $this->assertInstanceOf(Email::class, $userData->email);
        $this->assertEquals('john.doe@example.com', $userData->email->getValue());
        $this->assertInstanceOf(Password::class, $userData->password);
        $this->assertInstanceOf(Phone::class, $userData->phone);
        $this->assertEquals('+1234567890', $userData->phone->getValue());
        $this->assertInstanceOf(Country::class, $userData->country);
        $this->assertEquals('United States', $userData->country->getValue());
        $this->assertInstanceOf(ProfilePicture::class, $userData->profilePicture);
    }

    public function testFromModel()
    {
        // Create a mock EloquentUserModel instance
        $userModel = UserModel::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'gender' => 'Female',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
            'password' => Hash::make('password123'),
            'profile_picture' => null,
        ]);

        // Create UserData object from model
        $userData = UserData::fromModel($userModel);

        // Assertions
        $this->assertEquals('Jane', $userData->firstName);
        $this->assertEquals('Doe', $userData->lastName);
        $this->assertEquals('Female', $userData->gender);
        $this->assertInstanceOf(Email::class, $userData->email);
        $this->assertEquals('jane.doe@example.com', $userData->email->getValue());
        $this->assertInstanceOf(Password::class, $userData->password);
        $this->assertInstanceOf(Phone::class, $userData->phone);
        $this->assertEquals('1234567890', $userData->phone->getValue());
        $this->assertInstanceOf(Country::class, $userData->country);
        $this->assertEquals('United Kingdom', $userData->country->getValue());
    }
}