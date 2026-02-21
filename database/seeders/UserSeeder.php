<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@samosir.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'petugas@samosir.com'],
            [
                'name' => 'Petugas Pelayanan',
                'password' => bcrypt('password'),
                'role' => 'petugas',
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'syahbandar@samosir.com'],
            [
                'name' => 'Syahbandar',
                'password' => bcrypt('password'),
                'role' => 'syahbandar',
            ]
        );

        $this->command->info('UserSeeder completed successfully.');
    }
}
