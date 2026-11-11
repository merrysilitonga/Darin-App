@extends('layouts.mentor-layout')

@section('title', 'Mentoring & Kehadiran - DARIN')
@section('page_title', 'Mentoring & Rekap Kehadiran')
@section('page_subtitle', 'Kelola pengajuan izin dan sakit serta pantau rekap kehadiran mahasiswa bimbingan.')

@section('content')

<div class="space-y-6">

    {{-- RINGKASAN --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Mahasiswa Bimbingan</p>
            <p class="mt-3 text-3xl font-bold text-slate-800">
                {{ $stats['students'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Pengajuan Menunggu</p>
            <p class="mt-3 text-3xl font-bold text-amber-600">
                {{ $stats['pending_requests'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total Kehadiran</p>
            <p class="mt-3 text-3xl font-bold text-emerald-600">
                {{ $stats['hadir'] }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Termasuk status terlambat
            </p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total Ketidakhadiran</p>
            <p class="mt-3 text-3xl font-bold text-red-500">
                {{ $stats['alpa'] }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Hari kerja tanpa absensi atau izin sah
            </p>
        </div>

    </section>

    {{-- INFORMASI PENUGASAN --}}
    @if ($assignmentMissing)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="font-bold text-amber-800">
                Belum Ada Mahasiswa Bimbingan
            </p>
            <p class="mt-1 text-sm leading-6 text-amber-700">
                Saat ini belum ada mahasiswa magang yang ditugaskan kepada akun Mentor ini.
                Hubungi Admin/HR untuk melakukan penugasan melalui Manajemen Pengguna.
            </p>
        </div>
    @endif

    {{-- BAGIAN 1: PENGAJUAN IZIN DAN SAKIT --}}
    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

        <div class="flex flex-col justify-between gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:p-6">

            <div>
                <h2 class="text-sm font-bold text-slate-800">
                    Pengajuan Izin &amp; Sakit
                </h2>

                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Pengajuan mahasiswa bimbingan yang masih menunggu persetujuan Mentor.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                {{ $pendingRequests->count() }} Pengajuan Menunggu
            </span>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[950px] border-collapse text-left">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Mahasiswa
                        </th>

                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Jenis
                        </th>

                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Periode Izin
                        </th>

                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Alasan
                        </th>

                        <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Dokumen
                        </th>

                        <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($pendingRequests as $item)

                        <tr class="align-top transition hover:bg-slate-50/70">

                            {{-- MAHASISWA --}}
                            <td class="min-w-44 px-4 py-4">
                                <p class="text-xs font-bold text-slate-800">
                                    {{ $item->user?->name ?? 'Pengguna tidak ditemukan' }}
                                </p>

                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ $item->user?->nim ?? $item->user?->email ?? '-' }}
                                </p>
                            </td>

                            {{-- JENIS --}}
                            <td class="whitespace-nowrap px-4 py-4">

                                @if ($item->request_type === 'Sakit')
                                    <span class="inline-flex rounded-lg bg-blue-50 px-2.5 py-1.5 text-[11px] font-bold text-blue-700">
                                        Sakit
                                    </span>
                                @else
                                    <span class="inline-flex rounded-lg bg-violet-50 px-2.5 py-1.5 text-[11px] font-bold text-violet-700">
                                        {{ $item->request_type }}
                                    </span>
                                @endif

                            </td>

                            {{-- PERIODE --}}
                            <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-600">
                                <p>
                                    {{ $item->start_date?->format('d M Y') ?? '-' }}
                                </p>

                                <p class="mt-1 text-slate-400">
                                    s.d. {{ $item->end_date?->format('d M Y') ?? '-' }}
                                </p>
                            </td>

                            {{-- ALASAN --}}
                            <td class="min-w-56 max-w-sm px-4 py-4">
                                <p class="whitespace-pre-line break-words text-xs leading-5 text-slate-600">
                                    {{ $item->reason }}
                                </p>
                            </td>

                            {{-- DOKUMEN --}}
                            <td class="whitespace-nowrap px-4 py-4 text-center">

                                @if ($item->document_path)
                                    <a
                                        href="{{ route('mentor.leave-requests.document', $item->id) }}"
                                        class="inline-flex rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-[11px] font-bold text-blue-700 transition hover:bg-blue-100"
                                    >
                                        Unduh Dokumen
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">
                                        Tidak tersedia
                                    </span>
                                @endif

                            </td>

                            {{-- AKSI --}}
                            <td class="min-w-48 px-4 py-4">

                                <div class="flex flex-col gap-2">

                                    {{-- SETUJUI --}}
                                    <form
                                        action="{{ route('mentor.leave-requests.approve', $item->id) }}"
                                        method="POST"
                                        {{-- Js::from() meng-encode nilai sebagai literal JS (kutip di-hex-escape), mencegah XSS lewat nama pengguna. --}}
                                        onsubmit="return confirm({{ Js::from('Setujui pengajuan ' . $item->request_type . ' dari ' . ($item->user?->name ?? '-') . '?') }})"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="w-full rounded-lg bg-emerald-500 px-3 py-2 text-[11px] font-bold text-white transition hover:bg-emerald-600"
                                        >
                                            Setujui Pengajuan
                                        </button>
                                    </form>

                                    {{-- TOLAK --}}
                                    <details class="group">
                                        <summary class="cursor-pointer list-none rounded-lg bg-red-50 px-3 py-2 text-center text-[11px] font-bold text-red-600 transition hover:bg-red-100">
                                            Tolak Pengajuan
                                        </summary>

                                        <form
                                            action="{{ route('mentor.leave-requests.reject', $item->id) }}"
                                            method="POST"
                                            class="mt-3 space-y-2 rounded-lg border border-red-100 bg-red-50/50 p-3"
                                        >
                                            @csrf

                                            <label
                                                for="review-notes-{{ $item->id }}"
                                                class="block text-[11px] font-bold text-red-700"
                                            >
                                                Alasan penolakan
                                            </label>

                                            <textarea
                                                id="review-notes-{{ $item->id }}"
                                                name="review_notes"
                                                rows="3"
                                                maxlength="2000"
                                                required
                                                placeholder="Tuliskan alasan penolakan..."
                                                class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-xs outline-none focus:border-red-400"
                                            ></textarea>

                                            <button
                                                type="submit"
                                                class="w-full rounded-lg bg-red-600 px-3 py-2 text-[11px] font-bold text-white transition hover:bg-red-700"
                                                onclick="return confirm('Yakin menolak pengajuan ini?')"
                                            >
                                                Konfirmasi Penolakan
                                            </button>
                                        </form>

                                    </details>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="text-3xl">📋</div>

                                <p class="mt-3 text-sm font-bold text-slate-700">
                                    Tidak Ada Pengajuan Menunggu
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Pengajuan izin dan sakit mahasiswa bimbingan yang menunggu persetujuan akan muncul di sini.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="border-t border-gray-100 bg-gray-50 px-5 py-3">
            <p class="text-[11px] text-slate-400">
                Data pengajuan diambil langsung dari database DARIN.
                Hanya pengajuan berstatus Menunggu yang ditampilkan.
            </p>
        </div>

    </section>

    {{-- BAGIAN 2: FILTER REKAP --}}
    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">

        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

            <div>
                <h2 class="text-sm font-bold text-slate-800">
                    Rekapitulasi Absensi Bulanan
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Rekap mahasiswa bimbingan untuk periode {{ $monthLabel }}.
                </p>
            </div>

            <form
                action="{{ route('mentor.attendance') }}"
                method="GET"
                class="flex flex-wrap items-end gap-2"
            >

                <div>
                    <label
                        for="attendance_month"
                        class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500"
                    >
                        Periode
                    </label>

                    <input
                        type="month"
                        id="attendance_month"
                        name="month"
                        value="{{ $month }}"
                        class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                    >
                </div>

                <button
                    type="submit"
                    class="rounded-xl bg-[#ffc107] px-4 py-2.5 text-xs font-bold text-black transition hover:bg-amber-400"
                >
                    Terapkan
                </button>

                <a
                    href="{{ route('mentor.attendance') }}"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-gray-50"
                >
                    Reset
                </a>

            </form>

        </div>

    </section>

    {{-- BAGIAN 3: REKAP KEHADIRAN --}}
    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

        <div class="border-b border-gray-100 p-5 sm:p-6">

            <h2 class="text-sm font-bold text-slate-800">
                Rekap Kehadiran Mahasiswa Bimbingan
            </h2>

            <p class="mt-1 text-xs leading-5 text-slate-500">
                Data hadir, izin, sakit, dan alpa berdasarkan database absensi serta pengajuan yang telah disetujui.
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[950px] border-collapse text-left">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            No.
                        </th>

                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Mahasiswa
                        </th>

                        <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Hari Kerja
                        </th>

                        <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wide text-emerald-600">
                            Hadir (H)
                        </th>

                        <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wide text-violet-600">
                            Izin (I)
                        </th>

                        <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wide text-blue-600">
                            Sakit (S)
                        </th>

                        <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wide text-red-600">
                            Alpa (A)
                        </th>

                        <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wide text-slate-400">
                            Kehadiran
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($attendanceSummaries as $row)

                        @php
                            $student = $row['student'];
                        @endphp

                        <tr class="transition hover:bg-slate-50/70">

                            <td class="px-4 py-4 text-xs text-slate-400">
                                {{ $loop->iteration }}
                            </td>

                            <td class="min-w-52 px-4 py-4">

                                <p class="text-xs font-bold text-slate-800">
                                    {{ $student->name }}
                                </p>

                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ $student->nim ?? $student->email }}
                                </p>

                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ $student->division?->name ?? 'Divisi belum ditetapkan' }}
                                </p>

                                @if (!$row['valid_period'])
                                    <span class="mt-2 inline-flex rounded-full bg-red-50 px-2 py-1 text-[10px] font-bold text-red-600">
                                        Periode magang belum lengkap
                                    </span>
                                @endif

                            </td>

                            <td class="px-4 py-4 text-center text-xs font-medium text-slate-600">
                                {{ $row['effective_days'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-emerald-600">
                                {{ $row['hadir'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-violet-600">
                                {{ $row['izin'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-blue-600">
                                {{ $row['sakit'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-red-500">
                                {{ $row['alpa'] }}
                            </td>

                            <td class="px-4 py-4 text-center">

                                <span class="inline-flex rounded-lg px-2.5 py-1.5 text-[11px] font-bold
                                    {{ $row['percentage'] >= 90
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : ($row['percentage'] >= 75
                                            ? 'bg-amber-50 text-amber-700'
                                            : 'bg-red-50 text-red-700') }}">
                                    {{ number_format($row['percentage'], 1, ',', '.') }}%
                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">

                                <p class="text-sm font-bold text-slate-700">
                                    Belum Ada Rekap Kehadiran
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Data mahasiswa bimbingan akan ditampilkan setelah penugasan Mentor tersedia.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

                {{-- $attendanceSummaries berupa array (bukan Collection), jadi pakai count(). --}}
                @if (count($attendanceSummaries) > 0)
                    <tfoot class="border-t border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-4 py-4 text-xs font-bold text-slate-700">
                                Total seluruh mahasiswa
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-slate-700">
                                {{ $stats['effective_days'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-emerald-600">
                                {{ $stats['hadir'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-violet-600">
                                {{ $stats['izin'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-blue-600">
                                {{ $stats['sakit'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-red-500">
                                {{ $stats['alpa'] }}
                            </td>

                            <td class="px-4 py-4 text-center text-xs font-bold text-slate-500">
                                -
                            </td>
                        </tr>
                    </tfoot>
                @endif

            </table>

        </div>

        <div class="border-t border-gray-100 bg-gray-50 px-5 py-4">
            <p class="text-[11px] leading-5 text-slate-400">
                H dihitung dari absensi berstatus Hadir dan Terlambat.
                I dan S hanya menghitung pengajuan yang Disetujui Mentor.
                Alpa merupakan hari kerja efektif yang belum memiliki absensi maupun pengajuan izin/sakit yang disetujui.
                Sabtu, Minggu, hari libur aktif, dan tanggal setelah hari ini tidak dihitung.
            </p>
        </div>

    </section>

</div>

@endsection