<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Tangkapan</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
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
        }
        .meta p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .total-row {
            background-color: #f3f4f6 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
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
        <h1>LAPORAN HASIL TANGKAPAN</h1>
        <h2>Pelabuhan Perikanan Nusantara Sibolga</h2>
    </div>

    <div class="meta">
        <p><strong>Periode:</strong> {{ $dateFrom }} s/d {{ $dateTo }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px">No</th>
                <th>Tanggal</th>
                <th>Nama Kapal</th>
                <th>Jenis Ikan</th>
                <th>Nama Lokal</th>
                <th class="text-right">Berat (kg)</th>
                <th class="text-right">Estimasi Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($catches as $index => $catch)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $catch->arrival->arrival_date->format('d/m/Y') }}</td>
                <td>{{ $catch->arrival->vessel->vessel_name ?? '-' }}</td>
                <td>{{ $catch->fishSpecies->species_name ?? '-' }}</td>
                <td>{{ $catch->fishSpecies->local_name ?? '-' }}</td>
                <td class="text-right">{{ number_format($catch->weight_kg, 0, ',', '.') }} kg</td>
                <td class="text-right">{{ number_format($catch->estimated_value, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px; color: #9ca3af;">Tidak ada data tangkapan pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($total_weight, 0, ',', '.') }} kg</td>
                <td class="text-right">Rp {{ number_format($total_value, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Sistem Informasi SAMOSIR v3.0 · PPN Sibolga</p>
    </div>
</body>
</html>
