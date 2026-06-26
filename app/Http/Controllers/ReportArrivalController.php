<?php

namespace App\Http\Controllers;

use App\Models\Arrival;
use App\Models\LandingSite;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
     * Export arrivals data to Excel (XLSX format).
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Kedatangan');

        // Header columns
        $headers = [
            'No', 'Tanggal Kedatangan', 'Waktu', 'Nama Kapal', 'No. Izin', 'Asal',
            'Lokasi Pendaratan', 'Mutu Ikan', 'Kualitas Ikan', 'Harga Rata-rata',
            'Volume Limbah (kg)', 'Suhu Ikan (°C)', 'Suhu Palka (°C)',
            'Detail Ikan (Jenis - Berat - Nilai)', 'Total Berat Ikan (kg)',
            'Total Nilai Ikan', 'Status', 'Input Oleh', 'Catatan',
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

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $arrivalDate);
            $sheet->setCellValue('C' . $row, $arrivalTime);
            $sheet->setCellValue('D' . $row, $arrival->vessel->vessel_name ?? '-');
            $sheet->setCellValue('E' . $row, $arrival->vessel->license_number ?? '-');
            $sheet->setCellValue('F' . $row, $arrival->origin ?? '-');
            $sheet->setCellValue('G' . $row, $arrival->landingSite->site_name ?? '-');
            $sheet->setCellValue('H' . $row, $arrival->mutu ?? '-');
            $sheet->setCellValue('I' . $row, $arrival->fish_quality ?? '-');
            $sheet->setCellValue('J' . $row, $arrival->average_price ?? 0);
            $sheet->setCellValue('K' . $row, $arrival->waste_volume ?? 0);
            $sheet->setCellValue('L' . $row, $arrival->fish_temperature ?? 0);
            $sheet->setCellValue('M' . $row, $arrival->hold_temperature ?? 0);
            $sheet->setCellValue('N' . $row, $fishDetails ?: 'Tidak ada');
            $sheet->setCellValue('O' . $row, $totalWeight);
            $sheet->setCellValue('P' . $row, $totalValue);
            $sheet->setCellValue('Q' . $row, $arrival->status ?? '-');
            $sheet->setCellValue('R' . $row, $arrival->inputBy->name ?? '-');
            $sheet->setCellValue('S' . $row, $arrival->notes ?? '-');

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

        // Number format for currency columns
        $sheet->getStyle('J2:J' . max($row - 1, 2))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('P2:P' . max($row - 1, 2))->getNumberFormat()->setFormatCode('#,##0');

        $filename = 'laporan_kedatangan_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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