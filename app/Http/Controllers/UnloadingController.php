<?php

namespace App\Http\Controllers;

use App\Models\Unloading;
use App\Models\Arrival;
use App\Models\LandingSite;
use App\Models\User;
use App\Notifications\DataInputNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnloadingController extends Controller
{
    public function index(Request $request)
    {
        $unloadings = Unloading::with(['arrival.vessel', 'landingSite', 'syahbandar'])
            ->filter(
                $request->search,
                $request->status,
                $request->date_from,
                $request->date_to
            )
            ->orderBy('unloading_date', 'desc')
            ->orderBy('unloading_time', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Unloadings/Index', [
            'unloadings' => $unloadings,
        ]);
    }

    public function create()
    {
        // Get arrivals that don't have unloading yet
        $arrivals = Arrival::with('vessel', 'landingSite')
            ->whereDoesntHave('unloading')
            ->where('status', 'SELESAI')
            ->get();

        $landingSites = LandingSite::all();
        
        // Get users with syahbandar role
        $syahbandars = \App\Models\User::where('role', 'syahbandar')->get();

        return Inertia::render('Unloadings/Create', [
            'arrivals' => $arrivals,
            'landingSites' => $landingSites,
            'syahbandars' => $syahbandars,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'arrival_id' => 'required|exists:arrivals,id',
            'reference_number' => 'nullable|string|max:100',
            'syahbandar_id' => 'required|exists:users,id',
            'captain_name' => 'nullable|string|max:100',
            'identification_mark' => 'nullable|string|max:100',
            'unloading_date' => 'required|date',
            'unloading_time' => 'nullable|date_format:H:i',
            'sequence_number' => 'nullable|string|max:50',
            'registration_date' => 'required|date',
            'bl_code' => 'nullable|string|max:255',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'notes' => 'nullable|string',
        ]);

        // Set approval status to pending by default
        $validated['approval_status'] = false;

        $unloading = Unloading::create($validated);

        // Notify Assigned Syahbandar
        $unloading->load('arrival.vessel');
        $vesselName = $unloading->arrival?->vessel?->vessel_name ?? 'Kapal';

        $syahbandar = User::find($unloading->syahbandar_id);
        if ($syahbandar) {
            $syahbandar->notify(new DataInputNotification(
                'Approval Penimbangan',
                "Kapal {$vesselName} memerlukan approval penimbangan dari Anda.",
                "/approval/{$unloading->id}",
                'warning'
            ));
        }

        return redirect()->route('unloadings.index')->with('success', 'Data penimbangan ikan berhasil ditambahkan');
    }

    public function show(Unloading $unloading)
    {
        $unloading->load(['arrival.vessel', 'landingSite']);

        return Inertia::render('Unloadings/Show', [
            'unloading' => $unloading,
        ]);
    }

    public function edit(Unloading $unloading)
    {
        $unloading->load(['arrival.vessel', 'landingSite', 'syahbandar']);

        $arrivals = Arrival::with('vessel', 'landingSite')
            ->where('status', 'SELESAI')
            ->get();

        $landingSites = LandingSite::all();
        
        // Get users with syahbandar role
        $syahbandars = \App\Models\User::where('role', 'syahbandar')->get();

        return Inertia::render('Unloadings/Edit', [
            'unloading' => $unloading,
            'arrivals' => $arrivals,
            'landingSites' => $landingSites,
            'syahbandars' => $syahbandars,
        ]);
    }

    public function update(Request $request, Unloading $unloading)
    {
        $validated = $request->validate([
            'arrival_id' => 'required|exists:arrivals,id',
            'reference_number' => 'nullable|string|max:100',
            'syahbandar_id' => 'required|exists:users,id',
            'captain_name' => 'nullable|string|max:100',
            'identification_mark' => 'nullable|string|max:100',
            'unloading_date' => 'required|date',
            'unloading_time' => 'nullable|date_format:H:i',
            'sequence_number' => 'nullable|string|max:50',
            'registration_date' => 'required|date',
            'bl_code' => 'nullable|string|max:255',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'notes' => 'nullable|string',
        ]);

        $unloading->update($validated);

        return redirect()->route('unloadings.index')->with('success', 'Data penimbangan ikan berhasil diperbarui');
    }

    public function destroy(Unloading $unloading)
    {
        $unloading->delete();

        return redirect()->route('unloadings.index')->with('success', 'Data penimbangan ikan berhasil dihapus');
    }

    public function print(Unloading $unloading)
    {
        $unloading->load(['arrival.vessel', 'landingSite', 'syahbandar']);
        
        $pdf = Pdf::loadView('reports.unloading-print', [
            'unloading' => $unloading,
            'today' => Carbon::now()->isoFormat('D-MMM-Y')
        ])->setPaper('a4', 'portrait');

        $filename = 'surat_penimbangan_' . str_replace('/', '_', $unloading->reference_number) . '.pdf';
        
        return $pdf->stream($filename);
    }
}
