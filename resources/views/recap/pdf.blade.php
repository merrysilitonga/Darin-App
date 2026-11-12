<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Magang {{ $intern->name }} - DARIN</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #222; }
        .header { border-bottom: 2px solid #191712; padding-bottom: 8px; margin-bottom: 12px; }
        .header img { height: 40px; float: left; margin-right: 10px; }
        h1 { font-size: 16px; margin: 0; }
        h2 { font-size: 12px; margin: 14px 0 6px; }
        .sub { color: #666; margin: 2px 0 0; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .summary td { text-align: center; }
        .summary .num { font-size: 14px; font-weight: bold; }
        .Hadir { color: #059669; font-weight: bold; }
        .Terlambat { color: #d97706; font-weight: bold; }
        .Izin { color: #2563eb; font-weight: bold; }
        .Sakit { color: #7c3aed; font-weight: bold; }
        .Alfa { color: #dc2626; font-weight: bold; }
        .muted { color: #999; }
        .signature { margin-top: 30px; width: 100%; }
        .signature td { border: none; text-align: center; width: 50%; }
        .footer { margin-top: 10px; color: #888; font-size: 9px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo-esdm.png');
        $jam = fn ($nilai) => $nilai ? substr((string) $nilai, 0, 5) : '-';
    @endphp

    <div class="header">
        @if (is_file($logoPath) && extension_loaded('gd'))
            <img src="{{ $logoPath }}" alt="Logo">
        @endif
        <h1>Rekap Kehadiran Magang - DARIN</h1>
        <p class="sub">
            {{ $intern->name }}{{ $intern->nim ? ' (NIM ' . $intern->nim . ')' : '' }}
            · Divisi: {{ $intern->division?->name ?? '-' }}
            · Mentor: {{ $intern->mentor?->name ?? '-' }}
        </p>
        <p class="sub">
            Periode:
            @if ($start && $end)
                {{ $start->locale('id')->translatedFormat('d F Y') }} – {{ $end->locale('id')->translatedFormat('d F Y') }}
            @else
                belum diatur
            @endif
            ({{ $periodState }})
            @if ($countUntil)
                · dihitung sampai {{ $countUntil->locale('id')->translatedFormat('d F Y') }}
            @endif
        </p>
        <div class="clear"></div>
    </div>

    @if (!$countUntil)
        <p>Belum ada data kehadiran untuk direkap.</p>
    @else
        <table class="summary">
            <tr>
                <th>Hari Kerja</th>
                <th>Tepat Waktu</th>
                <th>Terlambat</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alfa</th>
                <th>Kehadiran</th>
            </tr>
            <tr>
                <td class="num">{{ $summary['effective_workdays'] }}</td>
                <td class="num">{{ $summary['hadir'] }}</td>
                <td class="num">{{ $summary['terlambat'] }}</td>
                <td class="num">{{ $summary['izin'] }}</td>
                <td class="num">{{ $summary['sakit'] }}</td>
                <td class="num">{{ $summary['alfa'] }}</td>
                <td class="num">{{ $summary['attendance_rate'] }}%</td>
            </tr>
        </table>

        <p class="muted">
            Sabtu/Minggu dan {{ $summary['libur'] }} hari libur tidak dihitung sebagai hari kerja.
            Logbook: {{ $logbookStats['total'] }} entri ({{ $logbookStats['Disetujui'] }} disetujui,
            {{ $logbookStats['Menunggu'] }} menunggu, {{ $logbookStats['Ditolak'] }} ditolak, {{ $logbookStats['Draft'] }} draft).
        </p>

        @if ($leaveRequests->isNotEmpty())
            <h2>Pengajuan Izin / Sakit</h2>
            <table>
                <tr>
                    <th>Jenis</th>
                    <th>Tanggal</th>
                    <th>Alasan</th>
                    <th>Status</th>
                </tr>
                @foreach ($leaveRequests as $leave)
                    <tr>
                        <td>{{ $leave->request_type }}</td>
                        <td>
                            {{ $leave->start_date->format('d/m/Y') }}
                            @if (!$leave->start_date->equalTo($leave->end_date))
                                – {{ $leave->end_date->format('d/m/Y') }}
                            @endif
                        </td>
                        <td>{{ $leave->reason }}</td>
                        <td>{{ $leave->status }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        <h2>Rincian Harian</h2>
        <table>
            <tr>
                <th style="width: 18%">Tanggal</th>
                <th style="width: 11%">Status</th>
                <th style="width: 8%">Masuk</th>
                <th style="width: 8%">Pulang</th>
                <th style="width: 8%">Lokasi</th>
                <th>Logbook / Keterangan</th>
            </tr>
            @foreach ($days as $day)
                <tr>
                    <td>{{ $day['date']->locale('id')->translatedFormat('D, d M Y') }}</td>
                    <td class="{{ str_replace(' ', '', $day['status']) }}">{{ $day['status'] }}</td>
                    <td>{{ $jam($day['clock_in']) }}</td>
                    <td>{{ $jam($day['clock_out']) }}</td>
                    <td>{{ $day['work_location'] ?? '-' }}</td>
                    <td>
                        {{ $day['note'] }}
                        @if ($day['logbook'])
                            {{ $day['note'] ? ' · ' : '' }}{{ $day['logbook']->topic }} ({{ $day['logbook']->status }})
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <table class="signature">
        <tr>
            <td></td>
            <td>
                Mengetahui,<br>Mentor Pembimbing
                <br><br><br><br>
                ( {{ $intern->mentor?->name ?? '.............................' }} )
            </td>
        </tr>
    </table>

    <p class="footer">Dibuat oleh sistem DARIN pada {{ $generatedAt->locale('id')->translatedFormat('d F Y, H:i') }} WIB.</p>
</body>
</html>
