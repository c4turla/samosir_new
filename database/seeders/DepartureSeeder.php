<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departure;
use App\Models\Vessel;
use App\Models\LandingSite;
use App\Models\User;
use Carbon\Carbon;

class DepartureSeeder extends Seeder
{
    public function run(): void
    {
        // Get vessels, landing sites, and users
        $vessels = Vessel::all();
        $landingSites = LandingSite::all();
        $users = User::all();
        
        if ($vessels->isEmpty() || $landingSites->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No vessels, landing sites, or users found. Skipping DepartureSeeder.');
            return;
        }

        $statuses = ['Sesuai Jadwal', 'Terlambat', 'Dibatalkan'];
        $destinations = ['Laut Jawa', 'Laut Maluku', 'Laut Banda', 'Laut Sulawesi', 'Laut Natuna', 'Selat Makassar', 'Laut Flores'];
        $syahbandars = ['H. Ahmad', 'B. Siregar', 'DR. Tanjung', 'Kapt. Purwanto', 'Ir. Wijaya'];
        $officers = ['Budi Santoso', 'Agus Pratama', 'Dewi Sartika', 'Rudi Hartono', 'Maya Putri'];

        // Generate 20 departures with realistic dates
        for ($i = 0; $i < 20; $i++) {
            $vessel = $vessels->random();
            $landingSite = $landingSites->random();
            $user = $users->random();
            
            // Generate random date within last 30 days
            $daysAgo = rand(0, 29);
            $departureDate = Carbon::now()->subDays($daysAgo);
            $departureTime = Carbon::createFromTime(rand(4, 10), rand(0, 59), 0);
            $departureDateTime = Carbon::create(
                $departureDate->year,
                $departureDate->month,
                $departureDate->day,
                $departureTime->hour,
                $departureTime->minute,
                $departureTime->second
            );
            
            // Estimate return date (7-14 days at sea)
            $returnDays = rand(7, 14);
            $returnDatetime = $departureDateTime->copy()->addDays($returnDays);
            
            // Determine status based on random factor
            $statusRand = rand(0, 10);
            if ($statusRand < 7) {
                $status = 'Sesuai Jadwal';
            } elseif ($statusRand < 9) {
                $status = 'Terlambat';
            } else {
                $status = 'Dibatalkan';
            }

            $approvalStatus = (string) rand(0, 1);
            $approvedBy = $approvalStatus ? $user : null;
            $approvedAt = $approvedBy ? $departureDateTime->copy()->subHours(rand(1, 24)) : null;
            $inputBy = $user;

            $crewCount = rand(5, 15);
            $iceSupply = $crewCount * rand(50, 100);
            $waterSupply = $crewCount * rand(30, 60);
            $dieselSupply = rand(500, 2000);
            $oilSupply = rand(50, 200);
            $gasolineSupply = rand(20, 100);

            Departure::create([
                'vessel_id' => $vessel->id,
                'destination' => $destinations[array_rand($destinations)],
                'crew_count' => $crewCount,
                'departure_date' => $departureDate->format('Y-m-d'),
                'departure_time' => $departureTime->format('H:i:s'),
                'departure_datetime' => $departureDateTime,
                'return_datetime' => $returnDatetime,
                'landing_site_id' => $landingSite->id,
                'syahbandar' => $syahbandars[array_rand($syahbandars)],
                'administrative_officer' => $officers[array_rand($officers)],
                'ice_supply' => $iceSupply,
                'water_supply' => $waterSupply,
                'diesel_supply' => $dieselSupply,
                'oil_supply' => $oilSupply,
                'gasoline_supply' => $gasolineSupply,
                'other_supplies' => $this->getRandomOtherSupplies(),
                'notes' => $this->getRandomNote(),
                'status' => $status,
                'approval_status' => $approvalStatus,
                'approved_by' => $approvedBy?->id,
                'approved_at' => $approvedAt,
                'input_by' => $inputBy->id,
                'signature' => null,
                'created_at' => $departureDateTime->copy()->subHours(rand(2, 48)),
                'updated_at' => $departureDateTime->copy()->subHours(rand(2, 48)),
            ]);
        }

        $this->command->info('DepartureSeeder completed successfully. Created 20 departures.');
    }

    private function getRandomOtherSupplies(): ?string
    {
        $supplies = [
            'Makanan kering, obat-obatan',
            'Perlengkapan keselamatan, peta laut',
            'Bahan bakar cadangan, spare parts',
            'Makanan segar, air minum tambahan',
            'Jaring cadangan, tali',
            null,
        ];

        return $supplies[array_rand($supplies)];
    }

    private function getRandomNote(): ?string
    {
        $notes = [
            'Kapal dalam kondisi siap berlayar',
            'Membutuhkan pengisian air tambahan',
            'Crew lengkap, semua dokumen siap',
            'Perlu pengecekan radar sebelum berangkat',
            'Kondisi mesin optimal, perawatan rutin dilakukan',
            'Membutuhkan bantuan tambahan untuk loading',
            'Semua persiapan berlayar sudah selesai',
            null,
            null,
        ];

        return $notes[array_rand($notes)];
    }
}