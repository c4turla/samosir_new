<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vessel;
use Carbon\Carbon;

class VesselSeeder extends Seeder
{
    public function run(): void
    {
        $vesselData = [
            [
                'vessel_name' => 'KM. Samudra Jaya',
                'owner_name' => 'H. Ahmad Siregar',
                'license_number' => 'PP.12345/SS/2024',
                'gt' => '45',
                'fishing_gear' => 'Gill Net',
                'length' => '15.5',
                'loa' => '16.0',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Bahari Raya',
                'owner_name' => 'Budi Pratama',
                'license_number' => 'PP.12346/SS/2024',
                'gt' => '38',
                'fishing_gear' => 'Purse Seine',
                'length' => '14.2',
                'loa' => '14.5',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Nelayan Bersatu',
                'owner_name' => 'Dewi Sartika',
                'license_number' => 'PP.12347/SS/2024',
                'gt' => '52',
                'fishing_gear' => 'Long Line',
                'length' => '16.8',
                'loa' => '17.5',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Laut Biru',
                'owner_name' => 'Rudi Hartono',
                'license_number' => 'PP.12348/SS/2024',
                'gt' => '30',
                'fishing_gear' => 'Gill Net',
                'length' => '13.5',
                'loa' => '14.0',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Harapan Baru',
                'owner_name' => 'Maya Putri',
                'license_number' => 'PP.12349/SS/2024',
                'gt' => '48',
                'fishing_gear' => 'Purse Seine',
                'length' => '15.8',
                'loa' => '16.5',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Tirta Samudra',
                'owner_name' => 'Agus Santoso',
                'license_number' => 'PP.12350/SS/2024',
                'gt' => '42',
                'fishing_gear' => 'Long Line',
                'length' => '14.8',
                'loa' => '15.5',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Bintang Timur',
                'owner_name' => 'Ir. Wijaya',
                'license_number' => 'PP.12351/SS/2024',
                'gt' => '55',
                'fishing_gear' => 'Gill Net',
                'length' => '17.2',
                'loa' => '18.0',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Pasifik Mandiri',
                'owner_name' => 'Kapt. Purwanto',
                'license_number' => 'PP.12352/SS/2024',
                'gt' => '35',
                'fishing_gear' => 'Purse Seine',
                'length' => '13.8',
                'loa' => '14.5',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Nusantara Tiga',
                'owner_name' => 'DR. Tanjung',
                'license_number' => 'PP.12353/SS/2024',
                'gt' => '60',
                'fishing_gear' => 'Long Line',
                'length' => '18.5',
                'loa' => '19.5',
                'approval_status' => 'approved',
            ],
            [
                'vessel_name' => 'KM. Cempaka Putih',
                'owner_name' => 'Rina Melati',
                'license_number' => 'PP.12354/SS/2024',
                'gt' => '28',
                'fishing_gear' => 'Gill Net',
                'length' => '12.5',
                'loa' => '13.0',
                'approval_status' => 'approved',
            ],
            // Pending vessels for approval table
            [
                'vessel_name' => 'KM. Indah Laut',
                'owner_name' => 'Siti Aminah',
                'license_number' => 'PP.12355/SS/2024',
                'gt' => '40',
                'fishing_gear' => 'Purse Seine',
                'length' => '14.5',
                'loa' => '15.0',
                'approval_status' => 'pending',
            ],
            [
                'vessel_name' => 'KM. Samudra Hijau',
                'owner_name' => 'Hendra Wijaya',
                'license_number' => 'PP.12356/SS/2024',
                'gt' => '45',
                'fishing_gear' => 'Gill Net',
                'length' => '15.2',
                'loa' => '16.0',
                'approval_status' => 'pending',
            ],
            [
                'vessel_name' => 'KM. Laut Utara',
                'owner_name' => 'Dedi Kurniawan',
                'license_number' => 'PP.12357/SS/2024',
                'gt' => '50',
                'fishing_gear' => 'Long Line',
                'length' => '16.0',
                'loa' => '17.0',
                'approval_status' => 'pending',
            ],
            [
                'vessel_name' => 'KM. Bima Sakti',
                'owner_name' => 'Bambang Suryadi',
                'license_number' => 'PP.12358/SS/2024',
                'gt' => '38',
                'fishing_gear' => 'Purse Seine',
                'length' => '14.0',
                'loa' => '14.5',
                'approval_status' => 'pending',
            ],
            [
                'vessel_name' => 'KM. Garuda Bahari',
                'owner_name' => 'Lestari Dewi',
                'license_number' => 'PP.12359/SS/2024',
                'gt' => '55',
                'fishing_gear' => 'Gill Net',
                'length' => '17.0',
                'loa' => '18.0',
                'approval_status' => 'pending',
            ],
        ];

        foreach ($vesselData as $vessel) {
            Vessel::create([
                ...$vessel,
                'created_at' => Carbon::now()->subDays(rand(30, 90)),
                'updated_at' => Carbon::now()->subDays(rand(1, 29)),
            ]);
        }

        $this->command->info('VesselSeeder completed successfully. Created ' . count($vesselData) . ' vessels.');
    }
}