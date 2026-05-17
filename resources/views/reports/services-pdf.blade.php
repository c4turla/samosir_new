<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Jasa {{ $serviceName }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 8px;
        }
        .header h1 {
            font-size: 14px;
            margin: 0;
            color: #1e40af;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 10px;
            margin: 3px 0 0;
            font-weight: normal;
            color: #555;
        }
        .meta-container {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 0;
            border: none;
            font-size: 8px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 5px 3px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1e40af;
        }
        td {
            padding: 4px 3px;
            border: 1px solid #e5e7eb;
            font-size: 8px;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status {
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
            text-align: center;
        }
        .status-order { background-color: #fef3c7; color: #92400e; }
        .status-processed { background-color: #dbeafe; color: #1e40af; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 7px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PELAYANAN JASA {{ strtoupper($serviceName) }}</h1>
        <h2>Pelabuhan Perikanan Nusantara Sibolga</h2>
    </div>

    <div class="meta-container">
        <table class="meta-table">
            <tr>
                <td style="width: 10%"><strong>Periode:</strong></td>
                <td style="width: 40%">{{ $dateFrom }} s/d {{ $dateTo }}</td>
                <td style="width: 15%"><strong>Total Transaksi:</strong></td>
                <td style="width: 35%">{{ $total }} transaksi</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>{{ $status ? strtoupper($status) : 'SEMUA STATUS' }}</td>
                <td><strong>Total Pendapatan (Lunas):</strong></td>
                <td class="font-bold" style="color: #065f46;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak:</strong></td>
                <td colspan="3">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px">No</th>
                <th style="width: 70px">No. Order</th>
                @if($serviceType === 'water')
                <th>Nama Pemohon</th>
                @else
                <th>Nama Penyewa</th>
                @endif
                <th>Nama Kapal</th>
                <th class="text-center" style="width: 60px">Tanggal</th>
                @if($serviceType === 'equipment')
                <th class="text-center" style="width: 45px">Durasi</th>
                @elseif($serviceType === 'water')
                <th class="text-center" style="width: 45px">Volume</th>
                @endif
                <th class="text-right" style="width: 70px">Total Biaya</th>
                <th style="width: 80px">Petugas Lapangan</th>
                <th style="width: 80px">Bendahara</th>
                <th class="text-center" style="width: 55px">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $record->order_number }}</td>
                @if($serviceType === 'water')
                <td>{{ $record->requester ?? '-' }}</td>
                <td>{{ $record->vessel->vessel_name ?? '-' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($record->request_date)->format('d/m/Y') }}</td>
                <td class="text-center">{{ $record->volume ?? 0 }} Ton</td>
                <td class="text-right">Rp {{ number_format($record->total_payment, 0, ',', '.') }}</td>
                <td>{{ $record->field_officer ?? '-' }}</td>
                <td>{{ $record->treasurer ?? '-' }}</td>
                @else
                <td>{{ $record->renter_name ?? '-' }}</td>
                <td>{{ $record->vessel->vessel_name ?? '-' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($record->service_date)->format('d/m/Y') }}</td>
                @if($serviceType === 'equipment')
                <td class="text-center">{{ $record->duration ?? 0 }} Jam</td>
                @endif
                <td class="text-right">Rp {{ number_format($record->total_amount, 0, ',', '.') }}</td>
                <td>{{ $record->officer ?? '-' }}</td>
                <td>{{ $record->treasurer ?? '-' }}</td>
                @endif
                <td class="text-center">
                    <span class="status status-{{ strtolower($record->status ?? 'order') }}">
                        {{ $record->status ?? '-' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $serviceType === 'ice_cruiser' ? 9 : 10 }}" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada data pelayanan jasa pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh Sistem SAMOSIR v3.0 · Pelabuhan Perikanan Nusantara Sibolga</p>
    </div>
</body>
</html>
