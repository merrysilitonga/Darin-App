<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .sub { color: #666; margin-top: 2px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        .status-hadir { color: #059669; font-weight: bold; }
        .status-terlambat { color: #d97706; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Riwayat Absensi - DARIN</h1>
    <p class="sub">
        {{ $user->name }} ({{ $user->email }}) &mdash; Periode {{ $periode->translatedFormat('F Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $a)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($a->date)->translatedFormat('l, d F Y') }}</td>
                    <td>{{ $a->clock_in ? \Carbon\Carbon::parse($a->clock_in)->format('H:i') : '-' }}</td>
                    <td>{{ $a->clock_out ? \Carbon\Carbon::parse($a->clock_out)->format('H:i') : '-' }}</td>
                    <td class="status-{{ strtolower($a->status) }}">{{ $a->status }}</td>
                    <td>{{ $a->notes }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center; padding: 20px;">Belum ada data absensi di periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>