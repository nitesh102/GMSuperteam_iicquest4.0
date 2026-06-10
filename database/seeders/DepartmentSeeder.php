<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Health Department',
                'description' => 'Oversees public health services, disease control, sanitation standards, and healthcare facility management across the municipality.',
            ],
            [
                'name' => 'Roads & Infrastructure',
                'description' => 'Manages construction, maintenance, and repair of roads, bridges, drainage systems, and public infrastructure projects.',
            ],
            [
                'name' => 'Water Supply & Sanitation',
                'description' => 'Responsible for drinking water distribution, water quality testing, sewage systems, and public toilet maintenance.',
            ],
            [
                'name' => 'Waste Management',
                'description' => 'Handles solid waste collection, recycling programs, landfill operations, and street cleaning services.',
            ],
            [
                'name' => 'Electricity & Public Lighting',
                'description' => 'Maintains public street lighting, coordinates with power utilities, and manages electrical safety inspections.',
            ],
            [
                'name' => 'Public Safety & Security',
                'description' => 'Coordinates with law enforcement, manages disaster response, fire safety, and community policing initiatives.',
            ],
            [
                'name' => 'Environment & Parks',
                'description' => 'Protects green spaces, manages public parks, monitors pollution levels, and oversees environmental conservation programs.',
            ],
            [
                'name' => 'Transportation',
                'description' => 'Regulates public transport, manages traffic flow, maintains road signage, and oversees parking infrastructure.',
            ],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['name' => $dept['name']],
                ['description' => $dept['description']]
            );
        }
    }
}
