<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keberangkatan</title>
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
        .status-aktif { background-color: #dbeafe; color: #1e40af; }
        .status-berlayar { background-color: #fef3c7; color: #92400e; }
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
        <h1>LAPORAN KEBERANGKATAN KAPAL</h1>
        <h2>Pelabuhan Perikanan Nusantara Sibolga</h2>
    </div>

    <div class="meta">
        <div style="float: left; width: 40%;">
            <p><strong>Periode:</strong> {{ $dateFrom }} s/d {{ $dateTo }}</p>
            @if($status)
            <p><strong>Status:</strong> {{ $status }}</p>
            @endif
        </div>
        <div style="float: right; width: 50%; text-align: right;">
            <p><strong>Total Data:</strong> {{ $total }} keberangkatan</p>
            <p><strong>Total ABK:</strong> {{ number_format($totalCrews, 0, ',', '.') }} orang</p>
            <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 20px">No</th>
                <th style="width: 80px">Nomor SKP</th>
                <th style="width: 80px">Tgl Masuk</th>
                <th style="width: 80px">Tgl Keluar</th>
                <th>Kapal / Nakhoda</th>
                <th style="width: 80px">Tujuan</th>
                <th class="text-center" style="width: 40px">Etmal</th>
                <th style="width: 100px">Status Kegiatan</th>
                <th class="text-center" style="width: 60px">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departures as $index => $departure)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $departure->nomor ?? '-' }}</td>
                <td>
                    {{ $departure->arrival_datetime ? \Carbon\Carbon::parse($departure->arrival_datetime)->format('d/m/Y H:i') : '-' }}
                </td>
                <td>
                    @if($departure->departure_datetime)
                        {{ \Carbon\Carbon::parse($departure->departure_datetime)->format('d/m/Y H:i') }}
                    @else
                        {{ $departure->departure_date instanceof \Carbon\Carbon ? $departure->departure_date->format('d/m/Y') : \Carbon\Carbon::parse($departure->departure_date)->format('d/m/Y') }}
                        {{ $departure->departure_time }}
                    @endif
                </td>
                <td>
                    <strong>{{ $departure->vessel->vessel_name ?? '-' }}</strong><br>
                    <span style="color: #666">Nakhoda: {{ $departure->nakhoda_name ?? '-' }}</span>
                </td>
                <td>
                    {{ $departure->destination ?? '-' }}<br>
                    <span style="color: #666; font-size: 7px">ABK: {{ $departure->crew_count ?? 0 }} Org</span>
                </td>
                <td class="text-center">
                    {{ $departure->etmal_days ?? 0 }} Etmal<br>
                    {{ $departure->etmal_hours ?? 0 }} Jam
                </td>
                <td>
                    F: {{ $departure->floating_status ?? '-' }}<br>
                    B: {{ $departure->unloading_status ?? '-' }}<br>
                    A: {{ $departure->admin_completion ?? '-' }}
                </td>
                <td class="text-center">
                    <span class="status status-{{ strtolower($departure->status ?? 'aktif') }}">
                        {{ $departure->status ?? '-' }}
                    </span>
                    <div style="font-size: 6px; margin-top: 2px; color: {{ $departure->approval_status ? '#059669' : '#d97706' }}">
                        {{ $departure->approval_status ? 'APPROVED' : 'PENDING' }}
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #9ca3af;">Tidak ada data keberangkatan pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; width: 100%;">
        <div style="float: right; width: 250px; text-align: center;">
            <p>Sibolga, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin-top: 5px; font-weight: bold;">SYAHBANDAR</p>
            <div style="margin-top: 50px; border-bottom: 1px solid #333; width: 180px; margin-left: auto; margin-right: auto;">
                {{ $departures->first()->syahbandar ?? '..........................................' }}
            </div>
            <p style="font-size: 7px; margin-top: 2px;">Petugas Pengawas Kesyahbandaran</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh Sistem SAMOSIR v3.0 · Pelabuhan Perikanan Nusantara Sibolga</p>
    </div>
</body>
</html>
