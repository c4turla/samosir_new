<?php

namespace App\Http\Controllers;

use App\Models\Arrival;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArrivalController extends Controller
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
        
        $arrivals = Arrival::query()
            ->with(['vessel', 'landingSite', 'inputBy', 'approvedBy'])
            ->when($search, function ($query, $search) {
                $query->whereHas('vessel', function ($q) use ($search) {
                    $q->where('vessel_name', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%");
                })->orWhereHas('landingSite', function ($q) use ($search) {
                    $q->where('site_name', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate('arrival_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate('arrival_date', '<=', $dateTo);
            })
            ->orderBy('arrival_date', 'desc')
            ->orderBy('arrival_time', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        return Inertia::render('Arrivals/Index', [
            'arrivals' => $arrivals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Arrivals/Create', [
            'vessels' => \App\Models\Vessel::where('approval_status', false)
                ->select('id', 'vessel_name', 'license_number')
                ->orderBy('vessel_name')
                ->get(),
            'landingSites' => \App\Models\LandingSite::where('is_active', true)
                ->select('id', 'site_name')
                ->orderBy('site_name')
                ->get(),
            'fishSpecies' => \App\Models\FishSpecies::where('is_active', true)
                ->select('id', 'species_name')
                ->orderBy('species_name')
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
            'origin' => 'nullable|string|max:255',
            'arrival_date' => 'required|date',
            'arrival_time' => 'nullable|date_format:H:i:s',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'fish_quality' => 'nullable|string|max:50',
            'average_price' => 'nullable|numeric|min:0',
            'waste_volume' => 'nullable|integer|min:0',
            'fish_temperature' => 'nullable|integer',
            'hold_temperature' => 'nullable|integer',
            'status' => 'required|in:TAMBAT,BONGKAR,SELESAI',
            'approval_status' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'is_processed' => 'nullable|boolean',
            'catches' => 'nullable|array',
            'catches.*.fish_species_id' => 'required_with:catches|exists:fish_species,id',
            'catches.*.quantity' => 'required_with:catches|integer|min:1',
            'catches.*.weight' => 'required_with:catches|numeric|min:0',
        ]);

        $validated['input_by'] = auth()->id();
        $validated['approval_status'] = $validated['approval_status'] ?? false;

        $arrival = Arrival::create($validated);

        // Save fish catches
        if (isset($validated['catches']) && is_array($validated['catches'])) {
            foreach ($validated['catches'] as $catch) {
                $arrival->catches()->create($catch);
            }
        }

        return redirect()->route('arrivals.index')
            ->with('success', 'Kedatangan kapal berhasil dicatat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Arrival $arrival)
    {
        $arrival->load(['vessel', 'landingSite', 'catches.fishSpecies']);
        return Inertia::render('Arrivals/Edit', [
            'arrival' => $arrival,
            'vessels' => \App\Models\Vessel::where('approval_status', true)
                ->select('id', 'vessel_name', 'license_number')
                ->orderBy('vessel_name')
                ->get(),
            'landingSites' => \App\Models\LandingSite::where('is_active', true)
                ->select('id', 'site_name')
                ->orderBy('site_name')
                ->get(),
            'fishSpecies' => \App\Models\FishSpecies::where('is_active', true)
                ->select('id', 'species_name')
                ->orderBy('species_name')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Arrival $arrival)
    {
        $validated = $request->validate([
            'vessel_id' => 'required|exists:vessels,id',
            'origin' => 'nullable|string|max:255',
            'arrival_date' => 'required|date',
            'arrival_time' => 'nullable|date_format:H:i:s',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'fish_quality' => 'nullable|string|max:50',
            'average_price' => 'nullable|numeric|min:0',
            'waste_volume' => 'nullable|integer|min:0',
            'fish_temperature' => 'nullable|integer',
            'hold_temperature' => 'nullable|integer',
            'status' => 'required|in:TAMBAT,BONGKAR,SELESAI',
            'approval_status' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'is_processed' => 'nullable|boolean',
            'catches' => 'nullable|array',
            'catches.*.fish_species_id' => 'required_with:catches|exists:fish_species,id',
            'catches.*.quantity' => 'required_with:catches|integer|min:1',
            'catches.*.weight' => 'required_with:catches|numeric|min:0',
        ]);

        // Handle approval status changes
        if ($arrival->approval_status !== $request->approval_status) {
            if ($request->approval_status) {
                $validated['approved_by'] = auth()->id();
                $validated['approved_at'] = now();
            } else {
                $validated['approved_by'] = null;
                $validated['approved_at'] = null;
            }
        }

        $arrival->update($validated);

        // Update fish catches - delete all and recreate
        $arrival->catches()->delete();
        if (isset($validated['catches']) && is_array($validated['catches'])) {
            foreach ($validated['catches'] as $catch) {
                $arrival->catches()->create($catch);
            }
        }

        return redirect()->route('arrivals.index')
            ->with('success', 'Data kedatangan kapal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Arrival $arrival)
    {
        $arrival->delete();

        return redirect()->route('arrivals.index')
            ->with('success', 'Data kedatangan kapal berhasil dihapus.');
    }

    /**
     * Approve an arrival.
     */
    public function approve(Arrival $arrival)
    {
        $arrival->update([
            'approval_status' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('arrivals.index')
            ->with('success', 'Kedatangan kapal berhasil disetujui.');
    }

    /**
     * Reject an arrival.
     */
    public function reject(Arrival $arrival)
    {
        $arrival->update([
            'approval_status' => false,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('arrivals.index')
            ->with('success', 'Kedatangan kapal berhasil ditolak.');
    }
}