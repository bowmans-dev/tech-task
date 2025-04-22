<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    protected $model = \App\Models\Group::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(), // Generate unique group names
        ];
    }
}