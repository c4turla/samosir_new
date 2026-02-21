<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vessel;
use App\Models\User;
use App\Models\Arrival;
use App\Models\Departure;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Statistics cards
        $stats = [
            'total_vessels' => Vessel::count(),
            'active_vessels' => Vessel::where('approval_status', 'approved')->count(),
            'pending_vessels' => Vessel::where('approval_status', 'pending')->count(),
            'total_users' => User::active()->count(),
            'arrivals_today' => Arrival::whereDate('arrival_date', $today)->count(),
            'departures_today' => Departure::whereDate('departure_date', $today)->count(),
            'vessels_at_port' => Arrival::where('status', 'TAMBAT')->count(),
            'vessels_unloading' => Arrival::where('status', 'BONGKAR')->count(),
        ];

        // Recent arrivals (last 10)
        $recentArrivals = Arrival::with(['vessel', 'landingSite'])
            ->orderBy('arrival_date', 'desc')
            ->orderBy('arrival_time', 'desc')
            ->limit(10)
            ->get();

        // Recent departures (last 10)
        $recentDepartures = Departure::with(['vessel', 'landingSite'])
            ->orderBy('departure_date', 'desc')
            ->orderBy('departure_time', 'desc')
            ->limit(10)
            ->get();

        // Pending vessel registrations
        $pendingVessels = Vessel::where('approval_status', 'pending')
            ->with(['registeredBy', 'managers'])
            ->latest()
            ->limit(5)
            ->get();

        // Monthly statistics chart data
        $monthlyStats = $this->getMonthlyStatistics();

        // Vessel status distribution
        $vesselStatusDistribution = [
            'approved' => Vessel::where('approval_status', 'approved')->count(),
            'pending' => Vessel::where('approval_status', 'pending')->count(),
            'rejected' => Vessel::where('approval_status', 'rejected')->count(),
        ];

        // Arrival status distribution
        $arrivalStatusDistribution = [
            'tambat' => Arrival::where('status', 'TAMBAT')->count(),
            'bongkar' => Arrival::where('status', 'BONGKAR')->count(),
            'selesai' => Arrival::where('status', 'SELESAI')->count(),
        ];

        // Top landing sites by arrivals
        $topLandingSites = Arrival::select('landing_site_id', DB::raw('count(*) as count'))
            ->with('landingSite')
            ->groupBy('landing_site_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return inertia('Dashboard/Index', [
            'stats' => $stats,
            'recentArrivals' => $recentArrivals,
            'recentDepartures' => $recentDepartures,
            'pendingVessels' => $pendingVessels,
            'monthlyStats' => $monthlyStats,
            'vesselStatusDistribution' => $vesselStatusDistribution,
            'arrivalStatusDistribution' => $arrivalStatusDistribution,
            'topLandingSites' => $topLandingSites,
        ]);
    }

    /**
     * Get monthly statistics for the current year.
     */
    private function getMonthlyStatistics()
    {
        $currentYear = Carbon::now()->year;

        $arrivals = Arrival::select(
            DB::raw('MONTH(arrival_date) as month'),
            DB::raw('count(*) as count')
        )
            ->whereYear('arrival_date', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $departures = Departure::select(
            DB::raw('MONTH(departure_date) as month'),
            DB::raw('count(*) as count')
        )
            ->whereYear('departure_date', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'month' => $i,
                'month_name' => Carbon::create()->month($i)->format('M'),
                'arrivals' => $arrivals->get($i, 0),
                'departures' => $departures->get($i, 0),
            ];
        }

        return $months;
    }
}