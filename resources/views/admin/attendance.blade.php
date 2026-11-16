@extends('layouts.admin-layout')

{{--
    Rekap Absensi & Logbook — Admin/HR.
    Variabel dari AdminController::attendance():
    $attendances, $logbooks, $leaveRequests, $divisions, $mentors,
    $stats, $workdayStats, $workdayPeriodLabel,
    $search, $month, $divisionId, $mentorId, $perPage
--}}

@section('content')
@php
    $formatJam = fn ($time) => $time ? \Illuminate\Support\Carbon::parse($time)->format('H:i') : null;

    $leaveBadge = [
        'Menunggu' => 'bg-amber-50 text-amber-700',
        'Disetujui' => 'bg-emerald-50 text-emerald-700',
        'Ditolak' => 'bg-red-50 text-red-600',
    ];
@endphp

<div class="mx-auto max-w-7xl space-y-6">

    {{-- HEADER --}}
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-amber-600">
            Monitoring Kehadiran
        </p>
        <h1 class="mt-1 text-2xl font-extrabold text-gray-900">
            Rekap Absensi &amp; Logbook
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Pantau kehadiran, pengajuan izin/sakit, dan logbook seluruh mahasiswa magang.
        </p>
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- FILTER --}}
    <form
        method="GET"
        action="{{ route('admin.attendance') }}"
        class="grid grid-cols-1 gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-5"
    >
        <div class="lg:col-span-2">
            <label for="search" class="mb-1 block text-xs font-bold text-gray-600">Cari Nama / Email</label>
            <input
                id="search"
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Contoh: Budi"
                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
            >
        </div>

        <div>
            <label for="month" class="mb-1 block text-xs font-bold text-gray-600">Bulan</label>
            <input
                id="month"
                type="month"
                name="month"
                value="{{ $month }}"
                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
            >
        </div>

        <div>
            <label for="division_id" class="mb-1 block text-xs font-bold text-gray-600">Divisi</label>
            <select
                id="division_id"
                name="division_id"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
            >
                <option value="">Semua divisi</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" @selected((string) $divisionId === (string) $division->id)>
                        {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="mentor_id" class="mb-1 block text-xs font-bold text-gray-600">Mentor</label>
            <select
                id="mentor_id"
                name="mentor_id"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
            >
                <option value="">Semua mentor</option>
                @foreach ($mentors as $mentor)
                    <option value="{{ $mentor->id }}" @selected((string) $mentorId === (string) $mentor->id)>
                        {{ $mentor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-5 lg:justify-end">
            <div class="mr-auto">
                <label for="per_page" class="mb-1 block text-xs font-bold text-gray-600">Baris per halaman</label>
                <select
                    id="per_page"
                    name="per_page"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-amber-500"
                >
                    @foreach ([10, 25, 50, 100] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <a
                href="{{ route('admin.attendance') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
            >
                Reset
            </a>
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-[#ffc107] px-5 py-2.5 text-sm font-bold text-[#191712] shadow-sm transition hover:bg-amber-400"
            >
                Terapkan Filter
            </button>
        </div>
    </form>

    {{-- RINGKASAN HARI KERJA EFEKTIF --}}
    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-1 sm:flex-row sm:items-end">
            <div>
                <h2 class="text-sm font-bold text-gray-800">Ringkasan Hari Kerja Efektif</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Periode: {{ $workdayPeriodLabel }} &middot; {{ $workdayStats['students'] }} mahasiswa
                    &middot; {{ $workdayStats['holiday_dates'] }} tanggal libur aktif dikecualikan
                </p>
            </div>
            <p class="text-[11px] text-gray-400">Sabtu, Minggu, dan hari libur tidak dihitung.</p>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-5">
            @foreach ([
                ['Hari Kerja Efektif', $workdayStats['effective_workdays'], 'text-gray-900'],
                ['Hadir', $workdayStats['attendance_days'], 'text-emerald-600'],
                ['Izin', $workdayStats['leave_days'], 'text-blue-600'],
                ['Sakit', $workdayStats['sick_days'], 'text-purple-600'],
                ['Alpa', $workdayStats['alpha_days'], 'text-red-600'],
            ] as [$label, $value, $color])
                <div class="rounded-xl bg-gray-50 p-4">
                    <p class="text-xs font-medium text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-extrabold {{ $color }}">{{ $value }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- STATISTIK RECORD --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Record Absensi</p>
            <p class="mt-2 text-3xl font-extrabold text-gray-900">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $stats['mahasiswa'] }} mahasiswa</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Tepat Waktu / Terlambat</p>
            <p class="mt-2 text-3xl font-extrabold">
                <span class="text-emerald-600">{{ $stats['hadir'] }}</span>
                <span class="text-gray-300">/</span>
                <span class="text-amber-600">{{ $stats['terlambat'] }}</span>
            </p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Pengajuan Izin/Sakit</p>
            <p class="mt-2 text-3xl font-extrabold text-gray-900">{{ $stats['leave_total'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                {{ $stats['leave_approved'] }} disetujui &middot; {{ $stats['leave_pending'] }} menunggu &middot; {{ $stats['leave_rejected'] }} ditolak
            </p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Logbook</p>
            <p class="mt-2 text-3xl font-extrabold text-gray-900">{{ $stats['logbook'] }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $stats['logbook_menunggu'] }} menunggu review</p>
        </div>
    </div>

    {{-- TABEL ABSENSI --}}
    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        @php
            // Ekspor membawa filter aktif, tapi tidak membawa pagination.
            $exportQuery = array_filter(
                request()->only(['search', 'month', 'division_id', 'mentor_id']),
                fn ($value) => $value !== null && $value !== ''
            );
        @endphp

        <div class="flex flex-col justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-sm font-bold text-gray-800">Data Absensi</h2>
                <p class="mt-1 text-xs text-gray-500">
                    Satu baris per mahasiswa per tanggal (masuk &amp; pulang digabung).
                    Ekspor berisi seluruh {{ $stats['total'] }} record hasil filter.
                </p>
            </div>
            <div class="flex gap-2">
                <a
                    href="{{ route('admin.attendance.export-csv', $exportQuery) }}"
                    class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100"
                >
                    Ekspor Excel (CSV)
                </a>
                <a
                    href="{{ route('admin.attendance.export-pdf', $exportQuery) }}"
                    class="inline-flex items-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100"
                >
                    Ekspor PDF
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Mahasiswa</th>
                        <th class="px-4 py-3">Masuk</th>
                        <th class="px-4 py-3">Pulang</th>
                        <th class="px-4 py-3">Lokasi &amp; Jarak</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($attendances as $attendance)
                        <tr class="align-top hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                {{ $attendance->date?->locale('id')->translatedFormat('D, d M Y') ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-800">{{ $attendance->user?->name ?? 'Akun terhapus' }}</p>
                                <p class="text-xs text-gray-400">{{ $attendance->user?->division?->name ?? 'Tanpa divisi' }}</p>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                <p class="font-semibold text-gray-800">{{ $formatJam($attendance->clock_in) ?? '-' }}</p>
                                @if ($attendance->work_location)
                                    <span class="mt-1 inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-600">
                                        {{ $attendance->work_location }}
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($attendance->clock_out)
                                    <p class="font-semibold text-gray-800">{{ $formatJam($attendance->clock_out) }}</p>
                                    <p class="text-[11px] text-emerald-600">Lengkap</p>
                                @else
                                    <p class="text-gray-400">-</p>
                                    <p class="text-[11px] text-amber-600">Belum absen pulang</p>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-xs text-gray-600">
                                <p>{{ $attendance->location_name ?? '-' }}</p>
                                <p class="text-gray-400">
                                    Masuk: {{ $attendance->distance_meters !== null ? $attendance->distance_meters . ' m' : '-' }}
                                    @if ($attendance->clock_out)
                                        &middot; Pulang: {{ $attendance->clock_out_distance_meters !== null ? $attendance->clock_out_distance_meters . ' m' : '-' }}
                                    @endif
                                </p>
                                @if ($attendance->latitude !== null && $attendance->longitude !== null)
                                    <a
                                        href="https://www.google.com/maps?q={{ $attendance->latitude }},{{ $attendance->longitude }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-600 hover:underline"
                                    >Lihat peta</a>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($attendance->status === 'Hadir')
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">Tepat Waktu</span>
                                @elseif ($attendance->status === 'Terlambat')
                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700">Terlambat</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-bold text-gray-600">{{ $attendance->status ?? '-' }}</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-center text-xs">
                                <div class="flex flex-col gap-1">
                                    @if ($attendance->photo_path)
                                        <a
                                            href="{{ route('admin.attendance.photo', $attendance) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="font-semibold text-blue-600 hover:underline"
                                        >Masuk</a>
                                    @endif
                                    @if ($attendance->clock_out_photo_path)
                                        <a
                                            href="{{ route('admin.attendance.photo', ['attendance' => $attendance, 'type' => 'pulang']) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="font-semibold text-blue-600 hover:underline"
                                        >Pulang</a>
                                    @endif
                                    @if (!$attendance->photo_path && !$attendance->clock_out_photo_path)
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">
                                Belum ada data absensi untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($attendances->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">
                {{ $attendances->links() }}
            </div>
        @endif
    </section>

    {{-- TABEL IZIN / SAKIT --}}
    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-sm font-bold text-gray-800">Pengajuan Izin &amp; Sakit</h2>
            <p class="mt-1 text-xs text-gray-500">Persetujuan dilakukan oleh mentor masing-masing mahasiswa.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Mahasiswa</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Alasan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Diproses Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($leaveRequests as $leave)
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-800">{{ $leave->user?->name ?? 'Akun terhapus' }}</p>
                                <p class="text-xs text-gray-400">Mentor: {{ $leave->user?->mentor?->name ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $leave->request_type }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-600">
                                {{ $leave->start_date?->format('d M Y') ?? '-' }}
                                <span class="text-gray-400">s.d.</span>
                                {{ $leave->end_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="max-w-xs px-4 py-3">
                                <p class="whitespace-pre-line break-words text-xs text-gray-600">{{ $leave->reason }}</p>
                                @if ($leave->status === 'Ditolak' && $leave->review_notes)
                                    <p class="mt-1 break-words text-xs text-red-500">Catatan: {{ $leave->review_notes }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $leaveBadge[$leave->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $leave->status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-600">
                                {{ $leave->reviewer?->name ?? '-' }}
                                @if ($leave->reviewed_at)
                                    <p class="text-gray-400">{{ $leave->reviewed_at->format('d M Y H:i') }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">
                                Belum ada pengajuan izin/sakit untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- TABEL LOGBOOK --}}
    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-sm font-bold text-gray-800">Logbook</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Mahasiswa</th>
                        <th class="px-4 py-3">Topik</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logbooks as $logbook)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $logbook->date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-800">{{ $logbook->user?->name ?? 'Akun terhapus' }}</p>
                                <p class="text-xs text-gray-400">{{ $logbook->user?->division?->name ?? 'Tanpa divisi' }}</p>
                            </td>
                            <td class="max-w-md px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $logbook->topic ?? '-' }}</p>
                                <p class="mt-0.5 line-clamp-2 break-words text-xs text-gray-500">{{ $logbook->description }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-bold text-gray-700">
                                    {{ $logbook->status ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-sm text-gray-400">
                                Belum ada logbook untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logbooks->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">
                {{ $logbooks->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
