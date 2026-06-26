<?php

namespace App\Http\Controllers;

use App\Models\ArrivalCatch;
use App\Models\FishSpecies;
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
     * Export catches data to Excel (XLSX format).
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Tangkapan');

        // Header columns
        $headers = [
            'No',
            'Tanggal Kedatangan',
            'Nama Kapal',
            'Jenis Ikan',
            'Nama Lokal',
            'Berat (kg)',
            'Estimasi Nilai (Rp)',
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
        foreach ($catches as $index => $catch) {
            $arrivalDate = $catch->arrival->arrival_date instanceof Carbon
                ? $catch->arrival->arrival_date->format('d/m/Y')
                : Carbon::parse($catch->arrival->arrival_date)->format('d/m/Y');

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $arrivalDate);
            $sheet->setCellValue('C' . $row, $catch->arrival->vessel->vessel_name ?? '-');
            $sheet->setCellValue('D' . $row, $catch->fishSpecies->species_name ?? '-');
            $sheet->setCellValue('E' . $row, $catch->fishSpecies->local_name ?? '-');
            $sheet->setCellValue('F' . $row, $catch->weight_kg);
            $sheet->setCellValue('G' . $row, $catch->estimated_value);

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

        // Number format for weight and value columns
        $sheet->getStyle('F2:F' . max($row - 1, 2))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('G2:G' . max($row - 1, 2))->getNumberFormat()->setFormatCode('#,##0');

        // Add totals row
        if ($row > 2) {
            $sheet->setCellValue('E' . $row, 'TOTAL');
            $sheet->setCellValue('F' . $row, $catches->sum('weight_kg'));
            $sheet->setCellValue('G' . $row, $catches->sum('estimated_value'));
            $sheet->getStyle('E' . $row . ':' . $lastCol . $row)->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E2F3']],
            ]);
        }

        // Auto-size columns
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = 'laporan_tangkapan_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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