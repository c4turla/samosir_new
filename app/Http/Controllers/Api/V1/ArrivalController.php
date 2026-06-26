<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Arrival;
use App\Models\User;
use App\Notifications\DataInputNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArrivalController extends Controller
{
    /**
     * Display a listing of ship arrivals.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Arrival::with(['vessel', 'landingSite', 'catches.fishSpecies']);

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

        $arrivals = $query->latest('arrival_date')->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $arrivals
        ]);
    }

    /**
     * Store a newly created ship arrival in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vessel_id' => 'required|exists:vessels,id',
            'origin' => 'nullable|string|max:255',
            'arrival_date' => 'required|date',
            'arrival_time' => 'nullable|date_format:H:i',
            'landing_site_id' => 'nullable|exists:landing_sites,id',
            'mutu' => 'nullable|string|max:50',
            'fish_quality' => 'nullable|string|max:50',
            'average_price' => 'nullable|numeric|min:0',
            'waste_volume' => 'nullable|integer|min:0',
            'fish_temperature' => 'nullable|integer',
            'hold_temperature' => 'nullable|integer',
            'status' => 'required|in:TAMBAT,LABUH,BONGKAR,MENGISI PERBEKALAN,PERBAIKAN,SELESAI',
            'notes' => 'nullable|string',
            'is_processed' => 'nullable|boolean',
            'catches' => 'nullable|array',
            'catches.*.fish_species_id' => 'required_with:catches|exists:fish_species,id',
            'catches.*.weight_kg' => 'required_with:catches|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $validated['input_by'] = auth()->id();
        $validated['approval_status'] = '0';
        $validated['is_processed'] = false;

        $catchesData = $validated['catches'] ?? [];
        unset($validated['catches']);

        $arrival = Arrival::create($validated);

        if (!empty($catchesData)) {
            foreach ($catchesData as $catch) {
                $arrival->catches()->create([
                    'fish_species_id' => $catch['fish_species_id'],
                    'weight_kg' => $catch['weight_kg'],
                ]);
            }
        }

        // Notify Users
        $arrival->load('vessel');
        $vesselName = $arrival->vessel ? $arrival->vessel->vessel_name : 'Tidak Diketahui';

        $users = User::where('is_active', true)->get();
        foreach ($users as $user) {
            if ($user->role === 'syahbandar') {
                $user->notify(new DataInputNotification(
                    'Menunggu Approval',
                    "Data Kedatangan Kapal {$vesselName} menunggu approval Anda.",
                    '/arrivals',
                    'warning'
                ));
            } else {
                $user->notify(new DataInputNotification(
                    'Kedatangan Kapal',
                    "Data Kedatangan Kapal {$vesselName} baru saja ditambahkan.",
                    '/arrivals',
                    'info'
                ));
            }
        }

        $arrival->load(['landingSite', 'catches.fishSpecies']);

        return response()->json([
            'status' => 'success',
            'message' => 'Kedatangan kapal berhasil dicatat.',
            'data' => $arrival
        ], 201);
    }

    /**
     * Display the specified ship arrival.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $arrival = Arrival::with(['vessel', 'landingSite', 'catches.fishSpecies', 'unloading'])->findOrFail($id);

        // Security check for pengelola
        if ($user->role === 'pengelola') {
            $vesselIds = $user->vessels()->pluck('vessels.id')->toArray();
            if (!in_array($arrival->vessel_id, $vesselIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke data kedatangan ini.'
                ], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $arrival
        ]);
    }
}
