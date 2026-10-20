<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Mentor - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-[#f8f9fa] text-gray-800 antialiased">

<div class="flex min-h-screen">

    {{-- SIDEBAR DINAMIS --}}
    @include('layouts.sidebar')

    {{-- KONTEN UTAMA --}}
    <main class="min-w-0 flex-1 p-5 md:p-8">

        {{-- HEADER --}}
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-extrabold">
                    Dashboard Mentor
                </h1>

                <p class="mt-2 text-sm text-gray-500">
                    {{ now('Asia/Jakarta')->format('d/m/Y') }}
                    · {{ $divisionName }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white px-5 py-3 shadow-sm">
                <p class="text-xs text-gray-500">
                    Mentor / Pembimbing
                </p>

                <p class="mt-1 font-bold">
                    {{ $mentor->name }}
                </p>
            </div>

        </header>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- DIVISI BELUM DIATUR --}}
        @if ($assignmentMissing)

            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <h2 class="font-bold text-amber-800">
                    Divisi Mentor belum ditetapkan
                </h2>

                <p class="mt-2 text-sm text-amber-700">
                    Hubungi HR/Admin untuk menetapkan divisi pada akun ini.
                    Data mahasiswa dan notifikasi akan tampil setelah
                    penugasan tersedia.
                </p>
            </div>

        @endif

        {{-- SAMBUTAN --}}
        <section class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-r from-[#191712] to-[#353025] p-6 text-white shadow-sm md:p-8">

            <div class="pointer-events-none absolute -right-4 -top-8 select-none text-9xl font-extrabold text-white/5">
                ⚡
            </div>

            <div class="relative">
                <p class="mb-2 text-sm font-semibold text-amber-400">
                    PORTAL MENTOR DARIN
                </p>

                <h2 class="text-xl font-extrabold md:text-2xl">
                    Selamat datang, {{ $mentor->name }}!
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-300">
                    Pantau kehadiran mahasiswa, periksa logbook harian,
                    tinjau izin dan sakit, serta pantau aktivitas mahasiswa
                    yang berada dalam divisi bimbingan kamu.
                </p>
            </div>

        </section>

        {{-- STATISTIK --}}
        <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

            {{-- TOTAL MAHASISWA --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">

                <div class="flex items-center justify-between">
                    <span class="text-2xl">👨‍🎓</span>

                    <span class="rounded-lg bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                        Bimbingan
                    </span>
                </div>

                <p class="mt-5 text-sm text-gray-500">
                    Total Mahasiswa
                </p>

                <p class="mt-1 text-3xl font-extrabold">
                    {{ $totalInterns }}
                </p>

                <p class="mt-1 text-xs text-gray-400">
                    Mahasiswa di divisi kamu
                </p>

            </div>

            {{-- HADIR HARI INI --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">

                <div class="flex items-center justify-between">
                    <span class="text-2xl">📅</span>

                    <span class="rounded-lg bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                        Hari ini
                    </span>
                </div>

                <p class="mt-5 text-sm text-gray-500">
                    Kehadiran Tercatat
                </p>

                <p class="mt-1 text-3xl font-extrabold text-green-600">
                    {{ $attendancePercent }}%
                </p>

                <p class="mt-1 text-xs text-gray-400">
                    {{ $presentToday }} dari {{ $totalInterns }} mahasiswa
                    tercatat hadir
                </p>

            </div>

            {{-- PENDING LOGBOOK --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">

                <div class="flex items-center justify-between">
                    <span class="text-2xl">📝</span>

                    <span class="rounded-lg bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                        Logbook
                    </span>
                </div>

                <p class="mt-5 text-sm text-gray-500">
                    Menunggu Validasi
                </p>

                <p class="mt-1 text-3xl font-extrabold text-blue-600">
                    {{ $pendingLogbooksCount }}
                </p>

                <a
                    href="{{ route('mentor.logbook') }}"
                    class="mt-2 inline-block text-xs font-bold text-blue-600 hover:underline"
                >
                    Periksa logbook →
                </a>

            </div>

            {{-- PENDING IZIN --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">

                <div class="flex items-center justify-between">
                    <span class="text-2xl">📋</span>

                    <span class="rounded-lg bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                        Izin / Sakit
                    </span>
                </div>

                <p class="mt-5 text-sm text-gray-500">
                    Menunggu Persetujuan
                </p>

                <p class="mt-1 text-3xl font-extrabold text-red-600">
                    {{ $pendingLeaveRequestsCount }}
                </p>

                <a
                    href="{{ route('mentor.leave-requests') }}"
                    class="mt-2 inline-block text-xs font-bold text-red-600 hover:underline"
                >
                    Periksa pengajuan →
                </a>

            </div>

        </section>

        {{-- RINGKASAN IZIN --}}
        <section class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="font-extrabold">
                        Ringkasan Pengajuan Izin
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Mahasiswa dengan izin disetujui yang berlaku hari ini.
                    </p>
                </div>

                <p class="text-2xl font-extrabold text-green-600">
                    {{ $approvedLeaveToday }}
                    <span class="text-sm font-medium text-gray-500">
                        mahasiswa
                    </span>
                </p>

            </div>

        </section>

        {{-- NOTIFIKASI MENUNGGU --}}
        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between md:p-6">

                <div>
                    <h2 class="text-lg font-extrabold">
                        🔔 Pemberitahuan dan Tindakan
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Logbook dan izin yang memerlukan pemeriksaan kamu.
                    </p>
                </div>

                <span class="w-fit rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800">
                    {{ $pendingActionCount }} menunggu
                </span>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[700px] text-left">

                    <thead class="bg-gray-50 text-xs font-bold uppercase tracking-wide text-gray-500">

                        <tr>
                            <th class="px-5 py-4">Mahasiswa</th>
                            <th class="px-5 py-4">Jenis</th>
                            <th class="px-5 py-4">Keterangan</th>
                            <th class="px-5 py-4">Tanggal</th>
                            <th class="px-5 py-4 text-center">Aksi</th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse ($notifications as $item)

                            <tr class="transition hover:bg-gray-50">

                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800">
                                        {{ $item['student_name'] }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-bold {{ $item['badge_class'] }}">
                                        {{ $item['icon'] }}
                                        {{ $item['type'] }}
                                    </span>
                                </td>

                                <td class="max-w-xs px-5 py-4 text-sm leading-relaxed text-gray-600">
                                    {{ $item['description'] }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-xs text-gray-500">
                                    {{ $item['created_at']
                                        ? $item['created_at']->timezone('Asia/Jakarta')->format('d/m/Y H:i')
                                        : '-' }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <a
                                        href="{{ $item['url'] }}"
                                        class="inline-flex whitespace-nowrap rounded-xl bg-[#ffc107] px-4 py-2 text-xs font-bold text-black transition hover:bg-amber-400"
                                    >
                                        Tinjau →
                                    </a>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">

                                    <div class="text-4xl">✅</div>

                                    <h3 class="mt-4 font-bold">
                                        Tidak Ada Tindakan Tertunda
                                    </h3>

                                    <p class="mt-2 text-sm text-gray-500">
                                        @if ($assignmentMissing)
                                            Data akan tersedia setelah HR/Admin menetapkan divisi.
                                        @else
                                            Semua logbook dan pengajuan izin mahasiswa di divisi kamu sudah diproses.
                                        @endif
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="flex flex-wrap gap-3 border-t border-gray-100 p-5">

                <a
                    href="{{ route('mentor.logbook') }}"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50"
                >
                    Lihat Semua Logbook
                </a>

                <a
                    href="{{ route('mentor.leave-requests') }}"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50"
                >
                    Lihat Semua Pengajuan Izin
                </a>

            </div>

        </section>

        <footer class="py-6 text-center text-xs text-gray-400">
            DARIN · Sistem Presensi Geospasial Terintegrasi ESDM
        </footer>

    </main>

</div>

</body>
</html>