<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tanda Bukti Lapor Kedatangan - {{ $arrival->id }}</title>
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
            padding: 2.5px 0;
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
        
        .catch-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 10px;
        }
        .catch-table th, .catch-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 10pt;
        }
        .catch-table th {
            background-color: #f2f2f2;
            text-align: left;
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
        <h4>Surat Tanda Bukti Lapor Kedatangan Kapal Perikanan</h4>
    </div>

    {{-- Nomor --}}
    <div class="nomor-section">
        Nomor : {{ str_pad($arrival->id, 4, '0', STR_PAD_LEFT) }}/PPNS-LDK/{{ \Carbon\Carbon::parse($arrival->arrival_date)->format('m/Y') }}
    </div>

    {{-- Intro --}}
    <p class="intro">Dengan ini menerangkan lapor kedatangan kapal perikanan dengan rincian sebagai berikut :</p>

    {{-- Data Table --}}
    <table class="data-table">
        {{-- 1. Nama Kapal --}}
        <tr>
            <td class="col-no">1.</td>
            <td class="col-label">Nama Kapal</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ strtoupper($arrival->vessel->vessel_name ?? '-') }}</td>
        </tr>

        {{-- 2. Nama Perusahaan / Pemilik --}}
        <tr>
            <td class="col-no">2.</td>
            <td class="col-label">Nama Perusahaan / Pemilik</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ strtoupper($arrival->vessel->owner_name ?? '-') }}</td>
        </tr>

        {{-- 3. Tanda Selar & Alat Tangkap --}}
        <tr>
            <td class="col-no">3.</td>
            <td class="col-label">Tanda Selar</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $arrival->vessel->selar_mark ?? '-' }}</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">Alat Tangkap</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $arrival->vessel->fishing_gear ?? '-' }}</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">Ukuran Kapal</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $arrival->vessel->length ?? '-' }} M (LOA) / {{ $arrival->vessel->gt ?? '-' }} GT</td>
        </tr>

        {{-- 4. Asal Pelabuhan / Fishing Ground --}}
        <tr>
            <td class="col-no">4.</td>
            <td class="col-label">Daerah Penangkapan / Asal</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $arrival->origin ?? '-' }}</td>
        </tr>

        {{-- 5. Dermaga Pendaratan --}}
        <tr>
            <td class="col-no">5.</td>
            <td class="col-label">Dermaga Pendaratan</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $arrival->landingSite->site_name ?? '-' }}</td>
        </tr>

        {{-- 6. Waktu Kedatangan --}}
        <tr>
            <td class="col-no">6.</td>
            <td class="col-label">Waktu Kedatangan</td>
            <td class="col-sep">:</td>
            <td class="col-value">
                @if($arrival->arrival_date)
                    {{ \Carbon\Carbon::parse($arrival->arrival_date)->format('d-m-Y') }} 
                    @if($arrival->arrival_time)
                        {{ \Carbon\Carbon::parse($arrival->arrival_time)->format('H:i:s') }} WIB
                    @endif
                @else
                    -
                @endif
            </td>
        </tr>

        {{-- 7. Kondisi Ikan & Estimasi --}}
        <tr>
            <td class="col-no">7.</td>
            <td class="col-label">Kondisi & Mutu Penanganan</td>
            <td class="col-sep"></td>
            <td class="col-value"></td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;a. Mutu Penanganan</td>
            <td class="col-sep">:</td>
            <td class="col-value">Mutu {{ $arrival->mutu ?? '-' }}</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;b. Produk Dihasilkan</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $arrival->fish_quality ?? '-' }}</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;c. Volume Limbah</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $arrival->waste_volume ? $arrival->waste_volume . ' Kg' : '0 Kg' }}</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;d. Suhu Ikan / Palka</td>
            <td class="col-sep">:</td>
            <td class="col-value">{{ $arrival->fish_temperature ? $arrival->fish_temperature . ' °C' : '-' }} / {{ $arrival->hold_temperature ? $arrival->hold_temperature . ' °C' : '-' }}</td>
        </tr>
        <tr class="sub-row">
            <td class="col-no"></td>
            <td class="col-label">&nbsp;&nbsp;e. Harga Rata-rata</td>
            <td class="col-sep">:</td>
            <td class="col-value">Rp {{ number_format($arrival->average_price, 0, ',', '.') }} / Kg</td>
        </tr>

        {{-- 8. Hasil Tangkapan Ikan --}}
        <tr>
            <td class="col-no">8.</td>
            <td class="col-label" colspan="3">Daftar Hasil Tangkapan Ikan :</td>
        </tr>
    </table>

    <table class="catch-table" style="margin-left: 27px; width: 90%;">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">No</th>
                <th style="width: 50%;">Jenis Ikan</th>
                <th style="width: 40%; text-align: right;">Volume (Kg)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($arrival->catches as $index => $catch)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $catch->fishSpecies->species_name ?? 'Tidak diketahui' }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($catch->weight_kg, 0, ',', '.') }} Kg</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Tidak ada data tangkapan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($arrival->notes)
        <div style="margin-top: 10px; margin-left: 27px;">
            <strong>Catatan Khusus :</strong>
            <p style="margin: 5px 0; font-style: italic;">{{ $arrival->notes }}</p>
        </div>
    @endif

    {{-- Tanda Tangan --}}
    <div class="signature-block">
        <table class="sig-table">
            <tr>
                {{-- Kiri: Petugas / Nakhoda --}}
                <td class="sig-left" style="vertical-align: top;">
                    <p>&nbsp;</p>                    
                    <p>&nbsp;</p>
                    <p>Nakhoda / Pengurus Kapal</p>
                    <div class="sig-space"></div>
                    <p class="sig-name">( .................................... )</p>
                </td>

                {{-- Kanan: Syahbandar --}}
                <td class="sig-right" style="vertical-align: top;">
                    <p>Tapanuli Tengah, {{ \Carbon\Carbon::parse($arrival->arrival_date ?? now())->locale('id')->translatedFormat('d F Y') }}</p>
                    <p>Syahbandar</p>
                    <p>di Pelabuhan Perikanan Nusantara Sibolga</p>
                    <div class="sig-space">
                        @if($syahbandarUser && $syahbandarUser->signature)
                            <img src="{{ public_path('storage/' . $syahbandarUser->signature) }}" class="sig-img" alt="Tanda Tangan">
                        @endif
                    </div>
                    <p class="sig-name">( {{ strtoupper($syahbandarUser->name ?? $arrival->syahbandar ?? '-') }} )</p>
                    @if($syahbandarUser?->nip)
                        <p>NIP : {{ $syahbandarUser->nip }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
