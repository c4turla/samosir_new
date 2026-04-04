<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportVesselController extends Controller
{
    /**
     * Display the vessel report page.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $gtMin = $request->input('gt_min');
        $gtMax = $request->input('gt_max');
        $fishingGear = $request->input('fishing_gear');
        $vesselType = $request->input('vessel_type');
        $sipiStatus = $request->input('sipi_status');

        $query = Vessel::query()
            ->when($search, function ($q, $search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('vessel_name', 'like', "%{$search}%")
                       ->orWhere('owner_name', 'like', "%{$search}%")
                       ->orWhere('license_number', 'like', "%{$search}%");
                });
            })
            ->when($gtMin, function ($q, $gtMin) {
                $q->where('gt', '>=', $gtMin);
            })
            ->when($gtMax, function ($q, $gtMax) {
                $q->where('gt', '<=', $gtMax);
            })
            ->when($fishingGear, function ($q, $fishingGear) {
                $q->where('fishing_gear', $fishingGear);
            })
            ->when($vesselType, function ($q, $vesselType) {
                $q->where('vessel_type', $vesselType);
            })
            ->when($sipiStatus, function ($q, $sipiStatus) {
                if ($sipiStatus === 'active') {
                    $q->whereDate('sipi_end_date', '>=', Carbon::now());
                } elseif ($sipiStatus === 'expired') {
                    $q->whereDate('sipi_end_date', '<', Carbon::now());
                }
            })
            ->orderBy('vessel_name');

        // Summary statistics
        $totalVessels = (clone $query)->count();
        $avgGt = (clone $query)->avg('gt');
        
        $vessels = $query->paginate(15)->withQueryString();

        // Get unique fishing gears and vessel types for filters
        $fishingGears = Vessel::whereNotNull('fishing_gear')->distinct()->pluck('fishing_gear');
        $vesselTypes = Vessel::whereNotNull('vessel_type')->distinct()->pluck('vessel_type');

        return Inertia::render('Reports/Vessels', [
            'vessels' => $vessels,
            'filters' => [
                'search' => $search,
                'gt_min' => $gtMin,
                'gt_max' => $gtMax,
                'fishing_gear' => $fishingGear,
                'vessel_type' => $vesselType,
                'sipi_status' => $sipiStatus,
            ],
            'options' => [
                'fishing_gears' => $fishingGears,
                'vessel_types' => $vesselTypes,
            ],
            'summary' => [
                'total' => $totalVessels,
                'avg_gt' => round($avgGt, 2),
            ],
        ]);
    }

    /**
     * Export vessels data to Excel (CSV format).
     */
    public function exportExcel(Request $request)
    {
        $search = $request->input('search');
        $gtMin = $request->input('gt_min');
        $gtMax = $request->input('gt_max');
        $fishingGear = $request->input('fishing_gear');
        $vesselType = $request->input('vessel_type');
        $sipiStatus = $request->input('sipi_status');

        $vessels = Vessel::query()
            ->when($search, function ($q, $search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('vessel_name', 'like', "%{$search}%")
                       ->orWhere('owner_name', 'like', "%{$search}%");
                });
            })
            ->when($gtMin, function ($q, $gtMin) {
                $q->where('gt', '>=', $gtMin);
            })
            ->when($gtMax, function ($q, $gtMax) {
                $q->where('gt', '<=', $gtMax);
            })
            ->when($fishingGear, function ($q, $fishingGear) {
                $q->where('fishing_gear', $fishingGear);
            })
            ->when($vesselType, function ($q, $vesselType) {
                $q->where('vessel_type', $vesselType);
            })
            ->when($sipiStatus, function ($q, $sipiStatus) {
                if ($sipiStatus === 'active') {
                    $q->whereDate('sipi_end_date', '>=', Carbon::now());
                } elseif ($sipiStatus === 'expired') {
                    $q->whereDate('sipi_end_date', '<', Carbon::now());
                }
            })
            ->orderBy('vessel_name')
            ->get();

        $filename = 'laporan_data_kapal_' . Carbon::now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($vessels) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'Nama Kapal',
                'Pemilik',
                'No. Izin / Selar',
                'GT',
                'Alat Tangkap',
                'Jenis Kapal',
                'No SIUP',
                'Tgl Akhir SIPI',
                'Status SIPI',
                'Panjang (m)',
                'Catatan',
            ]);

            foreach ($vessels as $index => $vessel) {
                fputcsv($file, [
                    $index + 1,
                    $vessel->vessel_name,
                    $vessel->owner_name ?? '-',
                    $vessel->license_number ?? $vessel->selar_mark ?? '-',
                    $vessel->gt ?? 0,
                    $vessel->fishing_gear ?? '-',
                    $vessel->vessel_type ?? '-',
                    $vessel->siup_number ?? '-',
                    $vessel->sipi_end_date ? $vessel->sipi_end_date->format('d/m/Y') : '-',
                    $vessel->sipi_status_text,
                    $vessel->length ?? 0,
                    $vessel->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export vessels data to PDF.
     */
    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $gtMin = $request->input('gt_min');
        $gtMax = $request->input('gt_max');
        $fishingGear = $request->input('fishing_gear');
        $vesselType = $request->input('vessel_type');
        $sipiStatus = $request->input('sipi_status');

        $vessels = Vessel::query()
            ->when($search, function ($q, $search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('vessel_name', 'like', "%{$search}%");
                });
            })
            ->when($gtMin, function ($q, $gtMin) {
                $q->where('gt', '>=', $gtMin);
            })
            ->when($gtMax, function ($q, $gtMax) {
                $q->where('gt', '<=', $gtMax);
            })
            ->when($fishingGear, function ($q, $fishingGear) {
                $q->where('fishing_gear', $fishingGear);
            })
            ->when($vesselType, function ($q, $vesselType) {
                $q->where('vessel_type', $vesselType);
            })
            ->when($sipiStatus, function ($q, $sipiStatus) {
                if ($sipiStatus === 'active') {
                    $q->whereDate('sipi_end_date', '>=', Carbon::now());
                } elseif ($sipiStatus === 'expired') {
                    $q->whereDate('sipi_end_date', '<', Carbon::now());
                }
            })
            ->orderBy('vessel_name')
            ->get();

        $pdf = Pdf::loadView('reports.vessels-pdf', [
            'vessels' => $vessels,
            'total' => $vessels->count(),
            'avg_gt' => round($vessels->avg('gt'), 2),
            'filters' => [
                'gt' => ($gtMin || $gtMax) ? ($gtMin ?? '0') . ' - ' . ($gtMax ?? 'Any') : 'Semua',
                'fishing_gear' => $fishingGear ?? 'Semua',
                'sipi_status' => $sipiStatus ?? 'Semua',
            ]
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan_data_kapal_' . Carbon::now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }
}
