<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Pemakaian Peralatan - {{ $service->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header-inner {
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }
        .header-logo img {
            width: 70px;
            height: auto;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .header-text h2 {
            margin: 0;
            text-transform: uppercase;
            font-size: 13px;
            font-weight: bold;
        }
        .header-text h3 {
            margin: 4px 0 0;
            font-size: 12px;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 2px 0;
        }
        .info-table td:first-child {
            width: 120px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .main-table th {
            background-color: #f2f2f2;
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
        }
        .main-table td.center {
            text-align: center;
        }
        .footer-section {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table {
            width: 100%;
        }
        .signature-table td {
            text-align: center;
            width: 50%;
        }
        .signature-space {
            height: 60px;
        }
        .footer-note {
            margin-top: 50px;
            text-align: center;
            font-style: italic;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-inner">
            <div class="header-logo">
                <img src="{{ public_path('img/logo_kkp.png') }}" alt="Logo KKP">
            </div>
            <div class="header-text">
                <h2>PELABUHAN PERIKANAN NUSANTARA SIBOLGA</h2>
                <h3>ORDER PEMAKAIAN PERALATAN</h3>
                <p style="margin: 4px 0 0; font-size: 11px;">No Order : {{ $service->order_number }}</p>
            </div>
            <div style="display: table-cell; width: 80px;"></div>
        </div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Nama Penyewa</td>
                <td>: {{ $service->renter_name }}</td>
            </tr>
            <tr>
                <td>Hari / Tanggal</td>
                <td>: {{ $service->service_date->translatedFormat('l, d-m-Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Peralatan</th>
                <th width="100">Jumlah</th>
                <th>KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($service->items as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $item->getEquipmentLabel() }}</td>
                <td class="center">{{ $item->quantity }} Unit</td>
                <td>{{ $item->notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-section">
        <p style="text-align: right; margin-right: 50px;">Pondok Batu, {{ now()->translatedFormat('d-M-Y') }}</p>
        <table class="signature-table">
            <tr>
                <td>Petugas Lapangan</td>
                <td>Pengguna Jasa</td>
            </tr>
            <tr>
                <td class="signature-space"></td>
                <td class="signature-space"></td>
            </tr>
            <tr>
                <td>({{ $service->officer ?? '..........................' }})</td>
                <td>({{ $service->renter_name }})</td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Mari Awasi dan Laporkan Semua Bentuk Korupsi. WA : 0813 9666 9717, 0811 662 484
    </div>
</body>
</html>
