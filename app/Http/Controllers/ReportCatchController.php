<?php

namespace App\Http\Controllers;

use App\Models\ArrivalCatch;
use App\Models\FishSpecies;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportCatchController extends Controller
{
    /**
     * Display the catch report page.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $fishSpeciesId = $request->input('fish_species_id');

        $query = ArrivalCatch::query()
            ->with(['arrival.vessel', 'fishSpecies'])
            ->whereHas('arrival', function ($q) use ($dateFrom, $dateTo) {
                $q->whereDate('arrival_date', '>=', $dateFrom)
                  ->whereDate('arrival_date', '<=', $dateTo);
            })
            ->when($fishSpeciesId, function ($q, $fishSpeciesId) {
                $q->where('fish_species_id', $fishSpeciesId);
            })
            ->orderBy(
                DB::table('arrivals')
                    ->select('arrival_date')
                    ->whereColumn('arrivals.id', 'arrival_catches.arrival_id'),
                'desc'
            );

        // Summary statistics
        $summary = [
            'total_weight' => (clone $query)->reorder()->sum('weight_kg'),
            'total_value' => (clone $query)->reorder()->sum('estimated_value'),
            'top_species' => (clone $query)
                ->reorder()
                ->select('fish_species_id', DB::raw('SUM(weight_kg) as total_weight'))
                ->groupBy('fish_species_id')
                ->with('fishSpecies')
                ->orderBy('total_weight', 'desc')
                ->limit(5)
                ->get(),
        ];

        $catches = $query->paginate(15)->withQueryString();

        $fishSpecies = FishSpecies::orderBy('species_name')->get(['id', 'species_name', 'local_name']);

        return Inertia::render('Reports/Catches', [
            'catches' => $catches,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'fish_species_id' => $fishSpeciesId,
            ],
            'fishSpecies' => $fishSpecies,
            'summary' => $summary,
        ]);
    }

    /**
     * Export catches data to Excel (CSV format).
     */
    public function exportExcel(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $fishSpeciesId = $request->input('fish_species_id');

        $catches = ArrivalCatch::query()
            ->with(['arrival.vessel', 'fishSpecies'])
            ->whereHas('arrival', function ($q) use ($dateFrom, $dateTo) {
                $q->whereDate('arrival_date', '>=', $dateFrom)
                  ->whereDate('arrival_date', '<=', $dateTo);
            })
            ->when($fishSpeciesId, function ($q, $fishSpeciesId) {
                $q->where('fish_species_id', $fishSpeciesId);
            })
            ->orderBy(
                DB::table('arrivals')
                    ->select('arrival_date')
                    ->whereColumn('arrivals.id', 'arrival_catches.arrival_id'),
                'desc'
            )
            ->get();

        $filename = 'laporan_tangkapan_' . $dateFrom . '_sd_' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($catches) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'Tanggal Kedatangan',
                'Nama Kapal',
                'Jenis Ikan',
                'Nama Lokal',
                'Berat (kg)',
                'Estimasi Nilai (Rp)',
            ]);

            foreach ($catches as $index => $catch) {
                fputcsv($file, [
                    $index + 1,
                    $catch->arrival->arrival_date->format('d/m/Y'),
                    $catch->arrival->vessel->vessel_name ?? '-',
                    $catch->fishSpecies->species_name ?? '-',
                    $catch->fishSpecies->local_name ?? '-',
                    $catch->weight_kg,
                    number_format($catch->estimated_value, 0, ',', '.'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export catches data to PDF.
     */
    public function exportPdf(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $fishSpeciesId = $request->input('fish_species_id');

        $catches = ArrivalCatch::query()
            ->with(['arrival.vessel', 'fishSpecies'])
            ->whereHas('arrival', function ($q) use ($dateFrom, $dateTo) {
                $q->whereDate('arrival_date', '>=', $dateFrom)
                  ->whereDate('arrival_date', '<=', $dateTo);
            })
            ->when($fishSpeciesId, function ($q, $fishSpeciesId) {
                $q->where('fish_species_id', $fishSpeciesId);
            })
            ->orderBy(
                DB::table('arrivals')
                    ->select('arrival_date')
                    ->whereColumn('arrivals.id', 'arrival_catches.arrival_id'),
                'desc'
            )
            ->get();

        $pdf = Pdf::loadView('reports.catches-pdf', [
            'catches' => $catches,
            'dateFrom' => Carbon::parse($dateFrom)->format('d/m/Y'),
            'dateTo' => Carbon::parse($dateTo)->format('d/m/Y'),
            'total_weight' => $catches->sum('weight_kg'),
            'total_value' => $catches->sum('estimated_value'),
        ])->setPaper('a4', 'portrait');

        $filename = 'laporan_tangkapan_' . $dateFrom . '_sd_' . $dateTo . '.pdf';

        return $pdf->download($filename);
    }
}
