<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Kapal</title>
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
            margin-bottom: 20px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 14px;
            margin: 0;
            color: #1e40af;
        }
        .header h2 {
            font-size: 10px;
            margin: 4px 0 0;
            font-weight: normal;
            color: #555;
        }
        .meta {
            margin-bottom: 15px;
            font-size: 8px;
        }
        .meta table {
            width: 100%;
            border: none;
        }
        .meta td {
            border: none;
            padding: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 5px 3px;
            text-align: left;
            font-size: 7px;
            text-transform: uppercase;
        }
        td {
            padding: 4px 3px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 7px;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status {
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6px;
            font-weight: bold;
        }
        .status-active { background-color: #d1fae5; color: #065f46; }
        .status-expired { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7px;
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
        <h1>LAPORAN DATA KAPAL</h1>
        <h2>Pelabuhan Perikanan Nusantara Sibolga</h2>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td width="15%"><strong>GT:</strong></td>
                <td width="35%">{{ $filters['gt'] }}</td>
                <td width="15%"><strong>Total Kapal:</strong></td>
                <td width="35%">{{ $total }} Unit</td>
            </tr>
            <tr>
                <td><strong>Alat Tangkap:</strong></td>
                <td>{{ $filters['fishing_gear'] }}</td>
                <td><strong>Rata-rata GT:</strong></td>
                <td>{{ $avg_gt }} GT</td>
            </tr>
            <tr>
                <td><strong>Status SIPI:</strong></td>
                <td>{{ ucfirst($filters['sipi_status']) }}</td>
                <td><strong>Tanggal Cetak:</strong></td>
                <td>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 20px">No</th>
                <th>Nama Kapal</th>
                <th>Pemilik</th>
                <th>No. Izin / Selar</th>
                <th class="text-center">GT</th>
                <th>Alat Tangkap</th>
                <th>Jenis Kapal</th>
                <th>No SIUP</th>
                <th class="text-center">Tgl Akhir SIPI</th>
                <th class="text-center">Status SIPI</th>
                <th class="text-center">Panjang (m)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vessels as $index => $vessel)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-weight: bold">{{ $vessel->vessel_name }}</td>
                <td>{{ $vessel->owner_name ?? '-' }}</td>
                <td>{{ $vessel->license_number ?? $vessel->selar_mark ?? '-' }}</td>
                <td class="text-center">{{ $vessel->gt ?? 0 }}</td>
                <td>{{ $vessel->fishing_gear ?? '-' }}</td>
                <td>{{ $vessel->vessel_type ?? '-' }}</td>
                <td>{{ $vessel->siup_number ?? '-' }}</td>
                <td class="text-center">{{ $vessel->sipi_end_date ? $vessel->sipi_end_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">
                    <span class="status status-{{ $vessel->sipi_status }}">
                        {{ $vessel->sipi_status_text }}
                    </span>
                </td>
                <td class="text-center">{{ $vessel->length ?? 0 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center" style="padding: 20px; color: #9ca3af;">Tidak ada data kapal yang ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh Sistem SAMOSIR v3.0 · Pelabuhan Perikanan Nusantara Sibolga</p>
    </div>
</body>
</html>
