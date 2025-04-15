<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

Artisan::command('users:count', function () {
    // Count the number of users in the users table
    $userCount = DB::table('users')->count();

    // Count the number of admins in the admins table
    $adminCount = DB::table('admins')->count();

    // Display the counts
    $this->info("Total Users: {$userCount}");
    $this->info("Total Admins: {$adminCount}");
})->purpose('Count the total number of users and admins in their respective tables.');

Artisan::command('users:inspect {user_id}', function ($user_id) {
    // Fetch the user by ID
    $user = User::find($user_id);

    // Check if user exists
    if (! $user) {
        $this->error("User with ID {$user_id} not found.");

        return;
    }

    // Display user details
    $this->info("User Details for ID: {$user->id}");
    $this->line("Name: {$user->first_name} {$user->last_name}");
    $this->line('Phone: '.($user->phone ?? 'N/A'));
    $this->line("Email: {$user->email}");
    $this->line('Phone: '.($user->phone ?? 'N/A'));
    $this->line('Country: '.($user->country ?? 'N/A'));
    $this->line('Gender: '.($user->gender ?? 'N/A'));
    $this->line('Profile Picture: '.($user->profile_picture ?? 'No profile picture'));
    $this->line("Created At: {$user->created_at}");
    $this->line("Updated At: {$user->updated_at}");
})->purpose('Inspect detailed information about a specific user.');

Artisan::command('users:inspect-all {fields?}', function ($fields = null) {
    // Fetch all users
    $users = User::all();

    // Check if no users are found
    if ($users->isEmpty()) {
        $this->info('No users found in the system.');

        return;
    }

    // Parse the requested fields
    $fields = $fields ? explode(',', $fields) : null; // Split fields into an array if provided

    // Define all possible fields for validation
    $availableFields = [
        'id', 'first_name', 'last_name', 'email', 'phone',
        'country', 'gender', 'profile_picture', 'created_at', 'updated_at',
    ];

    // Validate requested fields
    if ($fields) {
        foreach ($fields as $field) {
            if (! in_array($field, $availableFields)) {
                $this->error("Invalid field: {$field}. Available fields are: ".implode(', ', $availableFields));

                return;
            }
        }
    } else {
        $fields = $availableFields; // Default to all fields if none specified
    }

    // Display user details
    $this->info('User Details:');
    foreach ($users as $user) {
        $this->line("\n---------------------------------");
        foreach ($fields as $field) {
            $this->line("{$field}: ".($user->{$field} ?? 'N/A'));
        }
    }
})->purpose('Inspect all user details for specific fields or all fields.');
