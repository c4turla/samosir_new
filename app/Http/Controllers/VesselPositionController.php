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
        // Set locale to Indonesian for Indonesian relative time and dates
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID');

        // Get all vessels with their arrivals and departures to determine current status
        $vessels = Vessel::with([
            'arrivals' => function ($query) {
                $query->orderBy('arrival_date', 'desc')->orderBy('arrival_time', 'desc');
            },
            'arrivals.landingSite',
            'arrivals.unloading.landingSite',
            'departures' => function ($query) {
                $query->orderBy('departure_date', 'desc')->orderBy('departure_time', 'desc');
            },
            'departures.landingSite'
        ])->get();

        $landingSites = LandingSite::all()->keyBy('id');
        $positions = [];
        $now = Carbon::now('Asia/Jakarta');

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

            // Get the latest approved unloading for this arrival
            $lastUnloading = null;
            $unlTime = null;
            if ($lastArrival) {
                $lastUnloading = $lastArrival->unloading
                    ->where('approval_status', true)
                    ->sortByDesc(function ($unl) {
                        $dateStr = $unl->unloading_date instanceof Carbon 
                            ? $unl->unloading_date->format('Y-m-d') 
                            : Carbon::parse($unl->unloading_date)->format('Y-m-d');
                        $timeStr = $unl->unloading_time instanceof Carbon 
                            ? $unl->unloading_time->format('H:i:s') 
                            : $unl->unloading_time;
                        return $dateStr . ' ' . $timeStr;
                    })
                    ->first();
                if ($lastUnloading) {
                    $unlDateStr = $lastUnloading->unloading_date instanceof Carbon 
                        ? $lastUnloading->unloading_date->format('Y-m-d') 
                        : Carbon::parse($lastUnloading->unloading_date)->format('Y-m-d');
                    $unlTimeStr = $lastUnloading->unloading_time instanceof Carbon 
                        ? $lastUnloading->unloading_time->format('H:i:s') 
                        : $lastUnloading->unloading_time;
                    $unlTime = Carbon::parse($unlDateStr . ' ' . $unlTimeStr, 'Asia/Jakarta');
                }
            }

            // Determine which is the latest event among arrival, approved unloading, and departure
            $events = [];
            if ($arrTime) {
                $events[] = [
                    'type' => 'arrival',
                    'time' => $arrTime,
                    'site_id' => $lastArrival->landing_site_id,
                    'status' => $lastArrival->status
                ];
            }
            if ($unlTime) {
                $events[] = [
                    'type' => 'unloading',
                    'time' => $unlTime,
                    'site_id' => $lastUnloading->landing_site_id,
                    'status' => 'BONGKAR'
                ];
            }
            if ($depTime) {
                $events[] = [
                    'type' => 'departure',
                    'time' => $depTime,
                    'site_id' => $lastDeparture->landing_site_id,
                    'status' => 'Persiapan Berangkat'
                ];
            }

            // Sort events by time descending (latest event first)
            usort($events, function ($a, $b) {
                if ($a['time']->equalTo($b['time'])) {
                    return 0;
                }
                return $a['time']->lt($b['time']) ? 1 : -1;
            });

            if (count($events) > 0) {
                $latestEvent = $events[0];
                if ($latestEvent['type'] === 'departure') {
                    // If departure is the latest event, only at port if now <= depTime (preparation window)
                    if ($now->lte($latestEvent['time'])) {
                        $isAtPort = true;
                        $currentSiteId = $latestEvent['site_id'];
                        $statusStr = $latestEvent['status'];
                        $timeRef = $latestEvent['time'];
                    }
                } else {
                    // If arrival or unloading is the latest event, the vessel is currently at port
                    $isAtPort = true;
                    $currentSiteId = $latestEvent['site_id'];
                    $statusStr = $latestEvent['status'];
                    $timeRef = $latestEvent['time'];
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

                    // Build movement history for this vessel
                    $movements = [];

                    // 1. Gather arrivals
                    foreach ($vessel->arrivals as $arr) {
                        $arrSite = $arr->landingSite;
                        if ($arrSite && $arrSite->latitude && $arrSite->longitude) {
                            $arrDateStr = $arr->arrival_date instanceof Carbon 
                                ? $arr->arrival_date->format('Y-m-d') 
                                : Carbon::parse($arr->arrival_date)->format('Y-m-d');
                            $arrTimeStr = $arr->arrival_time instanceof Carbon 
                                ? $arr->arrival_time->format('H:i:s') 
                                : $arr->arrival_time;
                            $arrTimestamp = Carbon::parse($arrDateStr . ' ' . $arrTimeStr, 'Asia/Jakarta');
                            
                            $movements[] = [
                                'type' => 'arrival',
                                'type_label' => 'Kedatangan',
                                'timestamp' => $arrTimestamp->timestamp,
                                'time' => $arrTimestamp->translatedFormat('d F Y, H:i') . ' WIB',
                                'time_relative' => $arrTimestamp->diffForHumans(),
                                'site_name' => $arrSite->site_name,
                                'latitude' => (float)$arrSite->latitude,
                                'longitude' => (float)$arrSite->longitude,
                                'details' => "Asal: " . ($arr->origin ?? '-') . " | Kualitas: " . ($arr->fish_quality ?? '-') . " | Status: " . ($arr->status ?? '-')
                            ];
                            
                            // Unloadings for this arrival
                            if ($arr->unloading) {
                                foreach ($arr->unloading as $unl) {
                                    $unlSite = $unl->landingSite ?? $arrSite;
                                    if ($unlSite && $unlSite->latitude && $unlSite->longitude) {
                                        $unlDateStr = $unl->unloading_date instanceof Carbon 
                                            ? $unl->unloading_date->format('Y-m-d') 
                                            : Carbon::parse($unl->unloading_date)->format('Y-m-d');
                                        $unlTimeStr = $unl->unloading_time instanceof Carbon 
                                            ? $unl->unloading_time->format('H:i:s') 
                                            : $unl->unloading_time;
                                        $unlTimestamp = Carbon::parse($unlDateStr . ' ' . $unlTimeStr, 'Asia/Jakarta');
                                        
                                        $movements[] = [
                                            'type' => 'unloading',
                                            'type_label' => 'Penimbangan',
                                            'timestamp' => $unlTimestamp->timestamp,
                                            'time' => $unlTimestamp->translatedFormat('d F Y, H:i') . ' WIB',
                                            'time_relative' => $unlTimestamp->diffForHumans(),
                                            'site_name' => $unlSite->site_name,
                                            'latitude' => (float)$unlSite->latitude,
                                            'longitude' => (float)$unlSite->longitude,
                                            'details' => "No. Ref: " . ($unl->reference_number ?? '-') . " | Kapten: " . ($unl->captain_name ?? '-')
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    // 2. Gather departures
                    foreach ($vessel->departures as $dep) {
                        $depSite = $dep->landingSite;
                        if ($depSite && $depSite->latitude && $depSite->longitude) {
                            $rawDepDatetime = $dep->getRawOriginal('departure_datetime');
                            $depTimestamp = $rawDepDatetime 
                                ? Carbon::parse($rawDepDatetime, 'Asia/Jakarta') 
                                : null;
                                
                            if (!$depTimestamp) {
                                $depDateStr = $dep->departure_date instanceof Carbon 
                                    ? $dep->departure_date->format('Y-m-d') 
                                    : Carbon::parse($dep->departure_date)->format('Y-m-d');
                                $depTimeStr = $dep->departure_time instanceof Carbon 
                                    ? $dep->departure_time->format('H:i:s') 
                                    : $dep->departure_time;
                                $depTimestamp = Carbon::parse($depDateStr . ' ' . $depTimeStr, 'Asia/Jakarta');
                            }
                            
                            $movements[] = [
                                'type' => 'departure',
                                'type_label' => 'Keberangkatan',
                                'timestamp' => $depTimestamp->timestamp,
                                'time' => $depTimestamp->translatedFormat('d F Y, H:i') . ' WIB',
                                'time_relative' => $depTimestamp->diffForHumans(),
                                'site_name' => $depSite->site_name,
                                'latitude' => (float)$depSite->latitude,
                                'longitude' => (float)$depSite->longitude,
                                'details' => "Tujuan: " . ($dep->destination ?? '-') . " | Nakhoda: " . ($dep->nakhoda_name ?? '-')
                            ];
                        }
                    }

                    // Sort movements chronologically
                    usort($movements, function ($a, $b) {
                        return $a['timestamp'] <=> $b['timestamp'];
                    });

                    // Limit to last 5 movements to avoid clutter while showing the full latest cycle
                    $movements = array_slice($movements, -5);
                    
                    $positions[$currentSiteId]['vessels'][] = [
                        'id' => $vessel->id,
                        'name' => $vessel->vessel_name,
                        'owner' => $vessel->owner_name ?? '-',
                        'gt' => $vessel->gt ?? '-',
                        'status' => $statusStr,
                        'arrival_time' => $timeRef ? $timeRef->diffForHumans() : '-',
                        'movements' => $movements
                    ];
                }
            }
        }

        return Inertia::render('VesselPositions/Index', [
            'positions' => array_values($positions)
        ]);
    }
}
