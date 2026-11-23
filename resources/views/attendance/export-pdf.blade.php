<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Peserta Magang - DARIN</title>
    <style>
        @page { margin: 32px 40px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; }

        .kop { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .kop td { border: none; padding: 0; vertical-align: middle; }
        .kop .logo { width: 70px; }
        .kop .logo img { width: 58px; }
        .kop h1 { font-size: 17px; font-weight: normal; margin: 0; }

        .identitas { border-collapse: collapse; margin-bottom: 14px; }
        .identitas td { border: none; padding: 1px 0; font-size: 11.5px; }
        .identitas .label { width: 110px; }
        .identitas .titik { width: 12px; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; vertical-align: top; }
        table.data th { background: #eef2f7; font-weight: bold; }
        tr.baris-izin td { background: #f5f8ff; }
        tr.baris-alfa td { background: #fff5f5; }
        tr.baris-libur td { background: #f9fafb; color: #6b7280; }

        .hadir { color: #059669; font-weight: bold; }
        .terlambat { color: #d97706; font-weight: bold; }
        .izin { color: #2563eb; font-weight: bold; }
        .sakit { color: #7c3aed; font-weight: bold; }
        .ditolak { color: #dc2626; font-weight: bold; }
        .menunggu { color: #6b7280; font-weight: bold; }

        .ringkasan { margin-top: 10px; font-size: 10.5px; color: #444; }

        .ttd { width: 100%; margin-top: 36px; border-collapse: collapse; }
        .ttd td { border: none; vertical-align: top; }
        .ttd .kanan { width: 40%; text-align: center; }

        .footer { margin-top: 18px; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    @php
        /*
         * Logo ESDM: utamakan JPG (tidak butuh extension GD),
         * PNG hanya dipakai jika extension GD aktif.
         */
        $logoJpg = public_path('images/logo-esdm.jpg');
        $logoPng = public_path('images/logo-esdm.png');

        $logoPath = is_file($logoJpg)
            ? $logoJpg
            : ((is_file($logoPng) && extension_loaded('gd')) ? $logoPng : null);

        $jam = fn ($nilai) => $nilai ? \Carbon\Carbon::parse($nilai)->format('H:i') : '-';

        $unit = collect([$user->division?->name, $user->location?->name])->filter()->join(' / ');
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
                <h1>Laporan Absensi Peserta Magang</h1>
            </td>
        </tr>
    </table>

    {{-- IDENTITAS --}}
    <table class="identitas">
        <tr><td class="label">Nama Peserta</td><td class="titik">:</td><td>{{ $user->name }}</td></tr>
        <tr><td class="label">Universitas</td><td class="titik">:</td><td>{{ $user->university ?: '-' }}</td></tr>
        <tr><td class="label">Unit / Lokasi</td><td class="titik">:</td><td>{{ $unit ?: '-' }}</td></tr>
        <tr><td class="label">Bulan Laporan</td><td class="titik">:</td><td>{{ $periode->locale('id')->translatedFormat('F Y') }}</td></tr>
    </table>

    {{-- TABEL RIWAYAT --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width: 13%;">Tanggal</th>
                <th style="width: 9%;">Hari</th>
                <th style="width: 10%;">Jam Masuk</th>
                <th style="width: 10%;">Jam Pulang</th>
                <th style="width: 18%;">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($riwayat as $baris)
                @php $tanggal = \Carbon\Carbon::parse($baris['tanggal'])->locale('id'); @endphp

                @if (in_array($baris['jenis'], ['alfa', 'libur'], true))
                    <tr class="{{ $baris['jenis'] === 'alfa' ? 'baris-alfa' : 'baris-libur' }}">
                        <td>{{ $tanggal->format('d/m/Y') }}</td>
                        <td>{{ $tanggal->translatedFormat('l') }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="{{ $baris['jenis'] === 'alfa' ? 'ditolak' : 'menunggu' }}">{{ $baris['jenis'] === 'alfa' ? 'Alfa' : 'Libur' }}</td>
                        <td>{{ $baris['keterangan'] ?? '' }}</td>
                    </tr>
                @elseif ($baris['jenis'] === 'izin')
                    @php
                        $izin = $baris['leave'];
                        $kelas = match ($izin->status) {
                            'Disetujui' => strtolower($izin->request_type),
                            'Ditolak' => 'ditolak',
                            default => 'menunggu',
                        };
                    @endphp
                    <tr class="baris-izin">
                        <td>{{ $tanggal->format('d/m/Y') }}</td>
                        <td>{{ $tanggal->translatedFormat('l') }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="{{ $kelas }}">{{ $izin->request_type }} ({{ $izin->status }})</td>
                        <td>{{ $izin->reason }}</td>
                    </tr>
                @else
                    @php $a = $baris['attendance']; @endphp
                    <tr>
                        <td>{{ $tanggal->format('d/m/Y') }}</td>
                        <td>{{ $tanggal->translatedFormat('l') }}</td>
                        <td>{{ $jam($a->clock_in) }}</td>
                        <td>{{ $jam($a->clock_out) }}</td>
                        <td class="{{ strtolower((string) $a->status) }}">{{ $a->status === 'Hadir' ? 'Hadir (Tepat Waktu)' : ($a->status ?? '-') }}</td>
                        <td>{{ $a->work_location === 'WFA' ? 'WFA (luar kantor) · ' : '' }}{{ $a->location_name ?? '' }}{{ $a->notes ? ($a->location_name ? ' · ' : '') . $a->notes : '' }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 16px;">Belum ada data absensi maupun izin pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="ringkasan">
        Ringkasan: Hadir tepat waktu {{ $ringkasan['hadir'] }} hari ·
        Terlambat {{ $ringkasan['terlambat'] }} hari ·
        Izin {{ $ringkasan['izin'] }} hari ·
        Sakit {{ $ringkasan['sakit'] }} hari ·
        Alfa {{ $ringkasan['alfa'] ?? 0 }} hari
        @if ($ringkasan['menunggu'] > 0)
            · {{ $ringkasan['menunggu'] }} pengajuan izin masih menunggu persetujuan
        @endif
    </p>

    {{-- TANDA TANGAN --}}
    <table class="ttd">
        <tr>
            <td></td>
            <td class="kanan">
                Disetujui Oleh,
                <br><br><br><br><br>
                <strong>{{ $user->mentor?->name ?? '(....................................)' }}</strong>
                @if ($user->mentor?->nip)
                    <br>NIP. {{ $user->mentor->nip }}
                @endif
            </td>
        </tr>
    </table>

    <p class="footer">
        Dicetak dari DARIN pada {{ now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB.
    </p>
</body>
</html>
