<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi & Logbook Global - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8f9fa;
        }

        .card {
            border: 1px solid #eceef2;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
        }

        @media print {
            aside,
            .no-print,
            header .admin-identitas {
                display: none !important;
            }

            main {
                padding: 0 !important;
            }

            body {
                background: #fff !important;
            }

            .card {
                box-shadow: none !important;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-[#f8f9fa] text-gray-800 antialiased">

@php
    $adminName = auth()->user()->name ?? 'Administrator';

    $statusClass = static function ($status) {
        return match ($status) {
            'Hadir', 'Disetujui' => 'bg-emerald-100 text-emerald-700',
            'Terlambat', 'Menunggu' => 'bg-amber-100 text-amber-700',
            'Ditolak', 'Alpa' => 'bg-red-100 text-red-700',
            'Sakit' => 'bg-blue-100 text-blue-700',
            'Izin' => 'bg-violet-100 text-violet-700',
            default => 'bg-gray-100 text-gray-700',
        };
    };
@endphp

<div class="flex min-h-screen">

    <!-- SIDEBAR ADMIN / HR -->
    <aside class="no-print flex w-64 shrink-0 flex-col justify-between bg-[#191712] text-white">
        <div>
            <div class="flex items-center gap-3 border-b border-white/10 p-6">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#ffc107] text-2xl font-extrabold text-black shadow-md">
                    ⚡
                </div>

                <div>
                    <h1 class="text-xl font-extrabold tracking-wider">DARIN</h1>
                    <p class="mt-1 text-xs font-bold uppercase tracking-wider text-amber-400">
                        Portal HR / Admin
                    </p>
                </div>
            </div>

            <div class="border-b border-white/10 px-6 py-4">
                <p class="text-[10px] uppercase tracking-widest text-gray-500">
                    Login sebagai
                </p>

                <p class="mt-2 text-sm font-semibold">{{ $adminName }}</p>

                <p class="mt-1 text-xs text-gray-400">
                    Administrator / HR
                </p>
            </div>

            <nav class="space-y-2 p-4 text-sm font-semibold">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 hover:bg-white/10">
                    📊 Dashboard Utama
                </a>

                <a href="{{ route('admin.users') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 hover:bg-white/10">
                    👥 Manajemen Pengguna
                </a>

                <a href="{{ route('admin.divisions') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 hover:bg-white/10">
                    🏢 Penempatan & Divisi
                </a>

                <a href="{{ route('admin.attendance') }}"
                   class="flex items-center gap-3 rounded-xl bg-[#ffc107] px-4 py-3 text-black shadow-md">
                    📈 Rekap Absensi & Logbook
                </a>

                <a href="{{ route('admin.settings') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 hover:bg-white/10">
                    ⚙️ Pengaturan Sistem
                </a>
            </nav>
        </div>

        <div class="border-t border-white/10 p-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit"
                        class="w-full rounded-xl bg-red-600/20 px-4 py-3 text-sm font-bold text-red-300 transition hover:bg-red-600 hover:text-white">
                    🚪 Keluar / Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex min-w-0 flex-1 flex-col">

        <!-- HEADER -->
        <header class="flex flex-col justify-between gap-4 border-b border-gray-200 bg-white px-5 py-5 shadow-sm sm:flex-row sm:items-center md:px-8">
            <div>
                <p class="text-xs text-gray-500">
                    Administrator / Laporan
                </p>

                <h2 class="mt-1 text-xl font-extrabold text-gray-900 md:text-2xl">
                    Rekapitulasi Absensi & Logbook
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Data dari database DARIN sesuai filter yang dipilih.
                </p>
            </div>

            <div class="admin-identitas flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">
                        {{ $adminName }}
                    </p>

                    <p class="text-xs text-gray-500">
                        Human Resources / Administrator
                    </p>
                </div>

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#ffc107] font-extrabold text-black">
                    {{ collect(explode(' ', trim($adminName)))->take(2)->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('') ?: 'A' }}
                </div>
            </div>
        </header>

        <main class="flex-1 space-y-6 p-5 md:p-8">

            <!-- NOTIFIKASI -->
            @if(session('success'))
                <div role="status"
                     class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div role="alert"
                     class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert"
                     class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    <p class="font-bold">Terjadi kesalahan.</p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- FILTER LAPORAN -->
            <section class="card space-y-5 rounded-3xl bg-white p-5 md:p-7">
                <div class="flex flex-col justify-between gap-2 border-b border-gray-100 pb-4 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">
                            🔍 Filter Laporan
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Filter berlaku untuk data absensi dan logbook.
                        </p>
                    </div>

                    <span class="text-xs text-gray-500">
                        {{ $attendances->count() }} catatan absensi ditemukan
                    </span>
                </div>

                <form method="GET"
                      action="{{ route('admin.attendance') }}"
                      class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

                    <div>
                        <label for="search"
                               class="mb-2 block text-xs font-bold text-gray-700">
                            Nama / Email Mahasiswa
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $search }}"
                            placeholder="Cari mahasiswa..."
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    </div>

                    <div>
                        <label for="division_id"
                               class="mb-2 block text-xs font-bold text-gray-700">
                            Divisi
                        </label>

                        <select id="division_id"
                                name="division_id"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm focus:border-amber-400 focus:outline-none">
                            <option value="">Semua Divisi</option>

                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ (string) $divisionId === (string) $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mentor_id"
                               class="mb-2 block text-xs font-bold text-gray-700">
                            Mentor Pembimbing
                        </label>

                        <select id="mentor_id"
                                name="mentor_id"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm focus:border-amber-400 focus:outline-none">
                            <option value="">Semua Mentor</option>

                            @foreach($mentors as $mentor)
                                <option value="{{ $mentor->id }}"
                                    {{ (string) $mentorId === (string) $mentor->id ? 'selected' : '' }}>
                                    {{ $mentor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="month"
                               class="mb-2 block text-xs font-bold text-gray-700">
                            Periode / Bulan
                        </label>

                        <input
                            id="month"
                            name="month"
                            type="month"
                            value="{{ preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) ? $month : '' }}"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm focus:border-amber-400 focus:outline-none">
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="flex-1 rounded-xl bg-[#ffc107] px-4 py-3 text-sm font-extrabold text-black transition hover:bg-amber-400">
                            Terapkan
                        </button>

                        <a href="{{ route('admin.attendance') }}"
                           class="rounded-xl border border-gray-200 px-4 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>

                <p class="text-xs leading-5 text-gray-400">
                    Filter mentor berdasarkan penugasan langsung pada akun mahasiswa.
                    Filter divisi tetap menggunakan divisi mahasiswa.
                </p>
            </section>

            <!-- KARTU RINGKASAN -->
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm text-gray-500">Catatan Absensi</p>
                    <p class="mt-3 text-3xl font-extrabold text-gray-900">
                        {{ $stats['total'] }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">Sesuai filter aktif</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm text-gray-500">Mahasiswa Tercatat</p>
                    <p class="mt-3 text-3xl font-extrabold text-blue-600">
                        {{ $stats['mahasiswa'] }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">Mahasiswa unik pada hasil</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm text-gray-500">Hadir</p>
                    <p class="mt-3 text-3xl font-extrabold text-emerald-600">
                        {{ $stats['hadir'] }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">Status Hadir</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm text-gray-500">Terlambat</p>
                    <p class="mt-3 text-3xl font-extrabold text-amber-600">
                        {{ $stats['terlambat'] }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">Status Terlambat</p>
                </div>
            </section>

            <!-- TABEL ABSENSI -->
            <section class="card overflow-hidden rounded-3xl bg-white">
                <div class="flex flex-col justify-between gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center md:p-7">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">
                            📅 Data Kehadiran Mahasiswa
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Jam masuk, jam pulang, status, selfie, dan koordinat GPS.
                        </p>
                    </div>

                    <button type="button"
                            onclick="window.print()"
                            class="no-print rounded-xl bg-[#191712] px-5 py-3 text-sm font-bold text-white hover:bg-black">
                        🖨️ Cetak / Simpan PDF
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1150px] text-left text-sm">
                        <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-4">No.</th>
                                <th class="px-4 py-4">Tanggal</th>
                                <th class="px-4 py-4">Mahasiswa</th>
                                <th class="px-4 py-4">Divisi / Mentor</th>
                                <th class="px-4 py-4">Jam Masuk</th>
                                <th class="px-4 py-4">Jam Pulang</th>
                                <th class="px-4 py-4">Metode</th>
                                <th class="px-4 py-4">Status</th>
                                <th class="px-4 py-4">Bukti / Lokasi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($attendances as $attendance)
                                @php
                                    $student = $attendance->user;
                                    $division = $student?->division;

                                    // Nama mentor sesuai penugasan langsung.
                                    $assignedMentor = $student?->mentor_id
                                        ? $mentors->firstWhere('id', $student->mentor_id)
                                        : null;

                                    $hasCoordinates =
                                        $attendance->latitude !== null
                                        && $attendance->longitude !== null;
                                @endphp

                                <tr class="align-top hover:bg-gray-50/70">
                                    <td class="px-4 py-4 text-gray-400">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 font-semibold text-gray-700">
                                        {{ $attendance->date
                                            ? \Illuminate\Support\Carbon::parse($attendance->date)->translatedFormat('d M Y')
                                            : '-' }}
                                    </td>

                                    <td class="min-w-48 px-4 py-4">
                                        <p class="font-bold text-gray-900">
                                            {{ $student?->name ?? 'Pengguna tidak ditemukan' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $student?->email ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="min-w-44 px-4 py-4">
                                        <p class="font-semibold text-gray-700">
                                            {{ $division?->name ?? 'Belum ditetapkan' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Mentor: {{ $assignedMentor?->name ?? 'Belum ditugaskan' }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 font-semibold">
                                        {{ $attendance->clock_in
                                            ? \Illuminate\Support\Carbon::parse($attendance->clock_in)->format('H:i') . ' WIB'
                                            : '-' }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        {{ $attendance->clock_out
                                            ? \Illuminate\Support\Carbon::parse($attendance->clock_out)->format('H:i') . ' WIB'
                                            : '-' }}
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $attendance->work_location ?? '-' }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusClass($attendance->status ?? '') }}">
                                            {{ $attendance->status ?? 'Belum ada status' }}
                                        </span>
                                    </td>

                                    <td class="min-w-40 px-4 py-4">
                                        <div class="flex flex-col items-start gap-2">
                                            @if($attendance->photo_path)
                                                <a href="{{ route('admin.attendance.photo', $attendance->id) }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 hover:bg-amber-100">
                                                    📷 Lihat Selfie
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400">
                                                    Foto tidak tersedia
                                                </span>
                                            @endif

                                            @if($hasCoordinates)
                                                <a href="https://www.google.com/maps?q={{ $attendance->latitude }},{{ $attendance->longitude }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="text-xs font-semibold text-blue-600 hover:underline">
                                                    📍 Lihat GPS
                                                </a>

                                                <span class="max-w-48 break-all text-[10px] text-gray-400">
                                                    {{ $attendance->latitude }}, {{ $attendance->longitude }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">
                                                    GPS tidak tersedia
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-14 text-center">
                                        <p class="font-bold text-gray-700">
                                            Belum ada data absensi
                                        </p>
                                        <p class="mt-2 text-sm text-gray-400">
                                            Coba ubah filter atau pastikan mahasiswa sudah mengirim absensi.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 bg-gray-50 px-5 py-4 text-xs text-gray-500">
                    Menampilkan {{ $attendances->count() }} catatan absensi dari database.
                </div>
            </section>

            <!-- TABEL LOGBOOK -->
            <section class="card overflow-hidden rounded-3xl bg-white">
                <div class="flex flex-col justify-between gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center md:p-7">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">
                            📝 Rekapitulasi Logbook Kolektif
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Aktivitas yang tercatat pada database DARIN.
                        </p>
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ $stats['logbook'] }} catatan ·
                        {{ $stats['logbook_menunggu'] }} menunggu
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-4">Tanggal</th>
                                <th class="px-4 py-4">Mahasiswa</th>
                                <th class="px-4 py-4">Divisi</th>
                                <th class="px-4 py-4">Topik / Pekerjaan</th>
                                <th class="px-4 py-4">Deskripsi</th>
                                <th class="px-4 py-4">Status</th>
                                <th class="px-4 py-4">Catatan Mentor</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($logbooks as $item)
                                <tr class="align-top hover:bg-gray-50/70">
                                    <td class="whitespace-nowrap px-4 py-4 font-semibold text-gray-600">
                                        {{ $item->date
                                            ? \Illuminate\Support\Carbon::parse($item->date)->translatedFormat('d M Y')
                                            : '-' }}
                                    </td>

                                    <td class="min-w-40 px-4 py-4">
                                        <p class="font-bold text-gray-900">
                                            {{ $item->user?->name ?? 'Pengguna tidak ditemukan' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $item->user?->email ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item->user?->division?->name ?? 'Belum ditetapkan' }}
                                    </td>

                                    <td class="min-w-44 px-4 py-4 font-semibold text-gray-800">
                                        {{ $item->topic ?? '-' }}
                                    </td>

                                    <td class="min-w-64 max-w-md px-4 py-4">
                                        <p class="whitespace-pre-line break-words text-gray-600">
                                            {{ $item->description ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusClass($item->status ?? '') }}">
                                            {{ $item->status ?? 'Belum ditinjau' }}
                                        </span>
                                    </td>

                                    <td class="min-w-44 px-4 py-4 text-gray-600">
                                        {{ $item->mentor_feedback ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-14 text-center">
                                        <p class="font-bold text-gray-700">
                                            Belum ada data logbook
                                        </p>
                                        <p class="mt-2 text-sm text-gray-400">
                                            Catatan mahasiswa akan muncul di sini setelah tersimpan.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 bg-gray-50 px-5 py-4 text-xs text-gray-500">
                    Menampilkan {{ $logbooks->count() }} catatan logbook sesuai filter aktif.
                </div>
            </section>

            <footer class="pb-4 text-center text-xs text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </footer>

        </main>
    </div>
</div>

</body>
</html>
