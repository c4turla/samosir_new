<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

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
            'syahbandars' => \App\Models\User::where('role', 'syahbandar')
                ->where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get vessels that are currently "at port" (ArrivalsCount > departuresCount)
        $vesselsAtPort = \App\Models\Vessel::whereHas('arrivals')
            ->where(function ($query) {
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
        $validated['approval_status'] = '0';
        $validated['is_processed'] = true;
        $validated['status'] = $validated['status'] ?? 'Sesuai Jadwal';

        $departure = Departure::create($validated);

        // Notify Users
        $departure->load('vessel');
        $vesselName = $departure->vessel ? $departure->vessel->vessel_name : 'Tidak Diketahui';

        $users = \App\Models\User::where('is_active', true)->get();
        foreach ($users as $user) {
            if ($user->role === 'syahbandar') {
                $user->notify(new \App\Notifications\DataInputNotification(
                    'Menunggu Approval',
                    "Data Keberangkatan Kapal {$vesselName} menunggu approval Anda.",
                    '/departures',
                    'warning'
                ));
            } else {
                $user->notify(new \App\Notifications\DataInputNotification(
                    'Keberangkatan Kapal',
                    "Data Keberangkatan Kapal {$vesselName} baru saja ditambahkan.",
                    '/departures',
                    'info'
                ));
            }
        }

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

                if ($departure->inputBy) {
                    $departure->load('vessel');
                    $vesselName = $departure->vessel ? $departure->vessel->vessel_name : 'Tidak Diketahui';
                    $departure->inputBy->notify(new \App\Notifications\DataInputNotification(
                        'Laporan Keberangkatan Disetujui',
                        "Laporan Keberangkatan Kapal {$vesselName} telah disetujui oleh petugas.",
                        '/departures',
                        'success'
                    ));
                }
            } else {
                $validated['approved_by'] = null;
                $validated['approved_at'] = null;

                if ($departure->inputBy) {
                    $departure->load('vessel');
                    $vesselName = $departure->vessel ? $departure->vessel->vessel_name : 'Tidak Diketahui';
                    $departure->inputBy->notify(new \App\Notifications\DataInputNotification(
                        'Laporan Keberangkatan Ditolak',
                        "Laporan Keberangkatan Kapal {$vesselName} ditolak oleh petugas.",
                        '/departures',
                        'danger'
                    ));
                }
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
     * Print the departure letter (STBLKK).
     */
    public function print(Departure $departure)
    {
        $departure->load(['vessel', 'landingSite', 'approvedBy']);

        // Find syahbandar user by name stored in the syahbandar field
        $syahbandarUser = null;
        if ($departure->approvedBy && $departure->approvedBy->role === 'syahbandar') {
            $syahbandarUser = $departure->approvedBy;
        } elseif ($departure->syahbandar) {
            $syahbandarUser = \App\Models\User::where('name', $departure->syahbandar)
                ->where('role', 'syahbandar')
                ->first();
        }

        $pdf = Pdf::loadView('departures.print', compact('departure', 'syahbandarUser'))
            ->setPaper('a4', 'portrait');

        $filename = 'stblkk_' . str_replace('/', '_', $departure->nomor) . '.pdf';

        return $pdf->download($filename);
    }

    public function approve(Request $request, Departure $departure)
    {
        $syahbandarName = $request->input('syahbandar');
        
        if (!$syahbandarName && auth()->user()->role === 'syahbandar') {
            $syahbandarName = auth()->user()->name;
        }

        if (!$syahbandarName) {
            return redirect()->back()->with('error', 'Nama Syahbandar wajib diisi.');
        }

        $departure->update([
            'approval_status' => '1',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'syahbandar' => $syahbandarName,
        ]);

        if ($departure->inputBy) {
            $departure->load('vessel');
            $vesselName = $departure->vessel ? $departure->vessel->vessel_name : 'Tidak Diketahui';
            $departure->inputBy->notify(new \App\Notifications\DataInputNotification(
                'Laporan Keberangkatan Disetujui',
                "Laporan Keberangkatan Kapal {$vesselName} telah disetujui oleh syahbandar.",
                '/departures',
                'success'
            ));
        }

        return redirect()->route('departures.index')
            ->with('success', 'Keberangkatan kapal berhasil disetujui.');
    }

    /**
     * Reject a departure.
     */
    public function reject(Departure $departure)
    {
        if (auth()->user()->role !== 'syahbandar') {
            return redirect()->route('departures.index')->with('error', 'Hanya Syahbandar yang dapat menolak laporan ini.');
        }

        $departure->update([
            'approval_status' => '0',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        if ($departure->inputBy) {
            $departure->load('vessel');
            $vesselName = $departure->vessel ? $departure->vessel->vessel_name : 'Tidak Diketahui';
            $departure->inputBy->notify(new \App\Notifications\DataInputNotification(
                'Laporan Keberangkatan Ditolak',
                "Laporan Keberangkatan Kapal {$vesselName} ditolak oleh syahbandar.",
                '/departures',
                'danger'
            ));
        }

        return redirect()->route('departures.index')
            ->with('success', 'Keberangkatan kapal berhasil ditolak.');
    }

    /**
     * Forward a departure to syahbandar for approval.
     */
    public function forward(Departure $departure)
    {
        $departure->update([
            'is_processed' => true,
        ]);

        $departure->load('vessel');
        $vesselName = $departure->vessel ? $departure->vessel->vessel_name : 'Tidak Diketahui';

        $users = \App\Models\User::where('role', 'syahbandar')->where('is_active', true)->get();
        foreach ($users as $user) {
            $user->notify(new \App\Notifications\DataInputNotification(
                'Menunggu Approval',
                "Data Keberangkatan Kapal {$vesselName} telah diperiksa oleh petugas dan menunggu approval Anda.",
                '/departures',
                'warning'
            ));
        }

        return redirect()->route('departures.index')
            ->with('success', 'Keberangkatan kapal berhasil diteruskan ke Syahbandar.');
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

        return sprintf(
            '%03d/PPNS-SKP/%s/%d',
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
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $map[$month] ?? 'I';
    }
}
