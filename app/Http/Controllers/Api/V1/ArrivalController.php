<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Arrival;
use Illuminate\Http\Request;

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
