@extends('layouts.mentor-layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Mentor')
@section('page_subtitle', 'Pantau ringkasan aktivitas mahasiswa bimbingan Anda hari ini.')

@section('content')
@php
    $mentor = auth()->user();

    // Data mahasiswa yang dikirim dari controller.
    $mentorStudents = collect($students ?? []);

    $mentorTotalStudents = (int) (
        $totalStudents
        ?? $mentorStudents->count()
    );

    // Statistik kehadiran: gunakan data controller jika tersedia.
    $mentorPresentToday = (int) (
        $presentToday
        ?? $hadirHariIni
        ?? 0
    );

    $mentorLeaveToday = (int) (
        $leaveToday
        ?? $izinHariIni
        ?? 0
    );

    $mentorAttendancePercentage = $mentorTotalStudents > 0
        ? (int) round(
            ($mentorPresentToday / $mentorTotalStudents) * 100
        )
        : 0;

    // Pengajuan yang masih menunggu tindakan.
    $mentorPendingLogbooks = collect(
        $pendingLogbooks ?? $pendingLogbook ?? []
    );

    $mentorPendingLeaves = collect(
        $pendingLeaves
        ?? $pendingLeaveRequests
        ?? $leaveRequests
        ?? []
    );

    // Gabungkan notifikasi logbook dan pengajuan izin.
    $mentorPendingItems = collect();

    foreach ($mentorPendingLogbooks as $item) {
        $mentorPendingItems->push([
            'student' => data_get(
                $item,
                'user.name',
                data_get($item, 'student.name', 'Mahasiswa')
            ),
            'type' => 'Logbook Harian',
            'description' => data_get(
                $item,
                'activity',
                data_get(
                    $item,
                    'description',
                    data_get($item, 'title', 'Logbook menunggu validasi.')
                )
            ),
            'url' => route('mentor.logbook'),
        ]);
    }

    foreach ($mentorPendingLeaves as $item) {
        $mentorPendingItems->push([
            'student' => data_get(
                $item,
                'user.name',
                data_get($item, 'student.name', 'Mahasiswa')
            ),
            'type' => 'Pengajuan Izin',
            'description' => data_get(
                $item,
                'reason',
                data_get(
                    $item,
                    'notes',
                    data_get($item, 'description', 'Pengajuan menunggu persetujuan.')
                )
            ),
            'url' => route('mentor.attendance'),
        ]);
    }

    $mentorPendingCount = $mentorPendingItems->count();

    $mentorName = $mentor?->name ?? 'Mentor';
@endphp

<div class="space-y-5">

    {{-- BANNER SELAMAT DATANG --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#191712] to-[#302c23] p-6 shadow-sm">
        <div class="relative z-10">
            <p class="text-xs font-medium text-amber-400">
                PORTAL MENTOR DARIN
            </p>

            <h3 class="mt-2 text-lg font-semibold leading-7 text-white">
                Selamat datang, {{ $mentorName }}.
            </h3>

            <p class="mt-2 max-w-2xl text-xs font-normal leading-6 text-gray-300">
                Pantau kehadiran mahasiswa, validasi logbook harian,
                dan tinjau pengajuan izin melalui satu halaman.
            </p>
        </div>

        <div aria-hidden="true"
             class="pointer-events-none absolute -right-3 -top-8 select-none text-8xl font-bold text-white/[0.04]">
            DARIN
        </div>
    </section>

    {{-- KARTU STATISTIK --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2">

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">
                Total Mahasiswa Bimbingan
            </p>

            <div class="mt-3 flex items-baseline gap-2">
                <h4 class="text-3xl font-semibold text-slate-700">
                    {{ $mentorTotalStudents }}
                </h4>
                <span class="text-xs font-normal text-slate-400">
                    orang
                </span>
            </div>

            <p class="mt-2 text-xs font-normal leading-5 text-slate-400">
                Mahasiswa yang terdaftar sebagai bimbingan Anda.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">
                Kehadiran Hari Ini
            </p>

            <div class="mt-3 flex items-baseline gap-2">
                <h4 class="text-3xl font-semibold text-emerald-600">
                    {{ $mentorAttendancePercentage }}%
                </h4>
            </div>

            <p class="mt-2 text-xs font-normal leading-5 text-slate-400">
                {{ $mentorPresentToday }} hadir,
                {{ $mentorLeaveToday }} izin.
            </p>
        </div>

    </section>

    {{-- NOTIFIKASI LOGBOOK DAN IZIN --}}
    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">

        <div class="flex flex-col justify-between gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-sm font-semibold leading-6 text-slate-700">
                    Pemberitahuan dan Tindakan
                </h3>

                <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                    Logbook dan pengajuan izin yang memerlukan pemeriksaan Anda.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full px-3 py-1.5 text-xs font-medium
                {{ $mentorPendingCount > 0
                    ? 'bg-amber-50 text-amber-700'
                    : 'bg-emerald-50 text-emerald-700' }}">
                {{ $mentorPendingCount }} menunggu
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">

                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="whitespace-nowrap px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Nama Mahasiswa
                        </th>

                        <th class="whitespace-nowrap px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Jenis
                        </th>

                        <th class="min-w-64 px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Keterangan / Aktivitas
                        </th>

                        <th class="whitespace-nowrap px-3 py-3 text-center text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Tindakan
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50">

                    @forelse ($mentorPendingItems as $item)
                        <tr class="transition hover:bg-slate-50/70">

                            <td class="whitespace-nowrap px-3 py-3">
                                <span class="text-xs font-medium text-slate-700">
                                    {{ $item['student'] }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3">
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-[11px] font-medium
                                    {{ $item['type'] === 'Logbook Harian'
                                        ? 'bg-amber-50 text-amber-700'
                                        : 'bg-blue-50 text-blue-700' }}">
                                    {{ $item['type'] }}
                                </span>
                            </td>

                            <td class="min-w-64 px-3 py-3 text-xs font-normal leading-5 text-slate-500">
                                {{ $item['description'] }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-center">
                                <a href="{{ $item['url'] }}"
                                   class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-3 py-2 text-xs font-medium text-slate-800 transition hover:bg-amber-300">
                                    Tinjau
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center">
                                <p class="text-sm font-medium text-slate-600">
                                    Tidak ada tindakan tertunda
                                </p>

                                <p class="mt-2 text-xs font-normal leading-5 text-slate-400">
                                    Pengajuan izin dan logbook yang menunggu validasi akan tampil di sini.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </section>

    {{-- TAUTAN AKSES CEPAT --}}
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">

        <a href="{{ route('mentor.interns') }}"
           class="rounded-xl border border-gray-100 bg-white p-4 transition hover:border-amber-200 hover:bg-amber-50/30">
            <span class="text-sm font-medium text-slate-700">
                Mahasiswa Bimbingan
            </span>
            <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                Lihat daftar mahasiswa yang ditugaskan kepada Anda.
            </p>
        </a>

        <a href="{{ route('mentor.logbook') }}"
           class="rounded-xl border border-gray-100 bg-white p-4 transition hover:border-amber-200 hover:bg-amber-50/30">
            <span class="text-sm font-medium text-slate-700">
                Validasi Logbook
            </span>
            <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                Periksa dan validasi aktivitas harian mahasiswa.
            </p>
        </a>

        <a href="{{ route('mentor.attendance') }}"
           class="rounded-xl border border-gray-100 bg-white p-4 transition hover:border-amber-200 hover:bg-amber-50/30">
            <span class="text-sm font-medium text-slate-700">
                Mentoring &amp; Kehadiran
            </span>
            <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                Periksa rekap absensi dan pengajuan izin.
            </p>
        </a>

    </section>

</div>
@endsection