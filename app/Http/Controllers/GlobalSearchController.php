<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FishSpecies;
use App\Models\Vessel;
use App\Models\Arrival;
use App\Models\Departure;
use Illuminate\Support\Facades\Log;

class GlobalSearchController extends Controller
{
    /**
     * Perform a global search across multiple models.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty(trim($query))) {
            return response()->json([]);
        }

        $results = [];

        try {
            // 1. Search Vessels (Kapal)
            $vessels = Vessel::where('vessel_name', 'like', "%{$query}%")
                ->orWhere('license_number', 'like', "%{$query}%")
                ->limit(4)
                ->get()
                ->map(function ($vessel) {
                    return [
                        'type' => 'vessel',
                        'title' => $vessel->vessel_name,
                        'subtitle' => 'Izin: ' . ($vessel->license_number ?: '-'),
                        'url' => route('vessels.edit', $vessel->id)
                    ];
                });
            
            if ($vessels->isNotEmpty()) {
                $results[] = [
                    'group' => 'Kapal',
                    'items' => $vessels
                ];
            }

            // 2. Search Arrivals (Kedatangan)
            $arrivals = Arrival::with('vessel')
                ->whereHas('vessel', function ($q) use ($query) {
                    $q->where('vessel_name', 'like', "%{$query}%");
                })
                ->orWhere('origin', 'like', "%{$query}%")
                ->limit(4)
                ->get()
                ->map(function ($arrival) {
                    $date = $arrival->arrival_date ? $arrival->arrival_date->format('d/m/Y') : '';
                    return [
                        'type' => 'arrival',
                        'title' => 'Kedatangan: ' . ($arrival->vessel->vessel_name ?? 'Unknown'),
                        'subtitle' => "Asal: {$arrival->origin} | Tgl: {$date}",
                        'url' => route('arrivals.edit', $arrival->id)
                    ];
                });

            if ($arrivals->isNotEmpty()) {
                $results[] = [
                    'group' => 'Kedatangan',
                    'items' => $arrivals
                ];
            }

            // 3. Search Departures (Keberangkatan)
            $departures = Departure::with('vessel')
                ->whereHas('vessel', function ($q) use ($query) {
                    $q->where('vessel_name', 'like', "%{$query}%");
                })
                ->orWhere('destination', 'like', "%{$query}%")
                ->limit(4)
                ->get()
                ->map(function ($departure) {
                    $date = $departure->departure_date ? $departure->departure_date->format('d/m/Y') : '';
                    return [
                        'type' => 'departure',
                        'title' => 'Keberangkatan: ' . ($departure->vessel->vessel_name ?? 'Unknown'),
                        'subtitle' => "Tujuan: {$departure->destination} | Tgl: {$date}",
                        'url' => route('departures.edit', $departure->id)
                    ];
                });

            if ($departures->isNotEmpty()) {
                $results[] = [
                    'group' => 'Keberangkatan',
                    'items' => $departures
                ];
            }

            // 4. Search Fish Species (Jenis Ikan)
            $fishes = FishSpecies::where('species_name', 'like', "%{$query}%")
                ->orWhere('local_name', 'like', "%{$query}%")
                ->limit(4)
                ->get()
                ->map(function ($fish) {
                    return [
                        'type' => 'fish',
                        'title' => $fish->species_name,
                        'subtitle' => 'Nama Lokal: ' . ($fish->local_name ?: '-'),
                        'url' => route('fish-species.edit', $fish->id)
                    ];
                });

            if ($fishes->isNotEmpty()) {
                $results[] = [
                    'group' => 'Jenis Ikan',
                    'items' => $fishes
                ];
            }

            return response()->json($results);
            
        } catch (\Exception $e) {
            Log::error('Global Search Error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during search'], 500);
        }
    }
}
