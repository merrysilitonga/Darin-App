
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rekap Absensi & Logbook Global - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body class="bg-[#f8f9fa] text-gray-800 antialiased">

    @php
        $adminName = auth()->user()->name ?? 'Administrator';
    @endphp

    <div class="flex min-h-screen">

        {{-- SIDEBAR ADMIN / HR --}}
        <aside class="w-64 shrink-0 bg-[#191712] text-white flex flex-col justify-between">

            <div>

                {{-- LOGO DAN BRAND --}}
                <div class="p-6 flex items-center gap-3 border-b border-white/10">

                    <div class="bg-[#ffc107] text-black font-extrabold
                                w-12 h-12 shrink-0 rounded-xl
                                flex items-center justify-center
                                text-2xl shadow-md">
                        ⚡
                    </div>

                    <div>
                        <h1 class="font-extrabold tracking-wider text-xl">
                            DARIN
                        </h1>

                        <p class="text-xs uppercase tracking-wider
                                  text-amber-400 font-bold mt-1">
                            Portal HR / Admin
                        </p>
                    </div>

                </div>


                {{-- NAVIGASI --}}
                <nav class="p-4 space-y-2 text-sm font-semibold">

                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               text-gray-300 hover:bg-white/10
                               hover:text-white rounded-xl transition"
                    >
                        📊 Dashboard Utama
                    </a>

                    <a
                        href="{{ route('admin.users') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               text-gray-300 hover:bg-white/10
                               hover:text-white rounded-xl transition"
                    >
                        👥 Manajemen Pengguna
                    </a>

                    <a
                        href="{{ route('admin.divisions') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               text-gray-300 hover:bg-white/10
                               hover:text-white rounded-xl transition"
                    >
                        🏢 Penempatan & Divisi
                    </a>

                    <a
                        href="{{ route('admin.attendance') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               bg-[#ffc107] text-black
                               rounded-xl shadow-md"
                    >
                        📈 Rekap Absensi & Logbook
                    </a>

                    <a
                        href="{{ route('admin.settings') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               text-gray-300 hover:bg-white/10
                               hover:text-white rounded-xl transition"
                    >
                        ⚙️ Pengaturan Sistem
                    </a>

                </nav>

            </div>


            {{-- LOGOUT --}}
            <div class="p-4 border-t border-white/10">

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2
                               px-4 py-3 bg-red-600/20
                               hover:bg-red-600 text-red-300
                               hover:text-white rounded-xl
                               text-sm font-bold transition"
                    >
                        🚪 Keluar / Logout
                    </button>
                </form>

            </div>

        </aside>


        {{-- KONTEN UTAMA --}}
        <div class="flex-1 min-w-0 flex flex-col">

            {{-- TOPBAR --}}
            <header class="bg-white border-b border-gray-200
                           px-6 md:px-8 py-5 shadow-sm
                           flex flex-col sm:flex-row
                           sm:items-center justify-between gap-4">

                <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                    Rekapitulasi Absensi & Logbook Global
                </h2>

                <div class="flex items-center gap-3">

                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-800">
                            {{ $adminName }}
                        </p>

                        <p class="text-xs text-gray-500">
                            Human Resources / Administrator
                        </p>
                    </div>

                    <div class="w-11 h-11 shrink-0 bg-[#ffc107]
                                rounded-xl flex items-center justify-center
                                font-extrabold text-black shadow-inner">
                        {{ collect(explode(' ', trim($adminName)))->take(2)->map(fn($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('') ?: 'A' }}
                    </div>

                </div>

            </header>


            {{-- ISI HALAMAN --}}
            <main class="p-5 md:p-8 space-y-8">


                {{-- ==================================== --}}
                {{-- FILTER LAPORAN --}}
                {{-- ==================================== --}}

                <section class="bg-white p-6 md:p-7 rounded-3xl
                                border border-gray-200 shadow-sm space-y-5">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center sm:justify-between
                                gap-2 border-b border-gray-100 pb-4">

                        <h3 class="text-lg font-extrabold text-gray-900">
                            🔍 Filter Data Laporan
                        </h3>

                        <span class="text-sm text-gray-500">
                            Pilih parameter untuk menyaring data
                        </span>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2
                                xl:grid-cols-4 gap-5">

                        {{-- FILTER DIVISI --}}
                        <div class="space-y-2">

                            <label class="block text-sm font-bold text-gray-700">
                                Departemen / Divisi
                            </label>

                            <select
                                class="w-full px-4 py-3 bg-gray-50
                                       border border-gray-200 rounded-xl
                                       text-sm font-semibold text-gray-700
                                       focus:outline-none focus:border-[#ffc107]"
                            >
                                <option value="">Semua Divisi</option>
                                <option>Divisi IT & Web Development</option>
                                <option>Divisi Layanan & Kesehatan</option>
                                <option>Divisi Administrasi</option>
                            </select>

                        </div>


                        {{-- FILTER MENTOR --}}
                        <div class="space-y-2">

                            <label class="block text-sm font-bold text-gray-700">
                                Mentor Pembimbing
                            </label>

                            <select
                                class="w-full px-4 py-3 bg-gray-50
                                       border border-gray-200 rounded-xl
                                       text-sm font-semibold text-gray-700
                                       focus:outline-none focus:border-[#ffc107]"
                            >
                                <option value="">Semua Mentor</option>
                                <option>Budi Santoso, M.Kom.</option>
                                <option>Dr. H. Rahmat, M.Kes.</option>
                            </select>

                        </div>


                        {{-- FILTER PERIODE --}}
                        <div class="space-y-2">

                            <label class="block text-sm font-bold text-gray-700">
                                Periode / Bulan
                            </label>

                            <select
                                class="w-full px-4 py-3 bg-gray-50
                                       border border-gray-200 rounded-xl
                                       text-sm font-semibold text-gray-700
                                       focus:outline-none focus:border-[#ffc107]"
                            >
                                <option>September 2026</option>
                                <option>Agustus 2026</option>
                            </select>

                        </div>


                        {{-- TOMBOL FILTER --}}
                        <div class="flex items-end">

                            <button
                                type="button"
                                class="w-full px-4 py-3
                                       bg-[#ffc107] hover:bg-[#e0ac06]
                                       text-black font-extrabold text-sm
                                       rounded-xl transition shadow-sm"
                            >
                                Terapkan Filter
                            </button>

                        </div>

                    </div>

                </section>



                {{-- ==================================== --}}
                {{-- REKAP ABSENSI --}}
                {{-- ==================================== --}}

                <section class="bg-white p-6 md:p-7 rounded-3xl
                                border border-gray-200 shadow-sm space-y-5">

                    <div class="flex flex-col lg:flex-row
                                lg:items-center justify-between gap-4
                                border-b border-gray-100 pb-4">

                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900">
                                📅 Rekapitulasi Kehadiran Peserta
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Akumulasi kehadiran berdasarkan filter yang aktif
                            </p>
                        </div>

                        <button
                            type="button"
                            class="px-5 py-3 bg-[#191712] hover:bg-black
                                   text-white font-bold rounded-xl
                                   text-sm transition shadow-sm whitespace-nowrap"
                        >
                            📥 Unduh Rekap Absensi (Excel/PDF)
                        </button>

                    </div>


                    <div class="overflow-x-auto">

                        <table class="w-full text-left border-collapse min-w-[850px]">

                            <thead>
                                <tr class="border-b border-gray-200
                                           text-xs font-bold text-gray-500
                                           uppercase tracking-wide">

                                    <th class="py-4 px-4">No</th>
                                    <th class="py-4 px-4">Nama Mahasiswa</th>
                                    <th class="py-4 px-4">Divisi & Mentor</th>
                                    <th class="py-4 px-4 text-center">Hadir</th>
                                    <th class="py-4 px-4 text-center">Izin</th>
                                    <th class="py-4 px-4 text-center">Sakit</th>
                                    <th class="py-4 px-4 text-center">Alpa</th>
                                    <th class="py-4 px-4 text-center">Persentase</th>

                                </tr>
                            </thead>


                            <tbody class="text-sm divide-y divide-gray-100">

                                {{-- DATA CONTOH --}}
                                <tr class="hover:bg-gray-50 transition">

                                    <td class="py-4 px-4 text-gray-600 font-semibold">
                                        1
                                    </td>

                                    <td class="py-4 px-4">
                                        <p class="font-bold text-gray-900">
                                            Merry Silitonga
                                        </p>

                                        <p class="text-xs text-gray-500 mt-1">
                                            UBSI
                                        </p>
                                    </td>

                                    <td class="py-4 px-4">
                                        <p class="font-semibold text-gray-700">
                                            Divisi IT & Web Dev
                                        </p>

                                        <p class="text-xs text-gray-500 mt-1">
                                            Mentor: Budi Santoso
                                        </p>
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-emerald-600">
                                        20
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-amber-600">
                                        0
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-blue-600">
                                        0
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-red-600">
                                        0
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-block px-3 py-1.5
                                                     bg-emerald-50 text-emerald-700
                                                     font-bold rounded-lg text-xs">
                                            100%
                                        </span>
                                    </td>

                                </tr>


                                <tr class="hover:bg-gray-50 transition">

                                    <td class="py-4 px-4 text-gray-600 font-semibold">
                                        2
                                    </td>

                                    <td class="py-4 px-4">
                                        <p class="font-bold text-gray-900">
                                            Ahmad Fauzi
                                        </p>

                                        <p class="text-xs text-gray-500 mt-1">
                                            UBSI
                                        </p>
                                    </td>

                                    <td class="py-4 px-4">
                                        <p class="font-semibold text-gray-700">
                                            Divisi IT & Web Dev
                                        </p>

                                        <p class="text-xs text-gray-500 mt-1">
                                            Mentor: Budi Santoso
                                        </p>
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-emerald-600">
                                        18
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-amber-600">
                                        1
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-blue-600">
                                        1
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-red-600">
                                        0
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-block px-3 py-1.5
                                                     bg-emerald-50 text-emerald-700
                                                     font-bold rounded-lg text-xs">
                                            90%
                                        </span>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <p class="text-xs text-gray-400">
                        Catatan: data tabel ini masih berupa data contoh.
                    </p>

                </section>



                {{-- ==================================== --}}
                {{-- REKAP LOGBOOK KOLEKTIF --}}
                {{-- ==================================== --}}

                <section class="bg-white p-6 md:p-7 rounded-3xl
                                border border-gray-200 shadow-sm space-y-5">

                    <div class="flex flex-col lg:flex-row
                                lg:items-center justify-between gap-4
                                border-b border-gray-100 pb-4">

                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900">
                                📝 Rekapitulasi Logbook Kolektif
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Ringkasan aktivitas harian yang dilaporkan oleh peserta magang
                            </p>
                        </div>

                        <button
                            type="button"
                            class="px-5 py-3 bg-[#191712] hover:bg-black
                                   text-white font-bold rounded-xl
                                   text-sm transition shadow-sm whitespace-nowrap"
                        >
                            📥 Unduh Logbook Kolektif (PDF)
                        </button>

                    </div>


                    <div class="overflow-x-auto">

                        <table class="w-full text-left border-collapse min-w-[850px]">

                            <thead>
                                <tr class="border-b border-gray-200
                                           text-xs font-bold text-gray-500
                                           uppercase tracking-wide">

                                    <th class="py-4 px-4">Tanggal</th>
                                    <th class="py-4 px-4">Nama Mahasiswa</th>
                                    <th class="py-4 px-4">Aktivitas Pekerjaan / Tugas</th>
                                    <th class="py-4 px-4">Status Validasi</th>
                                    <th class="py-4 px-4">Catatan Mentor</th>

                                </tr>
                            </thead>


                            <tbody class="text-sm divide-y divide-gray-100">

                                {{-- DATA CONTOH --}}
                                <tr class="hover:bg-gray-50 transition">

                                    <td class="py-4 px-4 font-semibold text-gray-700 whitespace-nowrap">
                                        30 Sep 2026
                                    </td>

                                    <td class="py-4 px-4 font-bold text-gray-900">
                                        Merry Silitonga
                                    </td>

                                    <td class="py-4 px-4 text-gray-700 leading-relaxed">
                                        Mengembangkan komponen menu penilaian dan rekapitulasi nilai akhir di Portal Mentor.
                                    </td>

                                    <td class="py-4 px-4">
                                        <span class="inline-block px-3 py-1.5
                                                     bg-emerald-50 text-emerald-700
                                                     font-bold rounded-full text-xs">
                                            Disetujui
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 text-gray-600 italic">
                                        "Pekerjaan sangat baik dan rapi."
                                    </td>

                                </tr>


                                <tr class="hover:bg-gray-50 transition">

                                    <td class="py-4 px-4 font-semibold text-gray-700 whitespace-nowrap">
                                        30 Sep 2026
                                    </td>

                                    <td class="py-4 px-4 font-bold text-gray-900">
                                        Ahmad Fauzi
                                    </td>

                                    <td class="py-4 px-4 text-gray-700 leading-relaxed">
                                        Melakukan konfigurasi database dan manajemen tabel migrasi Laravel.
                                    </td>

                                    <td class="py-4 px-4">
                                        <span class="inline-block px-3 py-1.5
                                                     bg-amber-50 text-amber-700
                                                     font-bold rounded-full text-xs">
                                            Pending
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 text-gray-500">
                                        -
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <p class="text-xs text-gray-400">
                        Catatan: nama mahasiswa, aktivitas, dan status pada tabel ini masih berupa data contoh.
                    </p>

                </section>

            </main>

        </div>

    </div>

</body>
</html>
