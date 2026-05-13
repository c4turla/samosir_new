<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Perhitungan Jasa Peralatan - {{ $service->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .order-no {
            position: absolute;
            top: 0;
            right: 0;
            border: 1px solid #000;
            padding: 5px 10px;
            font-size: 10px;
        }
        .order-no span {
            color: red;
            font-weight: bold;
            font-size: 12px;
        }
        .header h2 {
            margin: 0;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .main-table th {
            text-align: center;
            font-weight: bold;
        }
        .main-table td.center {
            text-align: center;
        }
        .main-table td.right {
            text-align: right;
        }
        .total-row td {
            font-weight: bold;
        }
        .terbilang {
            font-style: italic;
            margin-bottom: 30px;
        }
        .signature-section {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table {
            width: 100%;
        }
        .signature-table td {
            text-align: center;
            width: 33.33%;
            vertical-align: top;
        }
        .signature-space {
            height: 60px;
        }
        .date-location {
            text-align: right;
            margin-bottom: 30px;
            margin-right: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="order-no">No: <span>{{ substr($service->order_number, -4) }}</span></div>
        <h2>DAFTAR PERHITUNGAN JASA PENGGUNAAN PERALATAN<br>PELABUHAN PERIKANAN NUSANTARA SIBOLGA</h2>
    </div>

    <div class="info-section">
        <div class="info-row">Nama Kapal / Penyewa : {{ $service->renter_name }}</div>
        <div class="info-row">Tanggal dan jam masa penggunaan : {{ $service->service_date->format('Y-m-d') }} {{ $service->start_time }}</div>
        <div class="info-row">Tanggal dan jam akhir penggunaan : {{ $service->service_date->format('Y-m-d') }} {{ $service->end_time }}</div>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="30">No.</th>
                <th>Nama Peralatan</th>
                <th width="80">Jumlah</th>
                <th width="150">Biaya Pemakaian (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $equipmentList = [
                    'keranjang_plastik' => 'Keranjang Plastik',
                    'meja_sortir' => 'Meja Sortir',
                    'gerobak' => 'Gerobak',
                    'timbangan' => 'Timbangan',
                    'ice_cruiser' => 'Ice Cruiser'
                ];
                $i = 1;
            @endphp
            @foreach($equipmentList as $key => $label)
                @php
                    $item = $service->items->where('equipment_name', $key)->first();
                @endphp
                <tr>
                    <td class="center">{{ $i++ }}.</td>
                    <td>{{ $label }}</td>
                    <td class="center">{{ $item ? $item->quantity : '' }}</td>
                    <td class="right">Rp {{ number_format($item ? $item->subtotal : 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="center">Total</td>
                <td class="right">Rp {{ number_format($service->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="terbilang">
        Terbilang: {{ ucfirst($terbilang) }}
    </div>

    <div class="date-location">
        Pondok Batu, {{ now()->translatedFormat('d-M-Y') }}
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>Petugas Pelayanan Jasa</td>
                <td></td>
                <td>Bendahara Penerimaan</td>
            </tr>
            <tr>
                <td class="signature-space"></td>
                <td></td>
                <td class="signature-space"></td>
            </tr>
            <tr>
                <td>({{ $service->officer ?? '..........................' }})</td>
                <td></td>
                <td>({{ $service->treasurer ?? '..........................' }})</td>
            </tr>
        </table>
    </div>
</body>
</html>
