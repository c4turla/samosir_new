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
            $lastDeparture = $vessel->departures->first();
            
            if (!$lastArrival && !$lastDeparture) {
                continue;
            }
            
            // Get exact timestamps for comparison (using Asia/Jakarta timezone)
            $arrTime = null;
            if ($lastArrival) {
                $arrDateStr = $lastArrival->arrival_date instanceof Carbon 
                    ? $lastArrival->arrival_date->format('Y-m-d') 
                    : Carbon::parse($lastArrival->arrival_date)->format('Y-m-d');
                $arrTime = Carbon::parse($arrDateStr . ' ' . ($lastArrival->arrival_time instanceof Carbon ? $lastArrival->arrival_time->format('H:i:s') : $lastArrival->arrival_time), 'Asia/Jakarta');
            }

            $depTime = null;
            if ($lastDeparture) {
                $rawDepDatetime = $lastDeparture->getRawOriginal('departure_datetime');
                $depTime = $rawDepDatetime 
                    ? Carbon::parse($rawDepDatetime, 'Asia/Jakarta') 
                    : null;
                    
                if (!$depTime) {
                    $depDateStr = $lastDeparture->departure_date instanceof Carbon 
                        ? $lastDeparture->departure_date->format('Y-m-d') 
                        : Carbon::parse($lastDeparture->departure_date)->format('Y-m-d');
                    $depTime = Carbon::parse($depDateStr . ' ' . ($lastDeparture->departure_time instanceof Carbon ? $lastDeparture->departure_time->format('H:i:s') : $lastDeparture->departure_time), 'Asia/Jakarta');
                }
            }

            $isAtPort = false;
            $currentSiteId = null;
            $statusStr = '';
            $timeRef = null;
            $now = Carbon::now('Asia/Jakarta');

            if ($lastArrival && $lastDeparture) {
                // Compare actual event times
                if ($depTime->gt($arrTime)) {
                    // Departure is the most recent event
                    if ($now->lte($depTime)) {
                        $isAtPort = true;
                        $currentSiteId = $lastDeparture->landing_site_id;
                        $statusStr = 'Persiapan Berangkat';
                        $timeRef = $depTime;
                    }
                } else {
                    // Arrival is the most recent event
                    $isAtPort = true;
                    $currentSiteId = $lastArrival->landing_site_id;
                    $statusStr = $lastArrival->status;
                    $timeRef = $arrTime;
                }
            } elseif ($lastArrival) {
                $isAtPort = true;
                $currentSiteId = $lastArrival->landing_site_id;
                $statusStr = $lastArrival->status;
                $timeRef = $arrTime;
            } elseif ($lastDeparture) {
                if ($now->lte($depTime)) {
                    $isAtPort = true;
                    $currentSiteId = $lastDeparture->landing_site_id;
                    $statusStr = 'Persiapan Berangkat';
                    $timeRef = $depTime;
                }
            }

            if ($isAtPort && $currentSiteId) {
                $site = $landingSites->get($currentSiteId);
                
                // Only consider landing sites that have coordinate data
                if ($site && $site->latitude && $site->longitude) {
                    if (!isset($positions[$currentSiteId])) {
                        $positions[$currentSiteId] = [
                            'id' => $site->id,
                            'name' => $site->site_name,
                            'latitude' => $site->latitude,
                            'longitude' => $site->longitude,
                            'vessels' => []
                        ];
                    }
                    
                    $positions[$currentSiteId]['vessels'][] = [
                        'id' => $vessel->id,
                        'name' => $vessel->vessel_name,
                        'owner' => $vessel->owner_name ?? '-',
                        'gt' => $vessel->gt ?? '-',
                        'status' => $statusStr,
                        'arrival_time' => $timeRef ? $timeRef->diffForHumans($now) : '-'
                    ];
                }
            }
        }

        return Inertia::render('VesselPositions/Index', [
            'positions' => array_values($positions)
        ]);
    }
}
