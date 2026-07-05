<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use App\Models\User;
use App\Notifications\DataInputNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartureController extends Controller
{
    /**
     * Display a listing of ship departures.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Departure::with(['vessel', 'landingSite']);

        // Filter based on role
        if ($user->role === 'pengelola') {
            $vesselIds = $user->vessels()->pluck('vessels.id');
            $query->whereIn('vessel_id', $vesselIds);
        }

        // Optional filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        $departures = $query->latest('departure_date')->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $departures
        ]);
    }

    /**
     * Store a newly created ship departure in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $validated['nomor'] = $this->generateNextNomor();
        $validated['input_by'] = auth()->id();
        $validated['approval_status'] = '0';
        $validated['is_processed'] = false;
        $validated['status'] = $validated['status'] ?? 'Sesuai Jadwal';

        $departure = Departure::create($validated);

        // Notify Users
        $departure->load('vessel');
        $vesselName = $departure->vessel ? $departure->vessel->vessel_name : 'Tidak Diketahui';

        $users = User::where('is_active', true)->get();
        foreach ($users as $user) {
            if ($user->role === 'syahbandar') {
                $user->notify(new DataInputNotification(
                    'Menunggu Approval',
                    "Data Keberangkatan Kapal {$vesselName} menunggu approval Anda.",
                    '/departures',
                    'warning'
                ));
            } else {
                $user->notify(new DataInputNotification(
                    'Keberangkatan Kapal',
                    "Data Keberangkatan Kapal {$vesselName} baru saja ditambahkan.",
                    '/departures',
                    'info'
                ));
            }
        }

        $departure->load('landingSite');

        return response()->json([
            'status' => 'success',
            'message' => 'Keberangkatan kapal berhasil dicatat.',
            'data' => $departure
        ], 201);
    }

    /**
     * Display the specified ship departure.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $departure = Departure::with(['vessel', 'landingSite'])->findOrFail($id);

        // Security check for pengelola
        if ($user->role === 'pengelola') {
            $vesselIds = $user->vessels()->pluck('vessels.id')->toArray();
            if (!in_array($departure->vessel_id, $vesselIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke data keberangkatan ini.'
                ], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $departure
        ]);
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
