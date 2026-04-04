<?php

namespace App\Http\Controllers;

use App\Models\Arrival;
use App\Models\LandingSite;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportArrivalController extends Controller
{
    /**
     * Display the arrival report page.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $landingSiteId = $request->input('landing_site_id');
        $search = $request->input('search');

        $query = Arrival::query()
            ->with(['vessel', 'landingSite', 'inputBy', 'catches.fishSpecies'])
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
            ->whereDate('arrival_date', '>=', $dateFrom)
            ->whereDate('arrival_date', '<=', $dateTo)
            ->orderBy('arrival_date', 'desc')
            ->orderBy('arrival_time', 'desc');

        // Summary statistics
        $totalArrivals = (clone $query)->count();
        $statusCounts = [
            'BERLABUH' => (clone $query)->where('status', 'BERLABUH')->count(),
            'BONGKAR' => (clone $query)->where('status', 'BONGKAR')->count(),
            'SELESAI' => (clone $query)->where('status', 'SELESAI')->count(),
        ];

        $arrivals = $query->paginate(15)->withQueryString();

        $landingSites = LandingSite::where('is_active', true)
            ->orderBy('site_name')
            ->get(['id', 'site_name']);

        return Inertia::render('Reports/Arrivals', [
            'arrivals' => $arrivals,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
                'landing_site_id' => $landingSiteId,
                'search' => $search,
            ],
            'landingSites' => $landingSites,
            'summary' => [
                'total' => $totalArrivals,
                'statusCounts' => $statusCounts,
            ],
        ]);
    }

    /**
     * Export arrivals data to Excel (CSV format).
     */
    public function exportExcel(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $landingSiteId = $request->input('landing_site_id');
        $search = $request->input('search');

        $arrivals = Arrival::query()
            ->with(['vessel', 'landingSite', 'inputBy', 'catches.fishSpecies'])
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
            ->whereDate('arrival_date', '>=', $dateFrom)
            ->whereDate('arrival_date', '<=', $dateTo)
            ->orderBy('arrival_date', 'desc')
            ->orderBy('arrival_time', 'desc')
            ->get();

        $filename = 'laporan_kedatangan_' . $dateFrom . '_sd_' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($arrivals) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'Tanggal Kedatangan',
                'Waktu',
                'Nama Kapal',
                'No. Izin',
                'Asal',
                'Lokasi Pendaratan',
                'Mutu Ikan',
                'Kualitas Ikan',
                'Harga Rata-rata',
                'Volume Limbah (kg)',
                'Suhu Ikan (°C)',
                'Suhu Palka (°C)',
                'Detail Ikan (Jenis - Berat - Nilai)',
                'Total Berat Ikan (kg)',
                'Total Nilai Ikan',
                'Status',
                'Input Oleh',
                'Catatan',
            ]);

            foreach ($arrivals as $index => $arrival) {
                $arrivalDate = $arrival->arrival_date instanceof Carbon
                    ? $arrival->arrival_date->format('d/m/Y')
                    : Carbon::parse($arrival->arrival_date)->format('d/m/Y');

                $arrivalTime = $arrival->arrival_time instanceof Carbon
                    ? $arrival->arrival_time->format('H:i')
                    : $arrival->arrival_time;

                $fishDetails = $arrival->catches->map(function ($c) {
                    $name = $c->fishSpecies->species_name ?? $c->fishSpecies->local_name ?? '-';
                    return $name . ': ' . number_format($c->weight_kg) . 'kg (Rp ' . number_format($c->estimated_value, 0, ',', '.') . ')';
                })->implode('; ');

                $totalWeight = $arrival->catches->sum('weight_kg');
                $totalValue = $arrival->catches->sum('estimated_value');

                fputcsv($file, [
                    $index + 1,
                    $arrivalDate,
                    $arrivalTime,
                    $arrival->vessel->vessel_name ?? '-',
                    $arrival->vessel->license_number ?? '-',
                    $arrival->origin ?? '-',
                    $arrival->landingSite->site_name ?? '-',
                    $arrival->mutu ?? '-',
                    $arrival->fish_quality ?? '-',
                    number_format($arrival->average_price ?? 0, 2, ',', '.'),
                    $arrival->waste_volume ?? 0,
                    $arrival->fish_temperature ?? 0,
                    $arrival->hold_temperature ?? 0,
                    $fishDetails ?: 'Tidak ada',
                    $totalWeight,
                    number_format($totalValue, 0, ',', '.'),
                    $arrival->status ?? '-',
                    $arrival->inputBy->name ?? '-',
                    $arrival->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export arrivals data to PDF.
     */
    public function exportPdf(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $landingSiteId = $request->input('landing_site_id');
        $search = $request->input('search');

        $arrivals = Arrival::query()
            ->with(['vessel', 'landingSite', 'inputBy', 'catches.fishSpecies'])
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
            ->whereDate('arrival_date', '>=', $dateFrom)
            ->whereDate('arrival_date', '<=', $dateTo)
            ->orderBy('arrival_date', 'desc')
            ->orderBy('arrival_time', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.arrivals-pdf', [
            'arrivals' => $arrivals,
            'dateFrom' => Carbon::parse($dateFrom)->format('d/m/Y'),
            'dateTo' => Carbon::parse($dateTo)->format('d/m/Y'),
            'status' => $status,
            'total' => $arrivals->count(),
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan_kedatangan_' . $dateFrom . '_sd_' . $dateTo . '.pdf';

        return $pdf->download($filename);
    }
}
