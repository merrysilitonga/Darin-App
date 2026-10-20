<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fcfbf9;
        }
    </style>
</head>

<body class="text-gray-800 flex h-screen overflow-hidden">

    @include('layouts.sidebar')

    <main class="flex-1 overflow-y-auto p-10">

        <!-- HEADER -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Dashboard Utama
                </h1>

                <p class="text-sm text-gray-500 mt-0.5">
                    Selamat datang kembali,
                    <span class="font-semibold text-gray-700">
                        {{ $user->name ?? 'Pengguna' }}
                    </span>
                    ! Berikut ringkasan aktivitas magang Anda.
                </p>

                <div class="w-16 h-1 bg-[#ffc107] mt-2 rounded-full"></div>
            </div>

            <!-- TANGGAL HARI INI -->
            <div class="bg-white px-5 py-3 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                <span class="text-2xl">📅</span>

                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 block">
                        Hari Ini
                    </span>

                    <span class="text-sm font-bold text-gray-800">
                        {{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                    </span>
                </div>
            </div>
        </header>

        <!-- GRID DASHBOARD -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- KOLOM KIRI -->
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">

                <!-- CARD HARI MAGANG -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <span class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl text-xl">
                            ✅
                        </span>

                        <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full">
                            Sejak Mulai
                        </span>
                    </div>

                    <div>
                        <h4 class="text-3xl font-extrabold text-gray-800 mb-1">
                            {{ $hariBerjalan ?? 0 }} Hari
                        </h4>

                        <p class="text-xs text-gray-500 font-medium">
                            Hari Magang Berjalan
                        </p>
                    </div>
                </div>

                <!-- CARD SISA DURASI -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <span class="p-3 bg-amber-50 text-amber-600 rounded-2xl text-xl">
                            ⏳
                        </span>

                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full
                            {{ ($periodeAktif ?? false) ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                            @if($periodeAktif ?? false)
                                Periode Aktif
                            @elseif(($sisaDurasi ?? '') === 'Belum dimulai')
                                Belum Dimulai
                            @elseif(($sisaDurasi ?? '') === 'Selesai')
                                Selesai
                            @else
                                Periode Magang
                            @endif
                        </span>
                    </div>

                    <div>
                        <h4 class="text-3xl font-extrabold text-gray-800 mb-1">
                            {{ $sisaDurasi ?? 'Belum diatur' }}
                        </h4>

                        <p class="text-xs text-gray-500 font-medium">
                            Sisa Durasi Magang
                        </p>

                        @if(empty($user->start_date) || empty($user->end_date))
                            <p class="text-xs text-rose-500 mt-2">
                                Tanggal magang belum diatur oleh admin.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- CARD LOGBOOK -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 sm:col-span-2 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full uppercase tracking-wider mb-2 inline-block">
                            Laporan
                        </span>

                        <h4 class="text-2xl font-extrabold text-gray-800 mb-0.5">
                            {{ $jumlahLaporan ?? 0 }} Catatan
                        </h4>

                        <p class="text-xs text-gray-500">
                            Jumlah laporan logbook selama periode magang Anda.
                        </p>
                    </div>

                    <div class="p-4 bg-blue-50 text-blue-600 rounded-2xl text-2xl">
                        📝
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: MENTOR -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-800 text-sm">
                            Mentor Pembimbing
                        </h3>

                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    </div>

                    <!-- PROFIL MENTOR -->
                    <div class="flex flex-col items-center text-center bg-gray-50/60 p-4 rounded-2xl border border-gray-100 mb-4">
                        <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-800 font-bold flex items-center justify-center text-xl mb-3 border-2 border-white shadow-sm">
                            👨‍💻
                        </div>

                        <h4 class="font-bold text-gray-800 text-sm">
                            Bpk. Dr. Ahmad Fauzi, M.Kom.
                        </h4>

                        <span class="text-xs text-gray-500 mt-0.5">
                            Lead Software Engineer
                        </span>

                        <span class="text-[10px] bg-gray-200 text-gray-700 font-semibold px-2.5 py-0.5 rounded-md mt-2">
                            Pembimbing Industri
                        </span>
                    </div>

                    <!-- DETAIL MENTOR -->
                    <div class="space-y-2 text-xs text-gray-600">
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100">
                            <span class="text-gray-400">Email:</span>
                            <span class="font-medium text-gray-700">
                                ahmad.fauzi@darin.id
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100">
                            <span class="text-gray-400">WhatsApp:</span>
                            <span class="font-medium text-gray-700">
                                +62 812-3456-7890
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-gray-400">Jam Konsultasi:</span>
                            <span class="font-medium text-gray-700">
                                13:00 - 15:00 WIB
                            </span>
                        </div>
                    </div>
                </div>

                <!-- TOMBOL WHATSAPP -->
                <a
                    href="https://wa.me/6281234567890"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full mt-5 py-2.5 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-xl flex items-center justify-center gap-2 hover:bg-emerald-600 hover:text-white transition shadow-sm"
                >
                    💬 Hubungi Mentor via WhatsApp
                </a>
            </div>

        </div>
    </main>
</body>
</html>