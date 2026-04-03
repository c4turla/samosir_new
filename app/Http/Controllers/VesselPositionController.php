<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vessel;
use App\Models\LandingSite;
use Carbon\Carbon;
use Inertia\Inertia;

class VesselPositionController extends Controller
{
    /**
     * Display a map with all vessels currently at port.
     */
    public function index()
    {
        // Get all vessels with their arrivals and departures to determine current status
        $vessels = Vessel::with([
            'arrivals' => function ($query) {
                $query->orderBy('arrival_date', 'desc')->orderBy('arrival_time', 'desc');
            },
            'departures' => function ($query) {
                $query->orderBy('departure_date', 'desc')->orderBy('departure_time', 'desc');
            }
        ])->get();

        $landingSites = LandingSite::all()->keyBy('id');
        $positions = [];

        foreach ($vessels as $vessel) {
            $lastArrival = $vessel->arrivals->first();
            
            // If the vessel has never arrived, we don't know where it is
            if (!$lastArrival || !$lastArrival->landing_site_id) {
                continue;
            }
            
            $lastDeparture = $vessel->departures->first();
            $isAtPort = true;

            // Formatting correctly to merge date and time strings for comparison
            $arrDateStr = $lastArrival->arrival_date instanceof Carbon 
                ? $lastArrival->arrival_date->format('Y-m-d') 
                : Carbon::parse($lastArrival->arrival_date)->format('Y-m-d');

                // Use format('H:i:s') to get only the time part from the casted arrival_time Carbon object
                $arrTime = Carbon::parse($arrDateStr . ' ' . ($lastArrival->arrival_time instanceof Carbon ? $lastArrival->arrival_time->format('H:i:s') : $lastArrival->arrival_time));
                
                if ($lastDeparture) {
                    $depDateStr = $lastDeparture->departure_date instanceof Carbon 
                        ? $lastDeparture->departure_date->format('Y-m-d') 
                        : Carbon::parse($lastDeparture->departure_date)->format('Y-m-d');

                    $depTime = Carbon::parse($depDateStr . ' ' . ($lastDeparture->departure_time instanceof Carbon ? $lastDeparture->departure_time->format('H:i:s') : $lastDeparture->departure_time));

                    // If the most recent departure happened strictly after the most recent arrival,
                    // the vessel is at sea.
                    if ($depTime->gt($arrTime)) {
                        $isAtPort = false;
                    }
                }

                if ($isAtPort) {
                    $siteId = $lastArrival->landing_site_id;
                    $site = $landingSites->get($siteId);
                    
                    // Only consider landing sites that have coordinate data
                    if ($site && $site->latitude && $site->longitude) {
                        if (!isset($positions[$siteId])) {
                            $positions[$siteId] = [
                                'id' => $site->id,
                                'name' => $site->site_name,
                                'latitude' => $site->latitude,
                                'longitude' => $site->longitude,
                                'vessels' => []
                            ];
                        }
                        
                        $positions[$siteId]['vessels'][] = [
                            'id' => $vessel->id,
                            'name' => $vessel->vessel_name,
                            'owner' => $vessel->owner_name ?? '-',
                            'gt' => $vessel->gt ?? '-',
                            'status' => $lastArrival->status,
                            'arrival_time' => $arrTime->diffForHumans()
                        ];
                    }
                }
        }

        return Inertia::render('VesselPositions/Index', [
            'positions' => array_values($positions)
        ]);
    }
}
