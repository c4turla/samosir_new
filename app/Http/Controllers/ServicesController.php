<?php

namespace App\Http\Controllers;

use App\Models\EquipmentService;
use App\Models\WaterService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServicesController extends Controller
{
    public function index()
    {
        // Get counts for each service type
        $equipmentCount = EquipmentService::whereHas('items', function($q) {
            $q->where('equipment_name', '!=', 'ice_cruiser');
        })->count();
        
        $iceCruiserCount = EquipmentService::whereHas('items', function($q) {
            $q->where('equipment_name', 'ice_cruiser');
        })->count();
        
        $waterCount = WaterService::count();

        // Get recent orders (last 5 for each)
        $recentEquipment = EquipmentService::with(['vessel', 'items'])
            ->whereHas('items', function($q) {
                $q->where('equipment_name', '!=', 'ice_cruiser');
            })
            ->latest()
            ->take(5)
            ->get();

        $recentIceCruiser = EquipmentService::with(['vessel', 'items'])
            ->whereHas('items', function($q) {
                $q->where('equipment_name', 'ice_cruiser');
            })
            ->latest()
            ->take(5)
            ->get();

        $recentWater = WaterService::latest()->take(5)->get();

        $stats = [
            'equipment' => ['count' => $equipmentCount],
            'iceCruiser' => ['count' => $iceCruiserCount],
            'water' => ['count' => $waterCount]
        ];

        return Inertia::render('Services/Index', [
            'stats' => $stats,
            'recentEquipment' => $recentEquipment,
            'recentIceCruiser' => $recentIceCruiser,
            'recentWater' => $recentWater,
        ]);
    }
}
