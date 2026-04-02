<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vessel;
use App\Models\User;
use App\Models\Arrival;
use App\Models\Departure;
use Carbon\Carbon;

class DashboardController extends Controller{
    /**
     * Display dashboard.
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

        // Get statistics data for different filters
        $monthlyStats = $this->getMonthlyStatistics();
        $weeklyStats = $this->getWeeklyStatistics();
        $yearlyStats = $this->getYearlyStatistics();

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
            'weeklyStats' => $weeklyStats,
            'yearlyStats' => $yearlyStats,
            'vesselStatusDistribution' => $vesselStatusDistribution,
            'arrivalStatusDistribution' => $arrivalStatusDistribution,
            'topLandingSites' => $topLandingSites,
        ]);
    }

    /**
     * Get weekly statistics for current week.
     */
    private function getWeeklyStatistics()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Get arrivals by day for current week
        $arrivals = Arrival::select(
            DB::raw('DATE(arrival_date) as date'),
            DB::raw('count(*) as count')
        )
            ->whereBetween('arrival_date', [$startOfWeek, $endOfWeek])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Get departures by day for current week
        $departures = Departure::select(
            DB::raw('DATE(departure_date) as date'),
            DB::raw('count(*) as count')
        )
            ->whereBetween('departure_date', [$startOfWeek, $endOfWeek])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Create array for each day of the week
        $days = [];
        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');
            
            $days[] = [
                'day' => $i + 1,
                'day_name' => $dayNames[$i],
                'date' => $dateString,
                'arrivals' => $arrivals->get($dateString, 0),
                'departures' => $departures->get($dateString, 0),
            ];
        }

        return $days;
    }

    /**
     * Get monthly statistics for current year with Indonesian month names.
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

        // Indonesian month names
        $monthNames = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'month' => $i,
                'month_name' => $monthNames[$i - 1],
                'arrivals' => $arrivals->get($i, 0),
                'departures' => $departures->get($i, 0),
            ];
        }

        return $months;
    }

    /**
     * Get yearly statistics for last 5 years.
     */
    private function getYearlyStatistics()
    {
        $currentYear = Carbon::now()->year;
        $startYear = $currentYear - 4; // Last 5 years including current

        $arrivals = Arrival::select(
            DB::raw('YEAR(arrival_date) as year'),
            DB::raw('count(*) as count')
        )
            ->whereBetween(DB::raw('YEAR(arrival_date)'), [$startYear, $currentYear])
            ->groupBy('year')
            ->orderBy('year')
            ->pluck('count', 'year');

        $departures = Departure::select(
            DB::raw('YEAR(departure_date) as year'),
            DB::raw('count(*) as count')
        )
            ->whereBetween(DB::raw('YEAR(departure_date)'), [$startYear, $currentYear])
            ->groupBy('year')
            ->orderBy('year')
            ->pluck('count', 'year');

        $years = [];
        for ($i = $startYear; $i <= $currentYear; $i++) {
            $years[] = [
                'year' => $i,
                'arrivals' => $arrivals->get($i, 0),
                'departures' => $departures->get($i, 0),
            ];
        }

        return $years;
    }
}
