<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tanda Bukti Lapor Keberangkatan - {{ $departure->nomor ?? '-' }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }
        .kop-img {
            width: 100%;
            display: block;
            margin-bottom: 0;
        }
        .divider {
            border-top: 4px double #000;
            margin: 4px 0 15px 0;
        }
        .title-section {
            text-align: center;
            margin-bottom: 5px;
        }
        .title-section h4 {
            font-size: 12pt;
            text-decoration: underline;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .nomor-section {
            text-align: right;
            margin-bottom: 10px;
            font-size: 11pt;
        }
        .intro {
            margin-bottom: 10px;
            font-size: 11pt;
        }
        .data-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-left: 5px;
        }
        .data-table td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 11pt;
        }
        .col-no {
            width: 22px;
            vertical-align: top;
        }
        .col-label {
            width: 230px;
            vertical-align: top;
        }
        .col-sep {
            width: 15px;
            text-align: center;
            vertical-align: top;
        }
        .col-value {
            vertical-align: top;
        }
        .sub-row td {
            padding-top: 1px;
            padding-bottom: 1px;
        }
        .muat-row {
            margin-top: 3px;
        }
        .signature-block {
            margin-top: 30px;
            width: 100%;
        }
        .sig-table {
            width: 100%;
            border: none;
        }
        .sig-table td {
            vertical-align: top;
            font-size: 11pt;
        }
        .sig-left {
            width: 50%;
            text-align: left;
        }
        .sig-right {
            width: 50%;
            text-align: left;
        }
        .signature-block p {
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .sig-space {
            height: 75px;
            display: block;
        }
        .sig-img {
            max-height: 75px;
            width: auto;
        }
        .sig-name {
            font-weight: bold;
        }
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    {{-- Kop Surat --}}
    <img src="{{ public_path('img/kop.png') }}" class="kop-img" alt="Kop Surat">

    {{-- Judul --}}
    <div class="title-section">
        <h4>Surat Tanda Bukti Lapor Keberangkatan Kapal Perikanan</h4>
    </div>

    {{-- Nomor --}}
    <div class="nomor-section">
        Nomor : {{ $departure->nomor ?? '-' }}
    </div>

    {{-- Intro --}}
    <p class="intro">Dengan ini memberikan persetujuan keluar kepada :</p>

    {{-- Data Table --}}
    <table class="data-table">
        {{-- 1. Nama Kapal --}}
        <tr>
            <td class="col-no">1.</td>
            <td class="col-label">Nama Kapal</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ strtoupper($departure->vessel->vessel_name ?? '-') }}</td>
        </tr>

        {{-- 2. Nama Perusahaan --}}
        <tr>
            <td class="col-no">2.</td>
            <td class="col-label">Nama Perusahaan</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ strtoupper($departure->vessel->owner_name ?? '-') }}</td>
        </tr>

        {{-- 3. Nama Nakhoda --}}
        <tr>
            <td class="col-no">3.</td>
            <td class="col-label">Nama Nakhoda</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->nakhoda_name ?? '-' }}</td>
        </tr>

        {{-- 4. Tanda Selar --}}
        <tr>
            <td class="col-no">4.</td>
            <td class="col-label">Tanda Selar</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->vessel->selar_mark ?? '-' }}
            </td>
        </tr>

        {{-- Alat Tangkap (sub of 4) --}}
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">Alat Tangkap</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->vessel->fishing_gear ?? '-' }}</td>
        </tr>

        {{-- Ukuran Kapal (sub of 4) --}}
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">Ukuran Kapal</td>
            <td class="col-sep">:</td>
            <td class="col-value"></td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;a. Panjang Kapal (LOA)</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->vessel->length ?? '-' }} Meter</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;b. Berat Kotor</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->vessel->gt ?? '-' }} GT</td>
        </tr>

        {{-- 5. Merek/Kekuatan Mesin --}}
        <tr>
            <td class="col-no">5.</td>
            <td class="col-label">Merek/Kekuatan Mesin</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->vessel->engine_power ?? '-' }}</td>
        </tr>

        {{-- 6. Tanggal dan Jam Masuk --}}
        <tr>
            <td class="col-no">6.</td>
            <td class="col-label">Tanggal dan Jam Masuk</td>
            <td class="col-sep">:</td>
            <td class="col-value">
                @if($departure->arrival_datetime)
                    {{ \Carbon\Carbon::parse($departure->arrival_datetime)->format('d-m-Y H:i:s') }} WIB
                @else
                    -
                @endif
            </td>
        </tr>

        {{-- 7. Tanggal dan Jam Keberangkatan --}}
        <tr>
            <td class="col-no">7.</td>
            <td class="col-label">Tanggal dan Jam Keberangkatan</td>
            <td class="col-sep">:</td>
            <td class="col-value">
                @if($departure->departure_datetime)
                    {{ \Carbon\Carbon::parse($departure->departure_datetime)->format('d-m-Y H:i:s') }} WIB
                @else
                    -
                @endif
            </td>
        </tr>

        {{-- 8. Telah Melakukan Kegiatan --}}
        <tr>
            <td class="col-no">8.</td>
            <td class="col-label">Telah Melakukan Kegiatan</td>
            <td class="col-sep"></td>
            <td class="col-value"></td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;a. Tambat</td>
            <td class="col-sep">:</td>
            <td class="col-value">
                @if($departure->etmal_days)
                    {{ $departure->etmal_days }} Etmal
                    &nbsp;&nbsp; Total : {{ $departure->etmal_hours ?? '0 Jam 0 Menit' }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;b. Floating</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->floating_status ?? '-' }}</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;c. Bongkar Ikan</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->unloading_status ?? '' }} Kg</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;d. Muat</td>
            <td class="col-sep">:</td>
            <td class="col-value"></td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td colspan="3">
                <table style="width:100%; border:none; font-size:11pt;">
                    <tr>
                        <td style="width:33%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Es : {{ $departure->ice_supply ?? 0 }} Kg</td>
                        <td style="width:33%">b. Air : {{ $departure->water_supply ?? 0 }} Liter</td>
                        <td style="width:34%">c. Solar : {{ $departure->diesel_supply ?? 0 }} Liter</td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d. Olie : {{ $departure->oil_supply ?? 0 }} Liter</td>
                        <td>e. Umpan : {{ $departure->gasoline_supply ?? 0 }}</td>
                        <td>f. Lain-lain : {{ $departure->other_supplies ?? '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- 9. Penyelesaian Administrasi Pelabuhan --}}
        <tr>
            <td class="col-no">9.</td>
            <td class="col-label">Penyelesaian Administrasi Pelabuhan</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->admin_completion ?? 'Check Point' }}</td>
        </tr>

        {{-- 10. Tujuan Keberangkatan --}}
        <tr>
            <td class="col-no">10.</td>
            <td class="col-label">Tujuan Keberangkatan</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $departure->destination ?? '-' }}</td>
        </tr>
    </table>

    {{-- Tanda Tangan --}}
    <div class="signature-block">
        <table class="sig-table">
            <tr>
                {{-- Kiri: Nakhoda --}}
                <td class="sig-left" style="vertical-align: top;">
                    <p>&nbsp;</p>                    
                    <p>&nbsp;</p>
                    <p>Nakhoda</p>
                    <div class="sig-space"></div>
                    <p class="sig-name">( {{ $departure->nakhoda_name ?? '-' }} )</p>
                </td>

                {{-- Kanan: Syahbandar --}}
                <td class="sig-right" style="vertical-align: top;">
                    <p>Tapanuli Tengah, {{ \Carbon\Carbon::parse($departure->departure_date ?? now())->locale('id')->translatedFormat('d F Y') }}</p>
                    <p>Syahbandar</p>
                    <p>di Pelabuhan Perikanan Nusantara Sibolga</p>
                    <div class="sig-space">
                        @if($syahbandarUser && $syahbandarUser->signature)
                            <img src="{{ public_path('storage/' . $syahbandarUser->signature) }}" class="sig-img" alt="Tanda Tangan">
                        @endif
                    </div>
                    <p class="sig-name">( {{ strtoupper($syahbandarUser->name ?? $departure->syahbandar ?? '-') }} )</p>
                    @if($syahbandarUser?->nip)
                        <p>NIP : {{ $syahbandarUser->nip }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
