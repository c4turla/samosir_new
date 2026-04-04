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
        // Get vessels that are currently "at port" (ArrivalsCount > departuresCount)
        $vesselsAtPort = \App\Models\Vessel::whereHas('arrivals')
            ->where(function($query) {
                $query->whereRaw('(SELECT COUNT(*) FROM arrivals WHERE arrivals.vessel_id = vessels.id) > (SELECT COUNT(*) FROM departures WHERE departures.vessel_id = vessels.id AND departures.deleted_at IS NULL)');
            })
            ->select('id', 'vessel_name', 'license_number')
            ->orderBy('vessel_name')
            ->get();

        return Inertia::render('Departures/Create', [
            'vessels' => $vesselsAtPort,
            'next_nomor' => $this->generateNextNomor(),
            'landingSites' => \App\Models\LandingSite::where('is_active', true)
                ->select('id', 'site_name')
                ->orderBy('site_name')
                ->get(),
            'syahbandars' => \App\Models\User::where('role', 'syahbandar')
                ->where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
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
            'nakhoda_name' => 'nullable|string|max:100',
            'destination' => 'required|string|max:255',
            'crew_count' => 'nullable|integer|min:0',
            'arrival_datetime' => 'required|date',
            'departure_date' => 'required|date',
            'departure_time' => 'nullable|date_format:H:i',
            'departure_datetime' => 'nullable|date',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'syahbandar' => 'nullable|string|max:255',
            'ice_supply' => 'nullable|integer|min:0',
            'water_supply' => 'nullable|integer|min:0',
            'diesel_supply' => 'nullable|integer|min:0',
            'oil_supply' => 'nullable|integer|min:0',
            'gasoline_supply' => 'nullable|integer|min:0',
            'other_supplies' => 'nullable|string',
            'notes' => 'nullable|string',
            'floating_status' => 'nullable|string|max:100',
            'unloading_status' => 'nullable|string|max:100',
            'admin_completion' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'etmal_days' => 'nullable|numeric',
            'etmal_hours' => 'nullable|string|max:100',
            'approval_status' => 'nullable|boolean',
            'signature' => 'nullable|string',
        ]);

        // Generate Nomor
        $validated['nomor'] = $this->generateNextNomor();

        $validated['input_by'] = auth()->id();
        $validated['approval_status'] = 1;
        $validated['status'] = $validated['status'] ?? 'Sesuai Jadwal';

        $departure = Departure::create($validated);

        return redirect()->route('departures.index')
            ->with('success', 'Keberangkatan kapal berhasil dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(\Illuminate\Http\Request $request, Departure $departure)
    {
        $departure->load(['vessel', 'landingSite']);
        
        if (($request->wantsJson() || $request->ajax()) && !$request->header('X-Inertia')) {
            return response()->json($departure);
        }
        
        return Inertia::render('Departures/Show', [
            'departure' => $departure
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Departure $departure)
    {
        // For Edit, we only show the current vessel since it cannot be changed (read-only)
        $selectedVessel = \App\Models\Vessel::where('id', $departure->vessel_id)
            ->select('id', 'vessel_name', 'license_number')
            ->get();

        return Inertia::render('Departures/Edit', [
            'departure' => $departure,
            'vessels' => $selectedVessel,
            'landingSites' => \App\Models\LandingSite::where('is_active', true)
                ->select('id', 'site_name')
                ->orderBy('site_name')
                ->get(),
            'syahbandars' => \App\Models\User::where('role', 'syahbandar')
                ->where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
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
            'nakhoda_name' => 'nullable|string|max:100',
            'destination' => 'required|string|max:255',
            'crew_count' => 'nullable|integer|min:0',
            'arrival_datetime' => 'required|date',
            'departure_date' => 'required|date',
            'departure_time' => 'nullable|date_format:H:i',
            'departure_datetime' => 'nullable|date',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'syahbandar' => 'nullable|string|max:255',
            'ice_supply' => 'nullable|integer|min:0',
            'water_supply' => 'nullable|integer|min:0',
            'diesel_supply' => 'nullable|integer|min:0',
            'oil_supply' => 'nullable|integer|min:0',
            'gasoline_supply' => 'nullable|integer|min:0',
            'other_supplies' => 'nullable|string',
            'notes' => 'nullable|string',
            'floating_status' => 'nullable|string|max:100',
            'unloading_status' => 'nullable|string|max:100',
            'admin_completion' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'etmal_days' => 'nullable|numeric',
            'etmal_hours' => 'nullable|string|max:100',
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

    /**
     * Generate the next departure number.
     */
    private function generateNextNomor()
    {
        $lastNomor = Departure::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextSeq = 1;
        if ($lastNomor && preg_match('/^(\d+)/', $lastNomor->nomor, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        }
        
        return sprintf('%03d/PPNS-SKP/%s/%d', 
            $nextSeq, 
            $this->getRomanMonth(now()->month), 
            now()->year
        );
    }

    /**
     * Get Roman representation of a month.
     */
    private function getRomanMonth($month)
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$month] ?? 'I';
    }
}
