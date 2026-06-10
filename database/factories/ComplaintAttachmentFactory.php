<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintAttachmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'file_path' => 'complaints/dummy/' . fake()->uuid() . '.jpg',
            'file_name' => fake()->word() . '.jpg',
            'file_type' => 'image/jpeg',
            'uploaded_by' => User::factory(),
            'created_by' => User::factory(),
        ];
    }
}
