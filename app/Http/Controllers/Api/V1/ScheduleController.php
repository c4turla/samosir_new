<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Arrival;
use App\Models\Departure;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of ship arrivals and departures (general schedules).
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $perPage = (int) $request->get('per_page', 15);

        $data = [];

        // Build Arrival Query
        if ($type === 'all' || $type === 'arrivals') {
            $arrivalQuery = Arrival::with(['vessel', 'landingSite']);

            if ($search) {
                $arrivalQuery->where(function ($q) use ($search) {
                    $q->whereHas('vessel', function ($qv) use ($search) {
                        $qv->where('vessel_name', 'like', "%{$search}%")
                           ->orWhere('license_number', 'like', "%{$search}%");
                    })->orWhereHas('landingSite', function ($ql) use ($search) {
                        $ql->where('site_name', 'like', "%{$search}%");
                    });
                });
            }

            if ($dateFrom) {
                $arrivalQuery->whereDate('arrival_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $arrivalQuery->whereDate('arrival_date', '<=', $dateTo);
            }

            $arrivals = $arrivalQuery->latest('arrival_date')
                ->orderBy('arrival_time', 'desc')
                ->paginate($perPage, ['*'], 'arrivals_page')
                ->withQueryString();

            if ($type === 'arrivals') {
                return response()->json([
                    'status' => 'success',
                    'data' => $arrivals
                ]);
            }

            $data['arrivals'] = $arrivals;
        }

        // Build Departure Query
        if ($type === 'all' || $type === 'departures') {
            $departureQuery = Departure::with(['vessel', 'landingSite']);

            if ($search) {
                $departureQuery->where(function ($q) use ($search) {
                    $q->whereHas('vessel', function ($qv) use ($search) {
                        $qv->where('vessel_name', 'like', "%{$search}%")
                           ->orWhere('license_number', 'like', "%{$search}%");
                    })->orWhereHas('landingSite', function ($ql) use ($search) {
                        $ql->where('site_name', 'like', "%{$search}%");
                    });
                });
            }

            if ($dateFrom) {
                $departureQuery->whereDate('departure_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $departureQuery->whereDate('departure_date', '<=', $dateTo);
            }

            $departures = $departureQuery->latest('departure_date')
                ->orderBy('departure_time', 'desc')
                ->paginate($perPage, ['*'], 'departures_page')
                ->withQueryString();

            if ($type === 'departures') {
                return response()->json([
                    'status' => 'success',
                    'data' => $departures
                ]);
            }

            $data['departures'] = $departures;
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
