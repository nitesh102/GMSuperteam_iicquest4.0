<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => fake()->unique()->word(),
            'description' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
