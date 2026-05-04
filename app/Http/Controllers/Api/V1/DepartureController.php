<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use Illuminate\Http\Request;

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
}
