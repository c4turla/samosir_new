<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kedatangan</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            color: #1e40af;
        }
        .header h2 {
            font-size: 12px;
            margin: 4px 0 0;
            font-weight: normal;
            color: #555;
        }
        .meta {
            margin-bottom: 15px;
            display: flex;
            font-size: 9px;
        }
        .meta p {
            margin: 2px 0;
        }
        .meta strong {
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8px;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        .status-berlabuh { background-color: #dbeafe; color: #1e40af; }
        .status-bongkar { background-color: #fef3c7; color: #92400e; }
        .status-selesai { background-color: #d1fae5; color: #065f46; }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KEDATANGAN KAPAL</h1>
        <h2>Pelabuhan Perikanan Nusantara Sibolga</h2>
    </div>

    <div class="meta">
        <p><strong>Periode:</strong> {{ $dateFrom }} s/d {{ $dateTo }}</p>
        @if($status)
        <p><strong>Status:</strong> {{ $status }}</p>
        @endif
        <p><strong>Total Data:</strong> {{ $total }} kedatangan</p>
        <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 25px">No</th>
                <th style="width: 65px">Tanggal</th>
                <th style="width: 35px">Jam</th>
                <th>Nama Kapal</th>
                <th>No. Izin</th>
                <th>Asal</th>
                <th>Lokasi</th>
                <th style="width: 120px">Detail Ikan (Jenis - Berat)</th>
                <th>Kualitas</th>
                <th class="text-right">Harga Rata²</th>
                <th class="text-center">Suhu Ikan</th>
                <th class="text-center">Suhu Palka</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($arrivals as $index => $arrival)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $arrival->arrival_date instanceof \Carbon\Carbon ? $arrival->arrival_date->format('d/m/Y') : \Carbon\Carbon::parse($arrival->arrival_date)->format('d/m/Y') }}</td>
                <td>{{ $arrival->arrival_time instanceof \Carbon\Carbon ? $arrival->arrival_time->format('H:i') : $arrival->arrival_time }}</td>
                <td>{{ $arrival->vessel->vessel_name ?? '-' }}</td>
                <td>{{ $arrival->vessel->license_number ?? '-' }}</td>
                <td>{{ $arrival->origin ?? '-' }}</td>
                <td>{{ $arrival->landingSite->site_name ?? '-' }}</td>
                <td>
                    @if($arrival->catches && $arrival->catches->count() > 0)
                        <ul style="margin: 0; padding-left: 10px;">
                        @foreach($arrival->catches as $catch)
                            <li>{{ $catch->fishSpecies->species_name ?? $catch->fishSpecies->local_name ?? '-' }}: {{ number_format($catch->weight_kg, 0, ',', '.') }}kg</li>
                        @endforeach
                        </ul>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $arrival->fish_quality ?? '-' }}</td>
                <td class="text-right">{{ number_format($arrival->average_price ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ $arrival->fish_temperature ?? 0 }}°C</td>
                <td class="text-center">{{ $arrival->hold_temperature ?? 0 }}°C</td>
                <td class="text-center">
                    <span class="status status-{{ strtolower($arrival->status ?? 'berlabuh') }}">
                        {{ $arrival->status ?? '-' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 20px; color: #9ca3af;">Tidak ada data kedatangan pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh Sistem SAMOSIR v3.0 · Pelabuhan Perikanan Nusantara Sibolga</p>
    </div>
</body>
</html>
