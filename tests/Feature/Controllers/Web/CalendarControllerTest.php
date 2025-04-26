<?php

namespace Tests\Feature\Controllers\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CalendarControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_can_save_a_calendar_event_with_file_upload()
    {
        // Ensure the test uses the actual storage path
        Storage::disk('public')->put(
            'default_profile_image.png',
            file_get_contents(storage_path('app/public/default_profile_image.png'))
        );

        // Create a test user
        $user = User::factory()->create();

        // Prepare request data
        $requestData = [
            'id' => null, // New event
            'event_name' => 'Test Event',
            'user_id' => $user->id,
            'date' => '2025-05-01',
            'time' => '14:00',
            'allDay' => false,
        ];

        // Add the actual file to the request
        $file = new UploadedFile(
            storage_path('app/public/default_profile_image.png'),
            'default_profile_image.png',
            'image/png',
            null,
            true // Test mode
        );
        $requestData['files'] = [$file];

        // Send the POST request to the Calendar save endpoint
        $response = $this->postJson('/calendar/events/save', $requestData);

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson(['message' => 'Event saved successfully!']);

        // Retrieve the event ID from the response
        $event = $response->getData()->event;

        // Assert event exists in the database
        $this->assertDatabaseHas('calendar', [
            'event_name' => 'Test Event',
            'user_id' => $user->id,
            'event_date' => '2025-05-01',
            'event_time' => '14:00',
            'all_day' => false,
        ]);

        // Assert file exists in the database
        $this->assertDatabaseHas('calendar_files', [
            'file_name' => 'default_profile_image.png',
            'file_path' => "events/{$event->id}/{$user->id}/default_profile_image.png",
        ]);

        // Assert file exists in storage
        Storage::disk('public')->assertExists("events/{$event->id}/{$user->id}/default_profile_image.png");
    }

    public function test_it_can_update_an_existing_calendar_event_with_new_data()
    {
        // Ensure the test uses the actual storage path
        Storage::disk('public')->put(
            'default_profile_image.png',
            file_get_contents(storage_path('app/public/default_profile_image.png'))
        );

        // Create a test user
        $user = User::factory()->create();

        // Create an initial event
        $originalEventData = [
            'id' => null, // New event
            'event_name' => 'Original Event',
            'user_id' => $user->id,
            'date' => '2025-05-01',
            'time' => '14:00',
            'allDay' => false,
        ];

        $originalFile = new UploadedFile(
            storage_path('app/public/default_profile_image.png'),
            'default_profile_image.png',
            'image/png',
            null,
            true // Test mode
        );
        $originalEventData['files'] = [$originalFile];

        // Save the initial event
        $initialResponse = $this->postJson('/calendar/events/save', $originalEventData);
        $initialResponse->assertStatus(200)->assertJson(['message' => 'Event saved successfully!']);

        // Retrieve the event ID
        $event = $initialResponse->getData()->event;

        // Prepare updated event data
        $updatedEventData = [
            'id' => $event->id, // Existing event ID
            'event_name' => 'Updated Event',
            'user_id' => $user->id,
            'date' => '2025-05-02', // Change date
            'time' => '15:00', // Change time
            'allDay' => false,
        ];

        $newFile = new UploadedFile(
            storage_path('app/public/default_profile_image.png'),
            'updated_profile_image.png',
            'image/png',
            null,
            true // Test mode
        );
        $updatedEventData['files'] = [$newFile];

        // Send the POST request to update the event
        $updateResponse = $this->postJson('/calendar/events/save', $updatedEventData);
        $updateResponse->assertStatus(200)->assertJson(['message' => 'Event saved successfully!']);

        // Assert updated event exists in the database
        $this->assertDatabaseHas('calendar', [
            'id' => $event->id, // Ensure it's the same event ID
            'event_name' => 'Updated Event',
            'user_id' => $user->id,
            'event_date' => '2025-05-02',
            'event_time' => '15:00',
            'all_day' => false,
        ]);

        // Assert updated file exists in the database
        $this->assertDatabaseHas('calendar_files', [
            'file_name' => 'updated_profile_image.png',
            'file_path' => "events/{$event->id}/{$user->id}/updated_profile_image.png",
        ]);

        // Assert the updated file exists in storage
        Storage::disk('public')->assertExists("events/{$event->id}/{$user->id}/updated_profile_image.png");
    }
}