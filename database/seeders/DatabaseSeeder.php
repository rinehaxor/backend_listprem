<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user for Filament
        User::factory()->create([
            'name' => 'Fiona Olivia',
            'email' => 'fionaolivia177@gmail.com',
            'password' => bcrypt('2133qwe1'),
        ]);

        // Seed sample data
        $this->call(SampleDataSeeder::class);
    }
}
