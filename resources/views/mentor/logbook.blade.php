@extends('layouts.mentor-layout')

@section('title', 'Validasi Logbook')
@section('page_title', 'Validasi Logbook & Aktivitas Harian')
@section('page_subtitle', 'Tinjau laporan harian mahasiswa bimbingan dan berikan persetujuan atau catatan revisi.')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DATA LOGBOOK
    |--------------------------------------------------------------------------
    */

    // Hanya tampilkan logbook yang sudah dikirim atau pernah diproses.
    // Draft mahasiswa tidak ditampilkan kepada Mentor.
    $submittedLogbooks = collect($logbooks ?? [])
        ->filter(function ($logbook) {
            return in_array(
                $logbook->status,
                ['Menunggu', 'Disetujui', 'Ditolak'],
                true
            );
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | STATISTIK LOGBOOK
    |--------------------------------------------------------------------------
    */

    $totalLogbooks = $submittedLogbooks->count();

    $pendingCount = $submittedLogbooks
        ->where('status', 'Menunggu')
        ->count();

    $approvedCount = $submittedLogbooks
        ->where('status', 'Disetujui')
        ->count();

    $rejectedCount = $submittedLogbooks
        ->where('status', 'Ditolak')
        ->count();
@endphp

<div class="space-y-5">

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="mb-2 text-sm font-semibold text-red-700">
                Logbook belum dapat diproses:
            </p>

            @foreach ($errors->all() as $error)
                <p class="text-xs leading-5 text-red-600">
                    {{ $error }}
                </p>
            @endforeach
        </div>
    @endif

    {{-- HEADER INFORMASI --}}
    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">

        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

            <div>
                <h3 class="text-sm font-semibold leading-6 text-slate-700">
                    Daftar Logbook Masuk
                </h3>

                <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                    Tinjau laporan kegiatan harian mahasiswa bimbingan,
                    kemudian berikan persetujuan atau catatan revisi.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700">
                {{ $pendingCount }} Menunggu Pemeriksaan
            </span>

        </div>

    </section>

    {{-- KONDISI BELUM ADA MAHASISWA BIMBINGAN --}}
    @if ($assignmentMissing)

        <section class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-12 text-center">

            <h3 class="text-sm font-semibold text-amber-800">
                Belum Ada Mahasiswa Bimbingan
            </h3>

            <p class="mx-auto mt-2 max-w-lg text-xs leading-6 text-amber-700">
                Belum ada mahasiswa yang ditugaskan kepada akun Mentor ini.
                Silakan hubungi Admin/HR untuk mengatur penugasan mahasiswa.
            </p>

        </section>

    @else

        {{-- STATISTIK --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- TOTAL LOGBOOK --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">
                    Total Logbook Masuk
                </p>

                <h3 class="mt-3 text-3xl font-semibold text-slate-700">
                    {{ $totalLogbooks }}
                </h3>

                <p class="mt-1 text-xs text-slate-400">
                    Seluruh logbook yang sudah dikirim atau diproses.
                </p>
            </div>

            {{-- MENUNGGU --}}
            <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">
                    Menunggu
                </p>

                <h3 class="mt-3 text-3xl font-semibold text-amber-600">
                    {{ $pendingCount }}
                </h3>

                <p class="mt-1 text-xs text-slate-400">
                    Menunggu pemeriksaan Mentor.
                </p>
            </div>

            {{-- DISETUJUI --}}
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">
                    Disetujui
                </p>

                <h3 class="mt-3 text-3xl font-semibold text-emerald-600">
                    {{ $approvedCount }}
                </h3>

                <p class="mt-1 text-xs text-slate-400">
                    Logbook yang sudah disetujui.
                </p>
            </div>

            {{-- DITOLAK --}}
            <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">
                    Perlu Revisi
                </p>

                <h3 class="mt-3 text-3xl font-semibold text-red-600">
                    {{ $rejectedCount }}
                </h3>

                <p class="mt-1 text-xs text-slate-400">
                    Logbook yang pernah ditolak.
                </p>
            </div>

        </section>

        {{-- DAFTAR LOGBOOK --}}
        <section class="space-y-4">

            <div>
                <h3 class="text-sm font-semibold text-slate-700">
                    Daftar Aktivitas Mahasiswa
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    Logbook dari mahasiswa yang ditugaskan kepada Anda.
                </p>
            </div>

            @forelse ($submittedLogbooks as $logbook)

                @php
                    $status = $logbook->status;

                    $statusClass = match ($status) {
                        'Menunggu' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'Disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'Ditolak' => 'bg-red-50 text-red-700 border-red-100',
                        default => 'bg-gray-100 text-gray-600 border-gray-200',
                    };

                    $studentName = $logbook->user?->name ?? 'Mahasiswa';

                    $initials = collect(
                        preg_split('/\s+/', trim($studentName))
                    )
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->implode('');
                @endphp

                <article class="space-y-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-amber-200 sm:p-6">

                    {{-- IDENTITAS DAN STATUS --}}
                    <div class="flex flex-col justify-between gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-start">

                        <div class="flex items-center gap-3">

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-sm font-semibold text-amber-800">
                                {{ $initials ?: 'M' }}
                            </div>

                            <div class="min-w-0">

                                <h4 class="break-words text-sm font-semibold text-slate-700">
                                    {{ $studentName }}
                                </h4>

                                <p class="mt-1 text-xs text-slate-500">
                                    NIM: {{ $logbook->user?->nim ?? '-' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Tanggal Kegiatan:
                                    {{ $logbook->date?->format('d M Y') ?? '-' }}
                                </p>

                                @if ($logbook->submitted_at)
                                    <p class="mt-1 text-xs text-slate-400">
                                        Dikirim:
                                        {{ $logbook->submitted_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                        WIB
                                    </p>
                                @endif

                            </div>

                        </div>

                        <span class="inline-flex w-fit shrink-0 rounded-full border px-3 py-1.5 text-[11px] font-semibold {{ $statusClass }}">
                            @if ($status === 'Menunggu')
                                Menunggu Pemeriksaan
                            @elseif ($status === 'Disetujui')
                                Disetujui
                            @elseif ($status === 'Ditolak')
                                Perlu Revisi
                            @else
                                {{ $status }}
                            @endif
                        </span>

                    </div>

                    {{-- DETAIL KEGIATAN --}}
                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs font-semibold text-slate-500">
                            Topik Kegiatan
                        </p>

                        <h5 class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $logbook->topic ?: 'Kegiatan Magang' }}
                        </h5>

                        <p class="mt-4 text-xs font-semibold text-slate-500">
                            Deskripsi Pekerjaan Harian
                        </p>

                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-slate-600">{{ $logbook->description }}</p>

                    </div>

                    {{-- CATATAN REVISI --}}
                    @if ($status === 'Ditolak' && $logbook->mentor_feedback)

                        <div class="rounded-xl border border-red-100 bg-red-50 p-4">

                            <p class="text-xs font-semibold text-red-800">
                                Catatan Revisi Mentor
                            </p>

                            <p class="mt-2 whitespace-pre-line break-words text-xs leading-6 text-red-700">{{ $logbook->mentor_feedback }}</p>

                        </div>

                    @endif

                    {{-- INFORMASI STATUS --}}
                    @if ($status === 'Disetujui' && $logbook->reviewed_at)

                        <p class="text-xs text-emerald-700">
                            Logbook telah diperiksa pada
                            {{ $logbook->reviewed_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                            WIB.
                        </p>

                    @elseif ($status === 'Ditolak' && $logbook->reviewed_at)

                        <p class="text-xs text-slate-500">
                            Pemeriksaan terakhir:
                            {{ $logbook->reviewed_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                            WIB.
                        </p>

                    @endif

                    {{-- FORM AKSI MENTOR --}}
                    @if ($status === 'Menunggu')

                        <div class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-4 md:grid-cols-2">

                            {{-- FORM PERSETUJUAN --}}
                            <form
                                action="{{ route('mentor.logbook.approve', $logbook->id) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menyetujui logbook mahasiswa ini?')"
                                class="flex flex-col justify-end"
                            >
                                @csrf

                                <p class="mb-3 text-xs leading-5 text-slate-500">
                                    Pastikan isi laporan telah sesuai sebelum memberikan persetujuan.
                                </p>

                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                >
                                    Setujui Logbook
                                </button>
                            </form>

                            {{-- FORM PENOLAKAN --}}
                            <form
                                action="{{ route('mentor.logbook.reject', $logbook->id) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin meminta revisi logbook ini?')"
                                class="space-y-3"
                            >
                                @csrf

                                <div>
                                    <label
                                        for="mentor_feedback_{{ $logbook->id }}"
                                        class="mb-2 block text-xs font-semibold text-slate-600"
                                    >
                                        Catatan Revisi Mentor
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea
                                        id="mentor_feedback_{{ $logbook->id }}"
                                        name="mentor_feedback"
                                        rows="3"
                                        maxlength="2000"
                                        required
                                        placeholder="Jelaskan bagian yang perlu diperbaiki oleh mahasiswa..."
                                        class="w-full resize-y rounded-xl border border-gray-200 bg-white px-4 py-3 text-xs leading-5 text-slate-700 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-red-600 px-4 py-3 text-xs font-semibold text-white transition hover:bg-red-700"
                                >
                                    Tolak dan Minta Revisi
                                </button>

                            </form>

                        </div>

                    @endif

                </article>

            @empty

                {{-- TIDAK ADA LOGBOOK MASUK --}}
                <div class="rounded-2xl border border-gray-100 bg-white px-5 py-14 text-center shadow-sm">

                    <h3 class="text-sm font-semibold text-slate-700">
                        Belum Ada Logbook Masuk
                    </h3>

                    <p class="mx-auto mt-2 max-w-lg text-xs leading-6 text-slate-500">
                        Belum ada logbook yang dikirimkan oleh mahasiswa bimbingan Anda.
                        Logbook yang sudah dikirim akan muncul di halaman ini.
                    </p>

                </div>

            @endforelse

        </section>

    @endif

</div>
@endsection
