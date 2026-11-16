<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rekap Magang {{ $intern->name }} - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fcfbf9;
        }
    </style>
</head>

<body class="text-gray-800">

@php
    $statusClass = [
        'Hadir' => 'bg-green-50 text-green-700',
        'Terlambat' => 'bg-amber-50 text-amber-700',
        'Izin' => 'bg-blue-50 text-blue-700',
        'Sakit' => 'bg-purple-50 text-purple-700',
        'Alfa' => 'bg-red-50 text-red-700',
        'Libur' => 'bg-gray-100 text-gray-600',
        'Akhir Pekan' => 'bg-gray-100 text-gray-500',
        'Belum Absen' => 'bg-gray-50 text-gray-400',
    ];

    $jam = fn ($nilai) => $nilai ? substr((string) $nilai, 0, 5) : '-';
@endphp

<div class="flex min-h-screen">

    @include('layouts.sidebar')

    <main class="flex-1 min-w-0 overflow-y-auto p-5 md:p-10">

        {{-- HEADER --}}
        <header class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                @if ($backUrl)
                    <a href="{{ $backUrl }}" class="mb-2 inline-block text-xs font-semibold text-amber-700 hover:underline">
                        ← Kembali
                    </a>
                @endif

                <h1 class="text-2xl font-bold tracking-tight">
                    Rekap Magang: {{ $intern->name }}
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $intern->nim ? 'NIM ' . $intern->nim . ' · ' : '' }}
                    {{ $intern->division?->name ?? 'Divisi belum diatur' }} ·
                    Mentor: {{ $intern->mentor?->name ?? '-' }}
                </p>

                <p class="mt-1 text-sm text-gray-500">
                    Periode:
                    @if ($start && $end)
                        {{ $start->locale('id')->translatedFormat('d F Y') }} – {{ $end->locale('id')->translatedFormat('d F Y') }}
                    @else
                        belum diatur
                    @endif
                    <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-600">{{ $periodState }}</span>
                </p>

                <div class="mt-3 h-1 w-16 rounded-full bg-amber-400"></div>
            </div>

            <a href="{{ $pdfUrl }}"
               class="inline-flex items-center justify-center rounded-xl bg-[#191712] px-5 py-3 text-sm font-bold text-white transition hover:bg-gray-800">
                ⬇ Unduh PDF
            </a>
        </header>

        @if (!$countUntil)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-800">
                @if ($periodState === 'Belum dimulai')
                    Periode magang belum dimulai, sehingga belum ada data untuk direkap.
                @else
                    Periode magang belum diatur oleh Admin/HR, sehingga rekap belum dapat dibuat.
                @endif
            </div>
        @else

            {{-- RINGKASAN --}}
            <section class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-7">
                @php
                    $kartuRingkasan = [
                        ['Hari Kerja', $summary['effective_workdays'], 'text-gray-900'],
                        ['Tepat Waktu', $summary['hadir'], 'text-green-600'],
                        ['Terlambat', $summary['terlambat'], 'text-amber-600'],
                        ['Izin', $summary['izin'], 'text-blue-600'],
                        ['Sakit', $summary['sakit'], 'text-purple-600'],
                        ['Alfa', $summary['alfa'], 'text-red-600'],
                        ['Kehadiran', $summary['attendance_rate'] . '%', 'text-gray-900'],
                    ];
                @endphp
                @foreach ($kartuRingkasan as $kartu)
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500">{{ $kartu[0] }}</p>
                        <p class="mt-1 text-2xl font-extrabold {{ $kartu[2] }}">{{ $kartu[1] }}</p>
                    </div>
                @endforeach
            </section>

            <p class="mb-6 text-xs text-gray-400">
                Dihitung sampai {{ $countUntil->locale('id')->translatedFormat('d F Y') }}.
                Sabtu/Minggu dan {{ $summary['libur'] }} hari libur tidak dihitung sebagai hari kerja.
                Persentase kehadiran = (tepat waktu + terlambat) ÷ hari kerja.
            </p>

            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- LOGBOOK --}}
                <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold">Logbook</h2>
                    <div class="grid grid-cols-5 gap-2 text-center text-sm">
                        @php
                            $kartuLogbook = [
                                ['Total', $logbookStats['total'], 'text-gray-900'],
                                ['Disetujui', $logbookStats['Disetujui'], 'text-green-600'],
                                ['Menunggu', $logbookStats['Menunggu'], 'text-amber-600'],
                                ['Ditolak', $logbookStats['Ditolak'], 'text-red-600'],
                                ['Draft', $logbookStats['Draft'], 'text-gray-500'],
                            ];
                        @endphp
                        @foreach ($kartuLogbook as $kartu)
                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xl font-extrabold {{ $kartu[2] }}">{{ $kartu[1] }}</p>
                                <p class="text-xs text-gray-500">{{ $kartu[0] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- PENGAJUAN IZIN --}}
                <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold">Pengajuan Izin / Sakit</h2>

                    @forelse ($leaveRequests as $leave)
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 py-2 text-sm last:border-0">
                            <span>
                                <span class="font-semibold">{{ $leave->request_type }}</span>
                                · {{ $leave->start_date->format('d/m/Y') }}
                                @if (!$leave->start_date->equalTo($leave->end_date))
                                    – {{ $leave->end_date->format('d/m/Y') }}
                                @endif
                            </span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-bold
                                {{ $leave->status === 'Disetujui' ? 'bg-green-50 text-green-700' : ($leave->status === 'Ditolak' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
                                {{ $leave->status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Tidak ada pengajuan izin/sakit.</p>
                    @endforelse
                </section>
            </div>

            {{-- RINCIAN HARIAN --}}
            <section class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-6">
                    <h2 class="text-lg font-bold">Rincian Harian</h2>
                    <p class="mt-1 text-sm text-gray-500">Hari kerja dan hari libur selama periode magang.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-3 font-bold">Tanggal</th>
                                <th class="px-5 py-3 font-bold">Status</th>
                                <th class="px-5 py-3 font-bold">Masuk</th>
                                <th class="px-5 py-3 font-bold">Pulang</th>
                                <th class="px-5 py-3 font-bold">Lokasi</th>
                                <th class="px-5 py-3 font-bold">Logbook / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse (array_reverse($days) as $day)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-3 font-semibold text-gray-700">
                                        {{ $day['date']->locale('id')->translatedFormat('D, d M Y') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass[$day['status']] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $day['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">{{ $jam($day['clock_in']) }}</td>
                                    <td class="px-5 py-3">{{ $jam($day['clock_out']) }}</td>
                                    <td class="px-5 py-3">{{ $day['work_location'] ?? '-' }}</td>
                                    <td class="max-w-sm px-5 py-3 text-gray-600">
                                        @if ($day['note'])
                                            <p class="font-semibold">{{ $day['note'] }}</p>
                                        @endif
                                        @if ($day['logbook'])
                                            <p>{{ $day['logbook']->topic }}
                                                <span class="text-xs text-gray-400">({{ $day['logbook']->status }})</span>
                                            </p>
                                        @elseif (!$day['note'])
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <p class="mt-6 text-xs text-gray-400">
            Dibuat {{ $generatedAt->locale('id')->translatedFormat('d F Y, H:i') }} WIB.
        </p>

    </main>

</div>

</body>
</html>
