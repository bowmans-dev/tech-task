<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\User;

class GroupSeeder extends Seeder
{
    public function run()
    {
        // Create 10 groups with predefined names
        $groupNames = ['Admins', 'Editors', 'Moderators', 'Members', 'Guests', 'Contributors', 'Supporters', 'Managers', 'Supervisors', 'Testers'];

        foreach ($groupNames as $name) {
            Group::create(['name' => $name]);
        }

        // Create 50 users using the factory
        $users = User::factory(50)->create();

        // Assign users to groups randomly
        $groups = Group::all();
        foreach ($users as $user) {
            // Assign each user to 1-3 random groups
            $randomGroups = $groups->random(rand(1, 3));
            foreach ($randomGroups as $group) {
                $user->groups()->attach($group->id);
            }
        }
    }
}