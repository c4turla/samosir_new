<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FishSpeciesSeeder extends Seeder
{
    public function run(): void
    {
        $species = [
            ['species_name' => 'CAKALANG'],
            ['species_name' => 'MADIDIHANG'],
            ['species_name' => 'TENGGIRI'],
            ['species_name' => 'TONGKOL KOMO'],
            ['species_name' => 'KEMBUNG'],
            ['species_name' => 'LAYANG'],
            ['species_name' => 'KAKAP PUTIH'],
            ['species_name' => 'KERAPU SUNU'],
            ['species_name' => 'CUMI-CUMI'],
            ['species_name' => 'LAINNYA'],
        ];

        foreach ($species as $item) {
            DB::table('fish_species')->insert([
                'species_name' => $item['species_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}