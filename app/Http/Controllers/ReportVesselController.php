<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
     * Export vessels data to Excel (XLSX format).
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Data Kapal');

        // Header columns
        $headers = [
            'No', 'Nama Kapal', 'Pemilik', 'No. Izin / Selar', 'GT',
            'Alat Tangkap', 'Jenis Kapal', 'No SIUP', 'Tgl Akhir SIPI',
            'Status SIPI', 'Panjang (m)', 'Catatan',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style header
        $lastCol = chr(ord('A') + count($headers) - 1);
        $headerRange = 'A1:' . $lastCol . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Data rows
        $row = 2;
        foreach ($vessels as $index => $vessel) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $vessel->vessel_name);
            $sheet->setCellValue('C' . $row, $vessel->owner_name ?? '-');
            $sheet->setCellValue('D' . $row, $vessel->license_number ?? $vessel->selar_mark ?? '-');
            $sheet->setCellValue('E' . $row, $vessel->gt ?? 0);
            $sheet->setCellValue('F' . $row, $vessel->fishing_gear ?? '-');
            $sheet->setCellValue('G' . $row, $vessel->vessel_type ?? '-');
            $sheet->setCellValue('H' . $row, $vessel->siup_number ?? '-');
            $sheet->setCellValue('I' . $row, $vessel->sipi_end_date ? $vessel->sipi_end_date->format('d/m/Y') : '-');
            $sheet->setCellValue('J' . $row, $vessel->sipi_status_text);
            $sheet->setCellValue('K' . $row, $vessel->length ?? 0);
            $sheet->setCellValue('L' . $row, $vessel->notes ?? '-');

            $row++;
        }

        // Style data rows with borders
        if ($row > 2) {
            $dataRange = 'A2:' . $lastCol . ($row - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
            ]);
        }

        // Auto-size columns
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = 'laporan_data_kapal_' . Carbon::now()->format('YmdHis') . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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