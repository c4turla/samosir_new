<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arrival;
use App\Models\Vessel;
use App\Models\LandingSite;
use App\Models\User;
use Carbon\Carbon;

class ArrivalSeeder extends Seeder
{
    public function run(): void
    {
        // Get vessels, landing sites, and users
        $vessels = Vessel::all();
        $landingSites = LandingSite::all();
        $users = User::all();
        
        if ($vessels->isEmpty() || $landingSites->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No vessels, landing sites, or users found. Skipping ArrivalSeeder.');
            return;
        }

        $statuses = ['TAMBAT', 'BONGKAR', 'SELESAI'];
        $qualities = ['SEGAR', 'BEKU', 'OLAHAN'];
        $origins = ['Laut Jawa', 'Laut Maluku', 'Laut Banda', 'Laut Sulawesi', 'Laut Natuna'];

        // Generate 20 arrivals with realistic dates
        for ($i = 0; $i < 20; $i++) {
            $vessel = $vessels->random();
            $landingSite = $landingSites->random();
            $user = $users->random();
            
            // Generate random date within last 30 days
            $daysAgo = rand(0, 29);
            $arrivalDate = Carbon::now()->subDays($daysAgo);
            $arrivalTime = Carbon::createFromTime(rand(6, 18), rand(0, 59), 0);
            
            // Set status based on date
            if ($daysAgo < 1) {
                $status = 'TAMBAT';
            } elseif ($daysAgo < 3) {
                $status = 'BONGKAR';
            } else {
                $status = 'SELESAI';
            }

            $approvalStatus = (string) rand(0, 1);
            $approvedBy = $approvalStatus ? $user : null;
            $approvedAt = $approvedBy ? $arrivalDate->copy()->addHours(rand(1, 24)) : null;
            $inputBy = $user;

            $isProcessed = $status === 'SELESAI';

            Arrival::create([
                'vessel_id' => $vessel->id,
                'origin' => $origins[array_rand($origins)],
                'arrival_date' => $arrivalDate->format('Y-m-d'),
                'arrival_time' => $arrivalTime->format('H:i:s'),
                'landing_site_id' => $landingSite->id,
                'fish_quality' => $qualities[array_rand($qualities)],
                'average_price' => rand(15000, 50000) + (rand(0, 99) / 100),
                'waste_volume' => rand(5, 50),
                'fish_temperature' => rand(0, 5),
                'hold_temperature' => rand(-2, 2),
                'status' => $status,
                'approval_status' => $approvalStatus,
                'approved_by' => $approvedBy?->id,
                'approved_at' => $approvedAt,
                'input_by' => $inputBy->id,
                'notes' => $this->getRandomNote(),
                'is_processed' => $isProcessed,
                'created_at' => $arrivalDate,
                'updated_at' => $arrivalDate,
            ]);
        }

        $this->command->info('ArrivalSeeder completed successfully. Created 20 arrivals.');
    }

    private function getRandomNote(): ?string
    {
        $notes = [
            'Kondisi ikan segar, kualitas baik',
            'Memerlukan penanganan khusus untuk ikan hiu',
            'Tangkapan normal, tidak ada kendala',
            'Kapal tiba terlambat karena cuaca buruk',
            'Membutuhkan bantuan tambahan untuk pembongkaran',
            'Kapal dalam kondisi baik, perawatan rutin dilakukan',
            null,
            null,
        ];

        return $notes[array_rand($notes)];
    }
}