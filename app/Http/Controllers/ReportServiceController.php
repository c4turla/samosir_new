<?php

namespace App\Http\Controllers;

use App\Models\EquipmentService;
use App\Models\WaterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportServiceController extends Controller
{
    /**
     * Display the services report page.
     */
    public function index(Request $request)
    {
        $serviceType = $request->input('service_type', 'all');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $search = $request->input('search');

        $results = null;
        $summary = [
            'total_records' => 0,
            'total_revenue' => 0,
            'status_counts' => [
                'order' => 0,
                'processed' => 0,
                'completed' => 0,
                'cancelled' => 0,
            ]
        ];

        if ($serviceType === 'all') {
            $equipmentQuery = EquipmentService::query()
                ->when($status, function ($q, $status) { $q->where('status', $status); })
                ->whereDate('service_date', '>=', $dateFrom)
                ->whereDate('service_date', '<=', $dateTo);
            
            $waterQuery = WaterService::query()
                ->when($status, function ($q, $status) { $q->where('status', $status); })
                ->whereDate('request_date', '>=', $dateFrom)
                ->whereDate('request_date', '<=', $dateTo);

            $summary['total_records'] = (clone $equipmentQuery)->count() + (clone $waterQuery)->count();
            
            $eqRevenue = (clone $equipmentQuery)->where('status', 'completed')->sum('total_amount');
            $watRevenue = (clone $waterQuery)->where('status', 'completed')->sum('total_payment');
            $summary['total_revenue'] = $eqRevenue + $watRevenue;

            $summary['equipment_revenue'] = (clone $equipmentQuery)->where('status', 'completed')->whereDoesntHave('items', function($q) { $q->where('equipment_name', 'ice_cruiser'); })->sum('total_amount');
            $summary['ice_cruiser_revenue'] = (clone $equipmentQuery)->where('status', 'completed')->whereHas('items', function($q) { $q->where('equipment_name', 'ice_cruiser'); })->sum('total_amount');
            $summary['water_revenue'] = $watRevenue;

            $summary['status_counts']['order'] = (clone $equipmentQuery)->where('status', 'order')->count() + (clone $waterQuery)->where('status', 'order')->count();
            $summary['status_counts']['processed'] = (clone $equipmentQuery)->where('status', 'processed')->count() + (clone $waterQuery)->where('status', 'processed')->count();
            $summary['status_counts']['completed'] = (clone $equipmentQuery)->where('status', 'completed')->count() + (clone $waterQuery)->where('status', 'completed')->count();
            $summary['status_counts']['cancelled'] = (clone $equipmentQuery)->where('status', 'cancelled')->count() + (clone $waterQuery)->where('status', 'cancelled')->count();

            $results = [
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
            ];

        } else if ($serviceType === 'equipment') {
            $query = EquipmentService::query()
                ->with(['vessel', 'items'])
                ->whereDoesntHave('items', function ($q) {
                    $q->where('equipment_name', 'ice_cruiser');
                })
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('renter_name', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('service_date', '>=', $dateFrom)
                ->whereDate('service_date', '<=', $dateTo)
                ->orderBy('service_date', 'desc')
                ->orderBy('order_number', 'desc');

            $summary['total_records'] = (clone $query)->count();
            $summary['total_revenue'] = (clone $query)->where('status', 'completed')->sum('total_amount');
            $summary['status_counts']['order'] = (clone $query)->where('status', 'order')->count();
            $summary['status_counts']['processed'] = (clone $query)->where('status', 'processed')->count();
            $summary['status_counts']['completed'] = (clone $query)->where('status', 'completed')->count();
            $summary['status_counts']['cancelled'] = (clone $query)->where('status', 'cancelled')->count();

            $results = $query->paginate(15)->withQueryString();

        } else if ($serviceType === 'ice_cruiser') {
            $query = EquipmentService::query()
                ->with(['vessel', 'items'])
                ->whereHas('items', function ($q) {
                    $q->where('equipment_name', 'ice_cruiser');
                })
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('renter_name', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('service_date', '>=', $dateFrom)
                ->whereDate('service_date', '<=', $dateTo)
                ->orderBy('service_date', 'desc')
                ->orderBy('order_number', 'desc');

            $summary['total_records'] = (clone $query)->count();
            $summary['total_revenue'] = (clone $query)->where('status', 'completed')->sum('total_amount');
            $summary['status_counts']['order'] = (clone $query)->where('status', 'order')->count();
            $summary['status_counts']['processed'] = (clone $query)->where('status', 'processed')->count();
            $summary['status_counts']['completed'] = (clone $query)->where('status', 'completed')->count();
            $summary['status_counts']['cancelled'] = (clone $query)->where('status', 'cancelled')->count();

            $results = $query->paginate(15)->withQueryString();

        } else if ($serviceType === 'water') {
            $query = WaterService::query()
                ->with(['vessel'])
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('requester', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('request_date', '>=', $dateFrom)
                ->whereDate('request_date', '<=', $dateTo)
                ->orderBy('request_date', 'desc')
                ->orderBy('order_number', 'desc');

            $summary['total_records'] = (clone $query)->count();
            $summary['total_revenue'] = (clone $query)->where('status', 'completed')->sum('total_payment');
            $summary['status_counts']['order'] = (clone $query)->where('status', 'order')->count();
            $summary['status_counts']['processed'] = (clone $query)->where('status', 'processed')->count();
            $summary['status_counts']['completed'] = (clone $query)->where('status', 'completed')->count();
            $summary['status_counts']['cancelled'] = (clone $query)->where('status', 'cancelled')->count();

            $results = $query->paginate(15)->withQueryString();
        }

        return Inertia::render('Reports/Services', [
            'results' => $results,
            'filters' => [
                'service_type' => $serviceType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
                'search' => $search,
            ],
            'summary' => $summary,
        ]);
    }

    /**
     * Export services report data to Excel (XLSX format).
     */
    public function exportExcel(Request $request)
    {
        $serviceType = $request->input('service_type', 'all');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $search = $request->input('search');

        if ($serviceType === 'all') {
            return redirect()->back()->with('error', 'Silakan pilih spesifik jenis jasa untuk diexport.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($serviceType === 'equipment') {
            $records = EquipmentService::query()
                ->with(['vessel'])
                ->whereDoesntHave('items', function ($q) {
                    $q->where('equipment_name', 'ice_cruiser');
                })
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('renter_name', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('service_date', '>=', $dateFrom)
                ->whereDate('service_date', '<=', $dateTo)
                ->orderBy('service_date', 'desc')
                ->get();

            $sheet->setTitle('Jasa Peralatan');

            $headers = [
                'No', 'No. Order', 'Nama Penyewa', 'Nama Kapal',
                'Tanggal Pelayanan', 'Durasi (Jam)', 'Total Biaya (Rp)',
                'Status', 'Petugas', 'Bendahara',
            ];

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $lastCol = chr(ord('A') + count($headers) - 1);

            $row = 2;
            foreach ($records as $index => $record) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $record->order_number);
                $sheet->setCellValue('C' . $row, $record->renter_name ?? '-');
                $sheet->setCellValue('D' . $row, $record->vessel->vessel_name ?? '-');
                $sheet->setCellValue('E' . $row, Carbon::parse($record->service_date)->format('d/m/Y'));
                $sheet->setCellValue('F' . $row, $record->duration ?? 0);
                $sheet->setCellValue('G' . $row, $record->total_amount ?? 0);
                $sheet->setCellValue('H' . $row, ucfirst($record->status));
                $sheet->setCellValue('I' . $row, $record->officer ?? '-');
                $sheet->setCellValue('J' . $row, $record->treasurer ?? '-');
                $row++;
            }

            // Totals row
            if ($row > 2) {
                $sheet->setCellValue('F' . $row, 'TOTAL');
                $sheet->setCellValue('G' . $row, $records->sum('total_amount'));
                $sheet->getStyle('F' . $row . ':G' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E2F3']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
            }

            $filename = 'laporan_jasa_peralatan_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';

        } else if ($serviceType === 'ice_cruiser') {
            $records = EquipmentService::query()
                ->with(['vessel'])
                ->whereHas('items', function ($q) {
                    $q->where('equipment_name', 'ice_cruiser');
                })
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('renter_name', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('service_date', '>=', $dateFrom)
                ->whereDate('service_date', '<=', $dateTo)
                ->orderBy('service_date', 'desc')
                ->get();

            $sheet->setTitle('Jasa Ice Cruiser');

            $headers = [
                'No', 'No. Order', 'Nama Penyewa', 'Nama Kapal',
                'Tanggal Pelayanan', 'Total Biaya (Rp)', 'Status',
                'Petugas', 'Bendahara',
            ];

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $lastCol = chr(ord('A') + count($headers) - 1);

            $row = 2;
            foreach ($records as $index => $record) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $record->order_number);
                $sheet->setCellValue('C' . $row, $record->renter_name ?? '-');
                $sheet->setCellValue('D' . $row, $record->vessel->vessel_name ?? '-');
                $sheet->setCellValue('E' . $row, Carbon::parse($record->service_date)->format('d/m/Y'));
                $sheet->setCellValue('F' . $row, $record->total_amount ?? 0);
                $sheet->setCellValue('G' . $row, ucfirst($record->status));
                $sheet->setCellValue('H' . $row, $record->officer ?? '-');
                $sheet->setCellValue('I' . $row, $record->treasurer ?? '-');
                $row++;
            }

            // Totals row
            if ($row > 2) {
                $sheet->setCellValue('E' . $row, 'TOTAL');
                $sheet->setCellValue('F' . $row, $records->sum('total_amount'));
                $sheet->getStyle('E' . $row . ':F' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E2F3']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
            }

            $filename = 'laporan_jasa_ice_cruiser_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';

        } else { // water
            $records = WaterService::query()
                ->with(['vessel'])
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('requester', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('request_date', '>=', $dateFrom)
                ->whereDate('request_date', '<=', $dateTo)
                ->orderBy('request_date', 'desc')
                ->get();

            $sheet->setTitle('Jasa Air');

            $headers = [
                'No', 'No. Order', 'Pemohon', 'Nama Kapal',
                'Tanggal Permohonan', 'Volume (Ton)', 'Total Biaya (Rp)',
                'Status', 'Petugas Lapangan', 'Bendahara PNBP',
            ];

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $lastCol = chr(ord('A') + count($headers) - 1);

            $row = 2;
            foreach ($records as $index => $record) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $record->order_number);
                $sheet->setCellValue('C' . $row, $record->requester ?? '-');
                $sheet->setCellValue('D' . $row, $record->vessel->vessel_name ?? '-');
                $sheet->setCellValue('E' . $row, Carbon::parse($record->request_date)->format('d/m/Y'));
                $sheet->setCellValue('F' . $row, $record->volume ?? 0);
                $sheet->setCellValue('G' . $row, $record->total_payment ?? 0);
                $sheet->setCellValue('H' . $row, ucfirst($record->status));
                $sheet->setCellValue('I' . $row, $record->field_officer ?? '-');
                $sheet->setCellValue('J' . $row, $record->treasurer ?? '-');
                $row++;
            }

            // Totals row
            if ($row > 2) {
                $sheet->setCellValue('F' . $row, 'TOTAL');
                $sheet->setCellValue('G' . $row, $records->sum('total_payment'));
                $sheet->getStyle('F' . $row . ':G' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E2F3']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
            }

            $filename = 'laporan_jasa_air_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';
        }

        // Style header row
        $headerRange = 'A1:' . $lastCol . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Style data rows
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

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export services report data to PDF.
     */
    public function exportPdf(Request $request)
    {
        $serviceType = $request->input('service_type', 'all');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $search = $request->input('search');

        $records = collect();
        $serviceName = '';
        $totalRevenue = 0;

        if ($serviceType === 'all') {
            return redirect()->back()->with('error', 'Silakan pilih spesifik jenis jasa untuk diexport.');
        } else if ($serviceType === 'equipment') {
            $records = EquipmentService::query()
                ->with(['vessel'])
                ->whereDoesntHave('items', function ($q) {
                    $q->where('equipment_name', 'ice_cruiser');
                })
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('renter_name', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('service_date', '>=', $dateFrom)
                ->whereDate('service_date', '<=', $dateTo)
                ->orderBy('service_date', 'desc')
                ->get();
                
            $serviceName = 'Peralatan';
            $totalRevenue = $records->where('status', 'completed')->sum('total_amount');

        } else if ($serviceType === 'ice_cruiser') {
            $records = EquipmentService::query()
                ->with(['vessel'])
                ->whereHas('items', function ($q) {
                    $q->where('equipment_name', 'ice_cruiser');
                })
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('renter_name', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('service_date', '>=', $dateFrom)
                ->whereDate('service_date', '<=', $dateTo)
                ->orderBy('service_date', 'desc')
                ->get();
                
            $serviceName = 'Ice Cruiser';
            $totalRevenue = $records->where('status', 'completed')->sum('total_amount');

        } else if ($serviceType === 'water') {
            $records = WaterService::query()
                ->with(['vessel'])
                ->when($search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('order_number', 'like', "%{$search}%")
                           ->orWhere('requester', 'like', "%{$search}%")
                           ->orWhereHas('vessel', function ($q3) use ($search) {
                               $q3->where('vessel_name', 'like', "%{$search}%");
                           });
                    });
                })
                ->when($status, function ($q, $status) {
                    $q->where('status', $status);
                })
                ->whereDate('request_date', '>=', $dateFrom)
                ->whereDate('request_date', '<=', $dateTo)
                ->orderBy('request_date', 'desc')
                ->get();
                
            $serviceName = 'Air';
            $totalRevenue = $records->where('status', 'completed')->sum('total_payment');
        }

        $pdf = Pdf::loadView('reports.services-pdf', [
            'records' => $records,
            'serviceType' => $serviceType,
            'serviceName' => $serviceName,
            'dateFrom' => Carbon::parse($dateFrom)->format('d/m/Y'),
            'dateTo' => Carbon::parse($dateTo)->format('d/m/Y'),
            'status' => $status,
            'total' => $records->count(),
            'totalRevenue' => $totalRevenue,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan_jasa_' . strtolower(str_replace(' ', '_', $serviceName)) . '_' . $dateFrom . '_sd_' . $dateTo . '.pdf';

        return $pdf->download($filename);
    }
}