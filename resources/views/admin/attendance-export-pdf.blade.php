<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - DARIN</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #222; }
        .header { border-bottom: 2px solid #191712; padding-bottom: 8px; margin-bottom: 12px; }
        .header img { height: 40px; float: left; margin-right: 10px; }
        h1 { font-size: 16px; margin: 0; }
        .sub { color: #666; margin: 2px 0 0; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .hadir { color: #059669; font-weight: bold; }
        .terlambat { color: #d97706; font-weight: bold; }
        .muted { color: #999; }
        .footer { margin-top: 10px; color: #888; font-size: 9px; }
    </style>
</head>
<body>
    @php
        // Logo opsional: laporan tetap terbuat walau file logo / extension GD tidak ada.
        $logoPath = public_path('images/logo-esdm.png');
    @endphp

    <div class="header">
        @if (is_file($logoPath) && extension_loaded('gd'))
            <img src="{{ $logoPath }}" alt="Logo">
        @endif
        <h1>Rekap Absensi Peserta Magang - DARIN</h1>
        <p class="sub">
            Periode: {{ $periodLabel }}
            @foreach ($filterNotes as $note)
                &middot; {{ $note }}
            @endforeach
        </p>
        <p class="sub">Total {{ $attendances->count() }} record</p>
        <div class="clear"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Divisi</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Lokasi</th>
                <th>Jarak (m)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $attendance->date?->locale('id')->translatedFormat('d M Y') ?? '-' }}</td>
                    <td>{{ $attendance->user?->name ?? '-' }}</td>
                    <td>{{ $attendance->user?->division?->name ?? '-' }}</td>
                    <td>{{ $attendance->clock_in ? substr((string) $attendance->clock_in, 0, 5) : '-' }}</td>
                    <td>
                        @if ($attendance->clock_out)
                            {{ substr((string) $attendance->clock_out, 0, 5) }}
                        @else
                            <span class="muted">Belum</span>
                        @endif
                    </td>
                    <td>{{ $attendance->work_location ?? '-' }}</td>
                    <td class="{{ $attendance->status === 'Hadir' ? 'hadir' : ($attendance->status === 'Terlambat' ? 'terlambat' : '') }}">
                        {{ $attendance->status === 'Hadir' ? 'Tepat Waktu' : ($attendance->status ?? '-') }}
                    </td>
                    <td>{{ $attendance->location_name ?? '-' }}</td>
                    <td>
                        {{ $attendance->distance_meters ?? '-' }}
                        @if ($attendance->clock_out)
                            / {{ $attendance->clock_out_distance_meters ?? '-' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding: 16px;">Tidak ada data absensi untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Dicetak dari DARIN pada {{ $generatedAt->locale('id')->translatedFormat('d F Y H:i') }} WIB.
        Jarak ditulis masuk / pulang.
    </p>
</body>
</html>