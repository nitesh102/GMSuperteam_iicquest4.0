<?php

namespace Database\Factories;

use App\Models\ComplaintCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintFactory extends Factory
{
    public function definition(): array
    {
        return [
            'complaint_no' => 'CMP-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
            'citizen_id' => User::factory(),
            'category_id' => ComplaintCategory::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'location' => fake()->optional()->address(),
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'emergency']),
            'current_status' => 'submitted',
            'ai_summary' => fake()->optional()->sentence(),
            'is_spam' => false,
            'created_by' => User::factory(),
        ];
    }

    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Buy now and get free money! Limited offer.',
            'is_spam' => true,
        ]);
    }

    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Fire reported at the market! Emergency response needed.',
            'priority' => 'emergency',
        ]);
    }

    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Urgent: water leak reported on Main Street.',
            'priority' => 'high',
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Repair request for street light.',
            'priority' => 'medium',
        ]);
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'current_status' => $status,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }
}
