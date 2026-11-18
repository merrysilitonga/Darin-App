<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor Penilaian Akhir Magang - DARIN</title>
    <style>
        @page { margin: 32px 40px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; }

        .kop { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .kop td { border: none; padding: 0; vertical-align: middle; }
        .kop .logo { width: 70px; }
        .kop .logo img { width: 58px; }
        .kop h1 { font-size: 17px; font-weight: normal; margin: 0; }

        .identitas { border-collapse: collapse; margin-bottom: 16px; }
        .identitas td { border: none; padding: 1px 0; font-size: 11.5px; }
        .identitas .label { width: 120px; }
        .identitas .titik { width: 12px; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        table.data th { background: #eef2f7; font-weight: bold; }
        table.data .angka { text-align: center; }
        table.data tr.total td { background: #fff8e1; font-weight: bold; }

        .keterangan { margin-top: 10px; font-size: 10px; color: #555; }

        .ttd { width: 100%; margin-top: 36px; border-collapse: collapse; }
        .ttd td { border: none; vertical-align: top; }
        .ttd .kanan { width: 40%; text-align: center; }

        .footer { margin-top: 18px; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    @php
        // Logo ESDM: utamakan JPG (tidak butuh extension GD).
        $logoJpg = public_path('images/logo-esdm.jpg');
        $logoPng = public_path('images/logo-esdm.png');

        $logoPath = is_file($logoJpg)
            ? $logoJpg
            : ((is_file($logoPng) && extension_loaded('gd')) ? $logoPng : null);

        $unit = collect([$student->division?->name, $student->location?->name])->filter()->join(' / ');

        $mulai = $student->getRawOriginal('start_date');
        $selesai = $student->getRawOriginal('end_date');

        $aspek = [
            'kedisiplinan' => 'Kedisiplinan',
            'kinerja_teknis' => 'Kinerja Teknis',
            'sikap_etika' => 'Sikap & Etika',
            'laporan' => 'Kualitas Laporan Akhir',
        ];

        $predikat = [
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'D' => 'Kurang',
            '-' => '-',
        ];

        $grade = $assessment->grade();
    @endphp

    {{-- KOP --}}
    <table class="kop">
        <tr>
            <td class="logo">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo Kementerian ESDM">
                @endif
            </td>
            <td>
                <h1>Rapor Penilaian Akhir Magang</h1>
            </td>
        </tr>
    </table>

    {{-- IDENTITAS --}}
    <table class="identitas">
        <tr><td class="label">Nama Peserta</td><td class="titik">:</td><td>{{ $student->name }}</td></tr>
        <tr><td class="label">NIM</td><td class="titik">:</td><td>{{ $student->nim ?: '-' }}</td></tr>
        <tr><td class="label">Universitas</td><td class="titik">:</td><td>{{ $student->university ?: '-' }}</td></tr>
        <tr><td class="label">Program Studi</td><td class="titik">:</td><td>{{ $student->study_program ?: '-' }}</td></tr>
        <tr><td class="label">Unit / Lokasi</td><td class="titik">:</td><td>{{ $unit ?: '-' }}</td></tr>
        <tr>
            <td class="label">Periode Magang</td><td class="titik">:</td>
            <td>
                @if ($mulai && $selesai)
                    {{ \Carbon\Carbon::parse($mulai)->locale('id')->translatedFormat('d F Y') }}
                    – {{ \Carbon\Carbon::parse($selesai)->locale('id')->translatedFormat('d F Y') }}
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    {{-- NILAI --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width: 6%;" class="angka">No</th>
                <th>Aspek Penilaian</th>
                <th style="width: 12%;" class="angka">Bobot</th>
                <th style="width: 14%;" class="angka">Nilai (0–100)</th>
                <th style="width: 16%;" class="angka">Nilai × Bobot</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($aspek as $kolom => $label)
                @php $bobot = \App\Models\Assessment::BOBOT[$kolom]; @endphp
                <tr>
                    <td class="angka">{{ $loop->iteration }}</td>
                    <td>{{ $label }}</td>
                    <td class="angka">{{ (int) round($bobot * 100) }}%</td>
                    <td class="angka">{{ rtrim(rtrim(number_format((float) $assessment->{$kolom}, 2, ',', ''), '0'), ',') }}</td>
                    <td class="angka">{{ number_format((float) $assessment->{$kolom} * $bobot, 2, ',', '') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="4">Nilai Akhir</td>
                <td class="angka">{{ number_format($assessment->nilaiAkhir(), 2, ',', '') }}</td>
            </tr>
            <tr class="total">
                <td colspan="4">Huruf Mutu / Predikat</td>
                <td class="angka">{{ $grade }} ({{ $predikat[$grade] }})</td>
            </tr>
        </tbody>
    </table>

    <p class="keterangan">
        Keterangan huruf mutu: A ≥ 85 (Sangat Baik) · B ≥ 75 (Baik) · C ≥ 60 (Cukup) · D &lt; 60 (Kurang).
        @if ($assessment->tanggal_penilaian)
            Dinilai pada {{ $assessment->tanggal_penilaian->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}.
        @endif
    </p>

    {{-- TANDA TANGAN --}}
    <table class="ttd">
        <tr>
            <td></td>
            <td class="kanan">
                Mentor Pembimbing,
                <br><br><br><br><br>
                <strong>{{ $mentor->name }}</strong>
                @if ($mentor->nip)
                    <br>NIP. {{ $mentor->nip }}
                @endif
            </td>
        </tr>
    </table>

    <p class="footer">
        Dicetak dari DARIN pada {{ now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB.
    </p>
</body>
</html>
