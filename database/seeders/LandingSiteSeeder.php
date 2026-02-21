<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LandingSiteSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            [
                'site_name' => 'DERMAGA 1',
                'address' => 'PELABUHAN PERIKANAN NUSANTARA SIBOLGA',
                'latitude' => 1.720469,
                'longitude' => 98.795337,
                'site_type' => 'dermaga'
            ],
            [
                'site_name' => 'DERMAGA 2',
                'address' => 'PELABUHAN PERIKANAN NUSANTARA SIBOLGA',
                'latitude' => 1.720833,
                'longitude' => 98.795848,
                'site_type' => 'dermaga'
            ],
            [
                'site_name' => 'TANGKAHAN GARUDA MAS',
                'address' => 'Jl. Mojopahit, Kota Sibolga',
                'latitude' => 1.725278,
                'longitude' => 98.795833,
                'site_type' => 'tangkahan'
            ],
        ];

        foreach ($sites as $site) {
            DB::table('landing_sites')->insert([
                'site_name' => $site['site_name'],
                'address' => $site['address'],
                'latitude' => $site['latitude'],
                'longitude' => $site['longitude'],
                'site_type' => $site['site_type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}