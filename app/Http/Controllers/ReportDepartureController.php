<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\LandingSite;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportDepartureController extends Controller
{
    /**
     * Display the departure report page.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $landingSiteId = $request->input('landing_site_id');
        $search = $request->input('search');

        $query = Departure::query()
            ->with(['vessel', 'landingSite', 'inputBy'])
            ->when($search, function ($q, $search) {
                $q->whereHas('vessel', function ($q2) use ($search) {
                    $q2->where('vessel_name', 'like', "%{$search}%")
                       ->orWhere('license_number', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($landingSiteId, function ($q, $landingSiteId) {
                $q->where('landing_site_id', $landingSiteId);
            })
            ->whereDate('departure_date', '>=', $dateFrom)
            ->whereDate('departure_date', '<=', $dateTo)
            ->orderBy('departure_date', 'desc')
            ->orderBy('departure_time', 'desc');

        // Summary statistics
        $totalDepartures = (clone $query)->count();
        $totalCrews = (clone $query)->sum('crew_count');
        
        $arrivals = $query->paginate(15)->withQueryString();

        $landingSites = LandingSite::where('is_active', true)
            ->orderBy('site_name')
            ->get(['id', 'site_name']);

        return Inertia::render('Reports/Departures', [
            'departures' => $arrivals,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
                'landing_site_id' => $landingSiteId,
                'search' => $search,
            ],
            'landingSites' => $landingSites,
            'summary' => [
                'total' => $totalDepartures,
                'totalCrews' => $totalCrews,
            ],
        ]);
    }

    /**
     * Export departures data to Excel (CSV format).
     */
    public function exportExcel(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $landingSiteId = $request->input('landing_site_id');
        $search = $request->input('search');

        $departures = Departure::query()
            ->with(['vessel', 'landingSite', 'inputBy'])
            ->when($search, function ($q, $search) {
                $q->whereHas('vessel', function ($q2) use ($search) {
                    $q2->where('vessel_name', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($landingSiteId, function ($q, $landingSiteId) {
                $q->where('landing_site_id', $landingSiteId);
            })
            ->whereDate('departure_date', '>=', $dateFrom)
            ->whereDate('departure_date', '<=', $dateTo)
            ->orderBy('departure_date', 'desc')
            ->orderBy('departure_time', 'desc')
            ->get();

        $filename = 'laporan_keberangkatan_' . $dateFrom . '_sd_' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($departures) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'Nomor SKP',
                'Tanggal Masuk',
                'Tanggal Keluar',
                'Nama Kapal',
                'Nakhoda',
                'No. Izin',
                'Tujuan',
                'Etmal (Hari)',
                'Etmal (Jam)',
                'Jumlah ABK',
                'Floating',
                'Bongkar Ikan',
                'Penyelesaian Administrasi',
                'Syahbandar',
                'Status',
                'Input Oleh',
                'Catatan',
            ]);

            foreach ($departures as $index => $departure) {
                $arrivalDate = $departure->arrival_datetime 
                    ? Carbon::parse($departure->arrival_datetime)->format('d/m/Y H:i') 
                    : '-';

                $departureDate = $departure->departure_datetime 
                    ? Carbon::parse($departure->departure_datetime)->format('d/m/Y H:i') 
                    : ($departure->departure_date instanceof Carbon ? $departure->departure_date->format('d/m/Y') : Carbon::parse($departure->departure_date)->format('d/m/Y'));

                fputcsv($file, [
                    $index + 1,
                    $departure->nomor ?? '-',
                    $arrivalDate,
                    $departureDate,
                    $departure->vessel->vessel_name ?? '-',
                    $departure->nakhoda_name ?? '-',
                    $departure->vessel->license_number ?? '-',
                    $departure->destination ?? '-',
                    $departure->etmal_days ?? 0,
                    $departure->etmal_hours ?? 0,
                    $departure->crew_count ?? 0,
                    $departure->floating_status ?? '-',
                    $departure->unloading_status ?? '-',
                    $departure->admin_completion ?? '-',
                    $departure->syahbandar ?? '-',
                    $departure->status ?? '-',
                    $departure->inputBy->name ?? '-',
                    $departure->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export departures data to PDF.
     */
    public function exportPdf(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $landingSiteId = $request->input('landing_site_id');
        $search = $request->input('search');

        $departures = Departure::query()
            ->with(['vessel', 'landingSite', 'inputBy'])
            ->when($search, function ($q, $search) {
                $q->whereHas('vessel', function ($q2) use ($search) {
                    $q2->where('vessel_name', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($landingSiteId, function ($q, $landingSiteId) {
                $q->where('landing_site_id', $landingSiteId);
            })
            ->whereDate('departure_date', '>=', $dateFrom)
            ->whereDate('departure_date', '<=', $dateTo)
            ->orderBy('departure_date', 'desc')
            ->orderBy('departure_time', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.departures-pdf', [
            'departures' => $departures,
            'dateFrom' => Carbon::parse($dateFrom)->format('d/m/Y'),
            'dateTo' => Carbon::parse($dateTo)->format('d/m/Y'),
            'status' => $status,
            'total' => $departures->count(),
            'totalCrews' => $departures->sum('crew_count'),
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan_keberangkatan_' . $dateFrom . '_sd_' . $dateTo . '.pdf';

        return $pdf->download($filename);
    }
}
