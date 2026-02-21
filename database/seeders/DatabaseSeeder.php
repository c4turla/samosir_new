<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FishSpeciesSeeder::class,
            LandingSiteSeeder::class,
            UserSeeder::class,
            VesselSeeder::class,
            ArrivalSeeder::class,
            DepartureSeeder::class,
        ]);
    }
}