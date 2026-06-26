<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Pemakaian Air Tawar - {{ $service->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12pt;
            color: #000;
            margin: 0;
            padding: 20px;
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
        .header-text h1 {
            font-size: 13pt;
            margin: 0;
            padding: 0;
            text-transform: uppercase;
        }
        .header-text h2 {
            font-size: 12pt;
            margin: 6px 0 0;
            padding: 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .order-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .order-number {
            font-weight: bold;
        }
        .order-number span {
            color: red;
        }
        .date-info {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th {
            padding: 10px;
            text-align: center;
            font-weight: bold;
            background-color: #fff;
        }
        td {
            padding: 10px;
            text-align: center;
        }
        .signature-container {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: left;
            width: 50%;
            text-align: center;
        }
        .signature-space {
            height: 80px;
        }
        .footer {
            clear: both;
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
                <h1>PELABUHAN PERIKANAN NUSANTARA SIBOLGA</h1>
                <h2>ORDER PEMAKAIAN AIR TAWAR</h2>
            </div>
            <div style="display: table-cell; width: 80px;"></div>
        </div>
    </div>

    <div class="order-info">
        <div class="order-number">No Order : <span>{{ substr($service->order_number, -4) }}</span></div>
    </div>

    <div class="date-info">
        Hari / Tanggal : {{ $hari }}, {{ $tanggal }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama Pemakai</th>
                <th>Volume (M3)</th>
                <th>Harga /M3</th>
                <th>Jumlah Pembayaran</th>
                <th style="width: 80px;">Ket</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ strtoupper($service->requester) }}</td>
                <td>{{ number_format($service->volume, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($service->total_payment, 0, ',', '.') }}</td>
                <td>{{ $service->notes ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-container">
        <div class="signature-box">
            <p>Pemohon</p>
            <div class="signature-space"></div>
            <p>({{ $service->requester ?: '....................' }})</p>
        </div>
        <div class="signature-box">
            <p>Petugas Lapangan</p>
            <div class="signature-space"></div>
            <p>({{ $service->field_officer ?: '....................' }})</p>
        </div>
    </div>
    <div class="footer"></div>
</body>
</html>
