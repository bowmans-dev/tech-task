<?php

namespace Tests\Unit;

use App\Domains\Core\Aggregates\UserAggregate;
use App\Domains\Core\Entities\User;
use PHPUnit\Framework\TestCase;

class UserAggregateTest extends TestCase
{
    public function testCreate(): void
    {
        // Input data for creating the user
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'gender' => 'Male',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Use UserAggregate::create to create the user
        $userAggregate = UserAggregate::create($userData);

        // Assert that the created object is an instance of UserAggregate
        $this->assertInstanceOf(UserAggregate::class, $userAggregate);

        // Assert that the processed data matches the input data
        $processedData = $userAggregate->getProcessedData();
        $this->assertEquals('john.doe@example.com', $processedData['email']);
    }

    public function testUpdate(): void
    {
        // Input data for creating the user
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'gender' => 'Male',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Create the aggregate
        $userAggregate = UserAggregate::create($userData);

        // Update the user's data
        $updateData = [
            'email' => 'john.updated@example.com',
        ];
        $userAggregate->update($updateData);

        // Assert that the processed data contains the updated email
        $processedData = $userAggregate->getProcessedData();
        $this->assertEquals('john.updated@example.com', $processedData['email']);
    }

    public function testGetProcessedData(): void
    {
        // Input data for creating the user, including a plain-text password
        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'gender' => 'Female',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
            'password' => 'plainpassword123', // Plain password
            'profile_picture' => null,
        ];
    
        // Create the aggregate
        $userAggregate = UserAggregate::create($userData);
    
        // Get processed data
        $processedData = $userAggregate->getProcessedData();
    
        // Assert that the processed data contains specific fields
        $this->assertEquals($userData['first_name'], $processedData['first_name']);
        $this->assertEquals($userData['last_name'], $processedData['last_name']);
        $this->assertEquals($userData['email'], $processedData['email']);
        $this->assertEquals($userData['gender'], $processedData['gender']);
        $this->assertEquals($userData['phone'], $processedData['phone']);
        $this->assertEquals($userData['country'], $processedData['country']);
        $this->assertEquals($userData['profile_picture'], $processedData['profile_picture']);
    
        // Assert that the password is hashed
        $this->assertNotEquals($userData['password'], $processedData['password']); // Should not be the plain password
        $this->assertTrue(password_verify('plainpassword123', $processedData['password'])); // Verify it matches the hashed password
    }
}