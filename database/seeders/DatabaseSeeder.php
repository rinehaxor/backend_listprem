<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user for Filament (Default)
        // PENTING: Jangan masukkan password asli kamu di sini jika di push ke Github
        User::factory()->create([
            'name' => 'Admin Baru',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        // Seed sample data
        $this->call(SampleDataSeeder::class);
    }
}
