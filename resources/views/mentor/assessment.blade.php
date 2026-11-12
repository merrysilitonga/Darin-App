<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Penilaian Magang - DARIN Mentor</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- Tambahan Script Alpine.js untuk mengatur Modal (Pop-up) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: #53647a;
            background-color: #f7f8fa;
        }

        .mentor-sidebar {
            background-color: #191712;
        }

        .mentor-nav {
            font-size: 12px;
            font-weight: 500;
            color: #c5cbd4;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .mentor-nav:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.06);
        }

        .mentor-nav.active {
            color: #191712;
            background-color: #ffc107;
            font-weight: 600;
        }

        .page-title {
            color: #263b59;
            font-size: 19px;
            font-weight: 600;
            line-height: 1.5;
        }

        .section-title {
            color: #2e4563;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.6;
        }

        .muted-text {
            color: #78889e;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.7;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            padding: 13px 12px;
            border-bottom: 1px solid #edf0f4;
            color: #8996a8;
            font-size: 10px;
            font-weight: 500;
            line-height: 1.6;
            letter-spacing: 0.025em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .data-table tbody td {
            padding: 13px 12px;
            border-bottom: 1px solid #f0f2f5;
            color: #586a80;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.7;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: #fafbfd;
        }

        .student-name {
            color: #304764;
            font-size: 12px;
            font-weight: 600;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.4;
            transition: background-color 0.2s ease;
            white-space: nowrap;
        }

        .card {
            border: 1px solid #e8edf2;
            border-radius: 18px;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(35, 55, 80, 0.025);
        }

        @media (max-width: 767px) {
            .mentor-sidebar {
                display: none;
            }

            .page-content {
                padding: 16px !important;
            }

            .page-header {
                padding: 16px !important;
            }
        }
    </style>
</head>

<body class="min-h-screen antialiased">

@php
    $mentorUser = auth()->user();

    $mentorName = $mentorUser?->name ?? 'Mentor';
    $mentorPosition = $mentorUser?->position ?? 'Mentor / Pembimbing';

    $mentorInitials = collect(explode(' ', trim($mentorName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<div class="flex min-h-screen">

    {{-- SIDEBAR MENTOR --}}
    <aside class="mentor-sidebar hidden w-64 shrink-0 flex-col justify-between text-white md:flex">

        <div>
            {{-- BRAND DARIN --}}
            <a href="{{ url('/mentor/dashboard') }}"
               class="flex items-center gap-3 border-b border-white/10 px-5 py-5">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#ffc107] text-lg font-bold text-black">
                    ⚡
                </div>

                <div>
                    <h1 class="text-base font-bold tracking-wide text-white">
                        DARIN
                    </h1>

                    <p class="mt-0.5 text-[10px] font-medium text-amber-400">
                        Portal Mentor
                    </p>
                </div>
            </a>

            {{-- PROFIL MENTOR --}}
            <div class="border-b border-white/10 px-4 py-5">

                <div class="mb-3 text-[10px] font-medium uppercase tracking-wider text-gray-500">
                    Login sebagai
                </div>

                <p class="text-xs font-semibold text-white">
                    {{ $mentorName }}
                </p>

                <p class="mt-1 text-[11px] font-normal text-gray-400">
                    {{ $mentorPosition }}
                </p>
            </div>

            {{-- NAVIGASI --}}
            <nav class="space-y-1 px-3 py-5">

                <p class="mb-3 px-3 text-[10px] font-medium uppercase tracking-wider text-gray-500">
                    Menu Utama
                </p>

                <a href="{{ url('/mentor/dashboard') }}"
                   class="mentor-nav {{ request()->is('mentor/dashboard') ? 'active' : '' }} block rounded-xl px-3 py-3">
                    Dashboard
                </a>

                <a href="{{ url('/mentor/interns') }}"
                   class="mentor-nav {{ request()->is('mentor/interns') ? 'active' : '' }} block rounded-xl px-3 py-3">
                    Daftar Mahasiswa Bimbingan
                </a>

                <a href="{{ url('/mentor/logbook') }}"
                   class="mentor-nav {{ request()->is('mentor/logbook') ? 'active' : '' }} block rounded-xl px-3 py-3">
                    Validasi Logbook
                </a>

                <a href="{{ url('/mentor/attendance') }}"
                   class="mentor-nav {{ request()->is('mentor/attendance') ? 'active' : '' }} block rounded-xl px-3 py-3">
                    Mentoring &amp; Kehadiran
                </a>

                <a href="{{ url('/mentor/assessment') }}"
                   class="mentor-nav {{ request()->is('mentor/assessment') ? 'active' : '' }} block rounded-xl px-3 py-3">
                    Penilaian Magang
                </a>

            </nav>
        </div>

        {{-- LOGOUT --}}
        <div class="border-t border-white/10 p-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit"
                        class="w-full rounded-lg bg-red-500/10 px-4 py-3 text-xs font-medium text-red-300 transition hover:bg-red-500 hover:text-white">
                    Logout
                </button>
            </form>
        </div>

    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="flex min-w-0 flex-1 flex-col" x-data="{ showModal: false, activeStudentId: null, activeStudentName: '', formKedisiplinan: 0, formKinerja: 0, formSikap: 0, formLaporan: 0 }">

        {{-- HEADER --}}
        <header class="page-header flex items-center justify-between gap-4 border-b border-gray-200 bg-white px-7 py-4">

            <div>
                <h2 class="page-title">
                    Penilaian Akhir Magang
                </h2>

                <p class="muted-text mt-1">
                    Kelola dan tinjau hasil penilaian mahasiswa magang.
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-3">

                <div class="hidden text-right sm:block">
                    <p class="text-xs font-medium text-[#304764]">
                        {{ $mentorName }}
                    </p>

                    <p class="mt-1 text-[11px] font-normal text-gray-500">
                        {{ $mentorPosition }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#ffc107] text-xs font-semibold text-gray-900">
                    {{ $mentorInitials ?: 'M' }}
                </div>

            </div>
        </header>

        {{-- ISI HALAMAN --}}
        <main class="page-content flex-1 space-y-5 p-7">
            
            {{-- ALERT SUCCESS --}}
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                    ✅ {{ session('success') }}
                </div>
            @endif

            {{-- ALERT ERROR --}}
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-700">
                    ❌ Terdapat kesalahan pengisian nilai. Pastikan angka antara 0-100.
                </div>
            @endif

            {{-- INFORMASI PENILAIAN --}}
            <section class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">

                <div class="mt-0.5 text-base text-amber-700">
                    💡
                </div>

                <div>
                    <h3 class="text-sm font-medium text-[#725b24]">
                        Informasi Penilaian
                    </h3>

                    <p class="mt-1 text-xs font-normal leading-6 text-[#8b7440]">
                        Nilai akhir magang dihitung berdasarkan aspek Kedisiplinan,
                        Kinerja Teknis, Sikap dan Etika, serta Kualitas Laporan Akhir. Skala 0-100.
                    </p>
                </div>

            </section>

            {{-- REKAP NILAI --}}
            <section class="card overflow-hidden">

                <div class="flex flex-col justify-between gap-2 border-b border-gray-100 px-5 py-5 sm:flex-row sm:items-center">

                    <div>
                        <h3 class="section-title">
                            Rekapitulasi Nilai Mahasiswa Magang
                        </h3>

                        <p class="muted-text mt-1">
                            Daftar rekap penilaian periode {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                        </p>
                    </div>

                    <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-medium text-amber-700">
                        {{ isset($students) ? $students->count() : 0 }} Mahasiswa Bimbingan
                    </span>

                </div>

                <div class="overflow-x-auto">

                    <table class="data-table text-left">

                        <thead>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <th class="text-center">Kedisiplinan (25%)</th>
                                <th class="text-center">Kinerja Teknis (35%)</th>
                                <th class="text-center">Sikap &amp; Etika (20%)</th>
                                <th class="text-center">Laporan (20%)</th>
                                <th class="text-center">Nilai Akhir</th>
                                <th class="text-center">Aksi / Unduh</th>
                            </tr>
                        </thead>

                        <tbody>
                            @if(isset($students) && $students->count() > 0)
                                @foreach($students as $student)
                                    @php
                                        $nilai = isset($assessments) ? $assessments->get($student->id) : null;
                                        
                                        $kedisiplinan = $nilai->kedisiplinan ?? 0;
                                        $kinerja = $nilai->kinerja_teknis ?? 0;
                                        $sikap = $nilai->sikap_etika ?? 0;
                                        $laporan = $nilai->laporan ?? 0;

                                        $nilaiAkhir = ($kedisiplinan * 0.25) + ($kinerja * 0.35) + ($sikap * 0.20) + ($laporan * 0.20);

                                        if ($nilaiAkhir >= 85) { 
                                            $grade = 'A'; $badgeColor = 'bg-amber-50 text-amber-800'; 
                                        } elseif ($nilaiAkhir >= 75) { 
                                            $grade = 'B'; $badgeColor = 'bg-slate-100 text-slate-700'; 
                                        } elseif ($nilaiAkhir >= 60) { 
                                            $grade = 'C'; $badgeColor = 'bg-emerald-50 text-emerald-700'; 
                                        } elseif ($nilaiAkhir > 0) { 
                                            $grade = 'D'; $badgeColor = 'bg-red-50 text-red-700'; 
                                        } else {
                                            $grade = '-'; $badgeColor = 'bg-gray-100 text-gray-500';
                                        }
                                    @endphp

                                    <tr class="transition hover:bg-slate-50/70">
                                        <td>
                                            <p class="student-name">
                                                {{ $student->name }}
                                            </p>

                                            <p class="muted-text mt-1">
                                                {{ $student->universitas ?? 'Universitas Asal Belum Diisi' }}
                                            </p>
                                        </td>

                                        <td class="text-center">{{ $nilai ? $kedisiplinan : '-' }}</td>
                                        <td class="text-center">{{ $nilai ? $kinerja : '-' }}</td>
                                        <td class="text-center">{{ $nilai ? $sikap : '-' }}</td>
                                        <td class="text-center">{{ $nilai ? $laporan : '-' }}</td>

                                        <td class="text-center">
                                            @if($nilai)
                                                <span class="inline-flex rounded-lg px-2.5 py-1.5 text-xs font-semibold {{ $badgeColor }}">
                                                    {{ number_format($nilaiAkhir, 1) }} ({{ $grade }})
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-lg bg-gray-50 px-2.5 py-1.5 text-xs font-semibold text-gray-500">
                                                    Belum Dinilai
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                {{-- Tombol Pemicu Modal (Alpine.js) --}}
                                                <button type="button"
                                                        @click="
                                                            showModal = true; 
                                                            activeStudentId = {{ $student->id }};
                                                            activeStudentName = '{{ $student->name }}';
                                                            formKedisiplinan = {{ $kedisiplinan }};
                                                            formKinerja = {{ $kinerja }};
                                                            formSikap = {{ $sikap }};
                                                            formLaporan = {{ $laporan }};
                                                            // Mengatur action form dinamis berdasarkan ID
                                                            $refs.assessmentForm.action = '{{ url('mentor/assessment') }}/' + activeStudentId;
                                                        "
                                                        class="action-button bg-slate-800 text-white hover:bg-slate-700">
                                                    {{ $nilai ? 'Edit Nilai' : 'Beri Nilai' }}
                                                </button>

                                                @if($nilai)
                                                    <a href="#" class="action-button bg-red-50 text-red-600 hover:bg-red-100">
                                                        PDF
                                                    </a>
                                                @else
                                                    <button type="button" disabled class="action-button bg-gray-50 text-gray-300 cursor-not-allowed">
                                                        PDF
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-gray-400">
                                        Saat ini tidak ada data mahasiswa bimbingan yang ditugaskan kepada Anda.
                                    </td>
                                </tr>
                            @endif
                        </tbody>

                    </table>

                </div>

                <div class="border-t border-gray-100 px-5 py-4">
                    <p class="muted-text">
                        Data pada tabel ini sudah terhubung dan membaca data nilai resmi dari database DARIN.
                    </p>
                </div>

            </section>
        </main>

        {{-- MODAL PENGISIAN NILAI (Alpine.js) --}}
        <div x-show="showModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Background Overlay -->
            <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Modal Panel -->
                    <div x-show="showModal" 
                         @click.away="showModal = false"
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        
                        <form x-ref="assessmentForm" method="POST" action="">
                            @csrf
                            <div class="bg-white px-6 pb-6 pt-7 sm:p-8 sm:pb-6">
                                <div class="mb-5">
                                    <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                        Input Nilai Magang
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Isi form penilaian (skala 0-100) untuk <span class="font-bold text-gray-700" x-text="activeStudentName"></span>.
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Kedisiplinan (25%)</label>
                                        <input type="number" name="kedisiplinan" x-model="formKedisiplinan" min="0" max="100" required
                                            class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Kinerja Teknis (35%)</label>
                                        <input type="number" name="kinerja_teknis" x-model="formKinerja" min="0" max="100" required
                                            class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Sikap & Etika (20%)</label>
                                        <input type="number" name="sikap_etika" x-model="formSikap" min="0" max="100" required
                                            class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Kualitas Laporan Akhir (20%)</label>
                                        <input type="number" name="laporan" x-model="formLaporan" min="0" max="100" required
                                            class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8">
                                <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto transition">
                                    Simpan Nilai
                                </button>
                                <button type="button" @click="showModal = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <footer class="px-6 pb-5 text-center">
            <p class="text-[11px] font-normal text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </p>
        </footer>

    </div>

</div>

</body>
</html>