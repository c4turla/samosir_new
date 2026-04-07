<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Penentuan Lokasi Penimbangan - {{ $unloading->reference_number }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
            position: relative;
        }
        .header table {
            width: 100%;
            border: none;
        }
        .logo {
            width: 80px;
        }
        .header-text {
            text-align: center;
        }
        .header-text h1 {
            font-size: 13pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #0000FF;
        }
        .header-text h2 {
            font-size: 14pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #0000FF;
        }
        .header-text h3 {
            font-size: 15pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #0000FF;
        }
        .header-text p {
            font-size: 8pt;
            margin: 2px 0;
            font-weight: normal;
        }
        .title-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-section h4 {
            font-size: 12pt;
            text-decoration: underline;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .title-section p {
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .intro {
            margin-bottom: 15px;
        }
        .data-table {
            width: 100%;
            border: none;
            margin-left: 10px;
        }
        .data-table td {
            vertical-align: top;
            padding-bottom: 6px;
        }
        .label {
            width: 30px;
        }
        .field-name {
            width: 220px;
        }
        .separator {
            width: 15px;
            text-align: center;
        }
        .signature-section {
            float: right;
            width: 300px;
            margin-top: 20px;
            text-align: left;
        }
        .signature-section p {
            margin: 0;
        }
        .signature-space {
            height: 70px;
        }
        .footer-note {
            position: fixed;
            bottom: 0;
            font-size: 9pt;
            font-style: italic;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    <img src="{{ public_path('img/logobaru.png') }}" style="width: 80px;">
                </td>
                <td class="header-text">
                    <h1>KEMENTERIAN KELAUTAN DAN PERIKANAN</h1>
                    <h2>DIREKTORAT JENDERAL PERIKANAN TANGKAP</h2>
                    <h3>PELABUHAN PERIKANAN NUSANTARA SIBOLGA</h3>
                    <p>JALAN JENDERAL GATOT SUBROTO, PONDOK BATU, KECAMATAN SARUDIK,</p>
                    <p>KABUPATEN TAPANULI TENGAH, PROVINSI SUMATERA UTARA 22616</p>
                    <p>TELEPON (0631) 22129, FAKSIMILI (0631) 22129</p>
                    <p>LAMAN https://kkp.go.id/djpt/ppnsibolga, EMAIL ppn.sibolga@kkp.go.id</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="title-section">
        <h4>SURAT PENENTUAN LOKASI PENIMBANGAN IKAN</h4>
        <p>Nomor : {{ $unloading->reference_number }}</p>
    </div>

    <div class="content">
        <p class="intro">Penentuan lokasi untuk melakukan penimbangan ikan hasil tangkapan diberikan kepada:</p>
        
        <table class="data-table">
            <tr>
                <td class="label">1.</td>
                <td class="field-name">Nama Nakhoda/Nelayan</td>
                <td class="separator">:</td>
                <td>{{ strtoupper($unloading->captain_name ?: '-') }}</td>
            </tr>
            <tr>
                <td class="label">2.</td>
                <td class="field-name">Nama Kapal</td>
                <td class="separator">:</td>
                <td>{{ strtoupper($unloading->arrival->vessel->vessel_name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">3.</td>
                <td class="field-name">Alat Penangkap Ikan</td>
                <td class="separator">:</td>
                <td>{{ strtoupper($unloading->arrival->vessel->fishing_gear ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">4.</td>
                <td class="field-name">Tanda Pengenal Kapal/Tanda Selar</td>
                <td class="separator">:</td>
                <td>{{ strtoupper($unloading->identification_mark ?: '-') }}</td>
            </tr>
            <tr>
                <td class="label">5.</td>
                <td class="field-name">Ukuran Kapal (GT)</td>
                <td class="separator">:</td>
                <td>{{ $unloading->arrival->vessel->gt ?? '-' }} GT</td>
            </tr>
            <tr>
                <td class="label">6.</td>
                <td class="field-name">Jam / No. Urut bongkar</td>
                <td class="separator">:</td>
                <td>{{ $unloading->unloading_time ? \Carbon\Carbon::parse($unloading->unloading_time)->format('H:i') : '00:00' }} / {{ $unloading->sequence_number ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">7.</td>
                <td class="field-name">Nomor STBL Kedatangan</td>
                <td class="separator">:</td>
                <td>{{ $unloading->bl_code ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">8.</td>
                <td class="field-name">Lokasi Penimbangan</td>
                <td class="separator">:</td>
                <td>{{ strtoupper($unloading->landingSite->site_name ?? '-') }}</td>
            </tr>
        </table>
    </div>

    <div class="clearfix">
        <div class="signature-section">
            <p>Sibolga, {{ $unloading->registration_date ? \Carbon\Carbon::parse($unloading->registration_date)->isoFormat('D MMMM Y') : $today }}</p>
            <br>
            <p>A.n Kepala Pelabuhan Perikanan</p>
            <p>Nusantara Sibolga,</p>
            <br>
            <p>Syahbandar di PPN Sibolga</p>
            <div class="signature-space">
                @if($unloading->syahbandar && $unloading->syahbandar->signature)
                    <img src="{{ public_path('storage/' . $unloading->syahbandar->signature) }}" style="max-height: 80px; width: auto; margin-top: -10px;">
                @endif
            </div>
            <p><strong>({{ strtoupper($unloading->syahbandar->name ?? '-') }})</strong></p>
            @if($unloading->syahbandar?->nip)
            <p>NIP. {{ $unloading->syahbandar->nip }}</p>
            @endif
        </div>
    </div>

    <div class="footer-note">
        <p>*Peraturan Menteri Kelautan dan Perikanan Nomor : 17 tahun 2024.</p>
        <p>Surat penentuan lokasi ini hanya berlaku untuk 1 (satu) kali kegiatan penimbangan.</p>
    </div>
</body>
</html>
