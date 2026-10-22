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
    $mentor = auth()->user();

    $mentorName = $mentor?->name ?? 'Mentor';
    $mentorPosition = $mentor?->position ?? 'Mentor / Pembimbing';

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
    <div class="flex min-w-0 flex-1 flex-col">

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
                        Kinerja Teknis, Sikap dan Etika, serta Kualitas Laporan Akhir.
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
                            Contoh rekap penilaian periode September 2026
                        </p>
                    </div>

                    <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-medium text-amber-700">
                        2 Data Contoh
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

                            {{-- DATA CONTOH MAHASISWA 1 --}}
                            <tr>
                                <td>
                                    <p class="student-name">
                                        Merry Silitonga
                                    </p>

                                    <p class="muted-text mt-1">
                                        Universitas Bina Sarana Informatika (UBSI)
                                    </p>
                                </td>

                                <td class="text-center">90</td>
                                <td class="text-center">95</td>
                                <td class="text-center">88</td>
                                <td class="text-center">92</td>

                                <td class="text-center">
                                    <span class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-800">
                                        91.8 (A)
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                                class="action-button bg-slate-800 text-white hover:bg-slate-700">
                                            Edit Nilai
                                        </button>

                                        <button type="button"
                                                class="action-button bg-red-50 text-red-600 hover:bg-red-100">
                                            PDF
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- DATA CONTOH MAHASISWA 2 --}}
                            <tr>
                                <td>
                                    <p class="student-name">
                                        Ahmad Fauzi
                                    </p>

                                    <p class="muted-text mt-1">
                                        Universitas Bina Sarana Informatika (UBSI)
                                    </p>
                                </td>

                                <td class="text-center">85</td>
                                <td class="text-center">80</td>
                                <td class="text-center">85</td>
                                <td class="text-center">88</td>

                                <td class="text-center">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700">
                                        84.2 (B)
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                                class="action-button bg-slate-800 text-white hover:bg-slate-700">
                                            Edit Nilai
                                        </button>

                                        <button type="button"
                                                class="action-button bg-red-50 text-red-600 hover:bg-red-100">
                                            PDF
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

                <div class="border-t border-gray-100 px-5 py-4">
                    <p class="muted-text">
                        Data pada tabel ini masih berupa contoh tampilan dan belum menjadi sumber nilai resmi dari database.
                    </p>
                </div>

            </section>

        </main>

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