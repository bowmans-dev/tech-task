<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

Artisan::command('users:count', function () {

    $userCount = DB::table('users')->count();

    $adminCount = DB::table('admins')->count();

    $this->info("Total Users: {$userCount}");
    $this->info("Total Admins: {$adminCount}");

})->purpose('Count the total number of users and admins in their respective tables.');



Artisan::command('users:inspect {user_id}', function ($user_id) {

    $user = User::find($user_id);

    if (! $user) {
        $this->error("User with ID {$user_id} not found.");

        return;
    }

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

    $users = User::all();

    if ($users->isEmpty()) {
        $this->info('No users found in the system.');

        return;
    }

    $fields = $fields ? explode(',', $fields) : null;

    $availableFields = [
        'id', 'first_name', 'last_name', 'email', 'phone',
        'country', 'gender', 'profile_picture', 'created_at', 'updated_at',
    ];

    if ($fields) {
        foreach ($fields as $field) {
            if (! in_array($field, $availableFields)) {
                $this->error("Invalid field: {$field}. Available fields are: ".implode(', ', $availableFields));

                return;
            }
        }
    } else {
        $fields = $availableFields;
    }

    $this->info('User Details:');
    foreach ($users as $user) {
        $this->line("\n---------------------------------");
        foreach ($fields as $field) {
            $this->line("{$field}: ".($user->{$field} ?? 'N/A'));
        }
    }
    
})->purpose('Inspect all user details for specific fields or all fields.');