<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DepartmentSeeder::class,
        ]);
        $password = bin2hex(random_bytes(4));
        $user = User::create([
            'name' => 'Super User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make($password),
        ]);
        $this->command->info("Superadmin credentials: admin@gmail.com / $password");
        $user->assignRole('Superadmin');
    }
}
