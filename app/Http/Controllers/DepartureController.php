<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        $departures = Departure::query()
            ->with(['vessel', 'landingSite', 'inputBy', 'approvedBy'])
            ->when($search, function ($query, $search) {
                $query->whereHas('vessel', function ($q) use ($search) {
                    $q->where('vessel_name', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%");
                })->orWhere('destination', 'like', "%{$search}%");
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate('departure_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate('departure_date', '<=', $dateTo);
            })
            ->orderBy('departure_date', 'desc')
            ->orderBy('departure_time', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        return Inertia::render('Departures/Index', [
            'departures' => $departures,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get vessels that have arrivals (ships that have arrived)
        $vesselsWithArrivals = \App\Models\Vessel::whereHas('arrivals')
            ->select('id', 'vessel_name', 'license_number')
            ->orderBy('vessel_name')
            ->get();

        return Inertia::render('Departures/Create', [
            'vessels' => $vesselsWithArrivals,
            'landingSites' => \App\Models\LandingSite::where('is_active', true)
                ->select('id', 'site_name')
                ->orderBy('site_name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vessel_id' => 'required|exists:vessels,id',
            'destination' => 'required|string|max:255',
            'crew_count' => 'nullable|integer|min:0',
            'departure_date' => 'required|date',
            'departure_time' => 'nullable|date_format:H:i:s',
            'departure_datetime' => 'nullable|date',
            'return_datetime' => 'nullable|date|after:departure_datetime',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'syahbandar' => 'nullable|string|max:255',
            'administrative_officer' => 'nullable|string|max:255',
            'ice_supply' => 'nullable|integer|min:0',
            'water_supply' => 'nullable|integer|min:0',
            'diesel_supply' => 'nullable|integer|min:0',
            'oil_supply' => 'nullable|integer|min:0',
            'gasoline_supply' => 'nullable|integer|min:0',
            'other_supplies' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'approval_status' => 'nullable|boolean',
            'signature' => 'nullable|string',
        ]);

        $validated['input_by'] = auth()->id();
        $validated['approval_status'] = $validated['approval_status'] ?? false;
        $validated['status'] = $validated['status'] ?? 'MENUNGGU';

        $departure = Departure::create($validated);

        return redirect()->route('departures.index')
            ->with('success', 'Keberangkatan kapal berhasil dicatat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Departure $departure)
    {
        // Get vessels that have arrivals (ships that have arrived)
        $vesselsWithArrivals = \App\Models\Vessel::whereHas('arrivals')
            ->select('id', 'vessel_name', 'license_number')
            ->orderBy('vessel_name')
            ->get();

        return Inertia::render('Departures/Edit', [
            'departure' => $departure,
            'vessels' => $vesselsWithArrivals,
            'landingSites' => \App\Models\LandingSite::where('is_active', true)
                ->select('id', 'site_name')
                ->orderBy('site_name')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Departure $departure)
    {
        $validated = $request->validate([
            'vessel_id' => 'required|exists:vessels,id',
            'destination' => 'required|string|max:255',
            'crew_count' => 'nullable|integer|min:0',
            'departure_date' => 'required|date',
            'departure_time' => 'nullable|date_format:H:i:s',
            'departure_datetime' => 'nullable|date',
            'return_datetime' => 'nullable|date|after:departure_datetime',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'syahbandar' => 'nullable|string|max:255',
            'administrative_officer' => 'nullable|string|max:255',
            'ice_supply' => 'nullable|integer|min:0',
            'water_supply' => 'nullable|integer|min:0',
            'diesel_supply' => 'nullable|integer|min:0',
            'oil_supply' => 'nullable|integer|min:0',
            'gasoline_supply' => 'nullable|integer|min:0',
            'other_supplies' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'approval_status' => 'nullable|boolean',
            'signature' => 'nullable|string',
        ]);

        // Handle approval status changes
        if ($departure->approval_status !== $request->approval_status) {
            if ($request->approval_status) {
                $validated['approved_by'] = auth()->id();
                $validated['approved_at'] = now();
            } else {
                $validated['approved_by'] = null;
                $validated['approved_at'] = null;
            }
        }

        $departure->update($validated);

        return redirect()->route('departures.index')
            ->with('success', 'Data keberangkatan kapal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Departure $departure)
    {
        $departure->delete();

        return redirect()->route('departures.index')
            ->with('success', 'Data keberangkatan kapal berhasil dihapus.');
    }

    /**
     * Approve a departure.
     */
    public function approve(Departure $departure)
    {
        $departure->update([
            'approval_status' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('departures.index')
            ->with('success', 'Keberangkatan kapal berhasil disetujui.');
    }

    /**
     * Reject a departure.
     */
    public function reject(Departure $departure)
    {
        $departure->update([
            'approval_status' => false,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('departures.index')
            ->with('success', 'Keberangkatan kapal berhasil ditolak.');
    }
}
