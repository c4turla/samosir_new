<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SprDeparture;
use App\Models\User;
use App\Notifications\DataInputNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SprDepartureController extends Controller
{
    /**
     * Display a listing of SPR departures.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = SprDeparture::with(['vessel', 'user']);

        // Filter based on role
        if ($user->role === 'pengelola') {
            $query->where('user_id', $user->id);
        }

        // Optional filter status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sprDepartures = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $sprDepartures
        ]);
    }

    /**
     * Store a newly created SPR departure request in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vessel_id' => 'required|exists:vessels,id',
            'nakhoda_name' => 'required|string|max:100',
            'muatan' => 'nullable|string|max:255',
            'planned_departure_datetime' => 'required|date',
            'additional_notes' => 'nullable|string',
            'cp_arrival_date' => 'nullable|date',
            'cp_arrival_stbl' => 'nullable|string|max:100',
            'cp_departure_date' => 'nullable|date',
            'cp_departure_stbl' => 'nullable|string|max:100',
            'physical_arrival_date' => 'nullable|date',
            'physical_arrival_stbl' => 'nullable|string|max:100',
            'physical_departure_date' => 'nullable|date',
            'physical_departure_stbl' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $spr = SprDeparture::create([
            'vessel_id' => $request->vessel_id,
            'user_id' => $request->user()->id,
            'nakhoda_name' => $request->nakhoda_name,
            'muatan' => $request->muatan,
            'planned_departure_datetime' => $request->planned_departure_datetime,
            'additional_notes' => $request->additional_notes,
            'cp_arrival_date' => $request->cp_arrival_date,
            'cp_arrival_stbl' => $request->cp_arrival_stbl,
            'cp_departure_date' => $request->cp_departure_date,
            'cp_departure_stbl' => $request->cp_departure_stbl,
            'physical_arrival_date' => $request->physical_arrival_date,
            'physical_arrival_stbl' => $request->physical_arrival_stbl,
            'physical_departure_date' => $request->physical_departure_date,
            'physical_departure_stbl' => $request->physical_departure_stbl,
            'status' => 'pending'
        ]);

        // Notify
        $spr->load('vessel');
        $vesselName = $spr->vessel ? $spr->vessel->vessel_name : 'Tidak Diketahui';

        // Notify Syahbandar & Others
        $users = User::where('is_active', true)->get();
        foreach ($users as $user) {
            if (in_array($user->role, ['syahbandar', 'petugas'])) {
                $user->notify(new DataInputNotification(
                    'Pemberitahuan SPR Keberangkatan',
                    "Pemberitahuan SPR Keberangkatan Kapal {$vesselName} baru saja ditambahkan oleh Pengelola.",
                    '/spr-departures',
                    'info'
                ));
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Permohonan SPR Keberangkatan berhasil diajukan.',
            'data' => $spr
        ], 201);
    }
}
