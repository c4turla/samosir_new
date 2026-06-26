<?php

namespace App\Http\Controllers;

use App\Models\SprDeparture;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SprDepartureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $sprDepartures = SprDeparture::query()
            ->with(['vessel', 'user'])
            ->when($search, function ($query, $search) {
                $query->whereHas('vessel', function ($q) use ($search) {
                    $q->where('vessel_name', 'like', "%{$search}%");
                })->orWhere('nakhoda_name', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();
            
        return Inertia::render('SprDepartures/Index', [
            'sprDepartures' => $sprDepartures,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SprDeparture $sprDeparture)
    {
        $sprDeparture->load(['vessel', 'user']);
        
        return Inertia::render('SprDepartures/Show', [
            'sprDeparture' => $sprDeparture
        ]);
    }
}
