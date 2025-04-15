<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Admin account
        Admin::create([
            'name' => 'Toni',
            'email' => 'admin@example.com',
            'password' => bcrypt('test1234'),
            'permissions' => json_encode(['manage_users', 'edit_settings']),
        ]);

        // Create User accounts
        User::factory()->count(20)->create();
    }
}
