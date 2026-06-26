<?php

namespace App\Http\Controllers;

use App\Models\Departure;
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
     * Export departures data to Excel (XLSX format).
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keberangkatan');

        // Header columns
        $headers = [
            'No', 'Nomor SKP', 'Tanggal Masuk', 'Tanggal Keluar', 'Nama Kapal',
            'Nakhoda', 'No. Izin', 'Tujuan', 'Etmal (Hari)', 'Etmal (Jam)',
            'Jumlah ABK', 'Floating', 'Bongkar Ikan', 'Penyelesaian Administrasi',
            'Syahbandar', 'Status', 'Input Oleh', 'Catatan',
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
        foreach ($departures as $index => $departure) {
            $arrivalDate = $departure->arrival_datetime
                ? Carbon::parse($departure->arrival_datetime)->format('d/m/Y H:i')
                : '-';

            $departureDate = $departure->departure_datetime
                ? Carbon::parse($departure->departure_datetime)->format('d/m/Y H:i')
                : ($departure->departure_date instanceof Carbon
                    ? $departure->departure_date->format('d/m/Y')
                    : Carbon::parse($departure->departure_date)->format('d/m/Y'));

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $departure->nomor ?? '-');
            $sheet->setCellValue('C' . $row, $arrivalDate);
            $sheet->setCellValue('D' . $row, $departureDate);
            $sheet->setCellValue('E' . $row, $departure->vessel->vessel_name ?? '-');
            $sheet->setCellValue('F' . $row, $departure->nakhoda_name ?? '-');
            $sheet->setCellValue('G' . $row, $departure->vessel->license_number ?? '-');
            $sheet->setCellValue('H' . $row, $departure->destination ?? '-');
            $sheet->setCellValue('I' . $row, $departure->etmal_days ?? 0);
            $sheet->setCellValue('J' . $row, $departure->etmal_hours ?? 0);
            $sheet->setCellValue('K' . $row, $departure->crew_count ?? 0);
            $sheet->setCellValue('L' . $row, $departure->floating_status ?? '-');
            $sheet->setCellValue('M' . $row, $departure->unloading_status ?? '-');
            $sheet->setCellValue('N' . $row, $departure->admin_completion ?? '-');
            $sheet->setCellValue('O' . $row, $departure->syahbandar ?? '-');
            $sheet->setCellValue('P' . $row, $departure->status ?? '-');
            $sheet->setCellValue('Q' . $row, $departure->inputBy->name ?? '-');
            $sheet->setCellValue('R' . $row, $departure->notes ?? '-');

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

        $filename = 'laporan_keberangkatan_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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