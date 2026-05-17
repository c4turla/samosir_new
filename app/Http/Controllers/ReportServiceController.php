<?php

namespace App\Http\Controllers;

use App\Models\EquipmentService;
use App\Models\WaterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportServiceController extends Controller
{
    /**
     * Display the services report page.
     */
    public function index(Request $request)
    {
        $serviceType = $request->input('service_type', 'equipment');
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

        if ($serviceType === 'equipment') {
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
     * Export services report data to CSV/Excel.
     */
    public function exportExcel(Request $request)
    {
        $serviceType = $request->input('service_type', 'equipment');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $search = $request->input('search');

        $records = [];

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
                
            $filename = 'laporan_jasa_peralatan_' . $dateFrom . '_sd_' . $dateTo . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function () use ($records) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, ['No', 'No. Order', 'Nama Penyewa', 'Nama Kapal', 'Tanggal Pelayanan', 'Durasi (Jam)', 'Total Biaya (Rp)', 'Status', 'Petugas', 'Bendahara']);
                
                foreach ($records as $index => $record) {
                    fputcsv($file, [
                        $index + 1,
                        $record->order_number,
                        $record->renter_name ?? '-',
                        $record->vessel->vessel_name ?? '-',
                        Carbon::parse($record->service_date)->format('d/m/Y'),
                        $record->duration ?? 0,
                        number_format($record->total_amount, 0, ',', '.'),
                        ucfirst($record->status),
                        $record->officer ?? '-',
                        $record->treasurer ?? '-',
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);

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
                
            $filename = 'laporan_jasa_ice_cruiser_' . $dateFrom . '_sd_' . $dateTo . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function () use ($records) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, ['No', 'No. Order', 'Nama Penyewa', 'Nama Kapal', 'Tanggal Pelayanan', 'Total Biaya (Rp)', 'Status', 'Petugas', 'Bendahara']);
                
                foreach ($records as $index => $record) {
                    fputcsv($file, [
                        $index + 1,
                        $record->order_number,
                        $record->renter_name ?? '-',
                        $record->vessel->vessel_name ?? '-',
                        Carbon::parse($record->service_date)->format('d/m/Y'),
                        number_format($record->total_amount, 0, ',', '.'),
                        ucfirst($record->status),
                        $record->officer ?? '-',
                        $record->treasurer ?? '-',
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);

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
                
            $filename = 'laporan_jasa_air_' . $dateFrom . '_sd_' . $dateTo . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function () use ($records) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, ['No', 'No. Order', 'Pemohon', 'Nama Kapal', 'Tanggal Permohonan', 'Volume (Ton)', 'Total Biaya (Rp)', 'Status', 'Petugas Lapangan', 'Bendahara PNBP']);
                
                foreach ($records as $index => $record) {
                    fputcsv($file, [
                        $index + 1,
                        $record->order_number,
                        $record->requester ?? '-',
                        $record->vessel->vessel_name ?? '-',
                        Carbon::parse($record->request_date)->format('d/m/Y'),
                        $record->volume ?? 0,
                        number_format($record->total_payment, 0, ',', '.'),
                        ucfirst($record->status),
                        $record->field_officer ?? '-',
                        $record->treasurer ?? '-',
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
    }

    /**
     * Export services report data to PDF.
     */
    public function exportPdf(Request $request)
    {
        $serviceType = $request->input('service_type', 'equipment');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $search = $request->input('search');

        $records = [];
        $serviceName = '';
        $totalRevenue = 0;

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
