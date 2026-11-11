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

        html {
            overflow-y: auto;
        }

        body {
            overflow-y: visible;
        }
    </style>
</head>

<body class="min-h-screen text-gray-800">

    <div class="flex min-h-screen items-stretch">

        {{-- SIDEBAR --}}
        @include('layouts.sidebar')

        {{-- KONTEN DASHBOARD --}}
        <main class="min-w-0 flex-1 p-5 md:p-10">

            {{-- NOTIFIKASI (mis. penolakan aksi saat periode magang berakhir) --}}
            @if (session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (auth()->user()?->isInternshipEnded())
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <span class="font-bold">Periode magang telah berakhir.</span>
                    Anda masih dapat melihat dan mengunduh riwayat, tetapi tidak dapat absen,
                    mengisi logbook, atau mengajukan izin.
                </div>
            @endif

            <!-- HEADER -->
            <header class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Dashboard Utama
                    </h1>

                    <p class="mt-0.5 text-sm text-gray-500">
                        Selamat datang kembali,
                        <span class="font-semibold text-gray-700">
                            {{ $user->name ?? 'Pengguna' }}
                        </span>
                        ! Berikut ringkasan aktivitas magang Anda.
                    </p>

                    <div class="mt-2 h-1 w-16 rounded-full bg-[#ffc107]"></div>
                </div>

                <!-- TANGGAL HARI INI -->
                <div class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white px-5 py-3 shadow-sm">
                    <span class="text-2xl">📅</span>

                    <div>
                        <span class="block text-[10px] font-bold uppercase text-gray-400">
                            Hari Ini
                        </span>

                        <span class="text-sm font-bold text-gray-800">
                            {{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- GRID DASHBOARD -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                <!-- KOLOM KIRI -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:col-span-2">

                    <!-- CARD HARI MAGANG -->
                    <div class="flex flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="rounded-2xl bg-emerald-50 p-3 text-xl text-emerald-600">
                                ✅
                            </span>

                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                Sejak Mulai
                            </span>
                        </div>

                        <div>
                            <h4 class="mb-1 text-3xl font-extrabold text-gray-800">
                                {{ $hariBerjalan ?? 0 }} Hari
                            </h4>

                            <p class="text-xs font-medium text-gray-500">
                                Hari Magang Berjalan
                            </p>
                        </div>
                    </div>

                    <!-- CARD SISA DURASI -->
                    <div class="flex flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="rounded-2xl bg-amber-50 p-3 text-xl text-amber-600">
                                ⏳
                            </span>

                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold
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
                            <h4 class="mb-1 text-3xl font-extrabold text-gray-800">
                                {{ $sisaDurasi ?? 'Belum diatur' }}
                            </h4>

                            <p class="text-xs font-medium text-gray-500">
                                Sisa Durasi Magang
                            </p>

                            @if(empty($user->start_date) || empty($user->end_date))
                                <p class="mt-2 text-xs text-rose-500">
                                    Tanggal magang belum diatur oleh admin.
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- CARD LOGBOOK -->
                    <div class="flex items-center justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:col-span-2">
                        <div>
                            <span class="mb-2 inline-block rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700">
                                Laporan
                            </span>

                            <h4 class="mb-0.5 text-2xl font-extrabold text-gray-800">
                                {{ $jumlahLaporan ?? 0 }} Catatan
                            </h4>

                            <p class="text-xs text-gray-500">
                                Jumlah laporan logbook selama periode magang Anda.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-blue-50 p-4 text-2xl text-blue-600">
                            📝
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN: MENTOR (data asli dari penugasan Admin/HR) -->
                <div class="flex flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-800">
                                Mentor Pembimbing
                            </h3>

                            <span class="h-2 w-2 rounded-full {{ $mentor ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                        </div>

                        @if ($mentor)

                            <!-- PROFIL MENTOR -->
                            <div class="mb-4 flex flex-col items-center rounded-2xl border border-gray-100 bg-gray-50/60 p-4 text-center">
                                @if ($mentor->photo)
                                    <img
                                        src="{{ asset('storage/' . $mentor->photo) }}"
                                        alt="Foto {{ $mentor->name }}"
                                        class="mb-3 h-16 w-16 rounded-full border-2 border-white object-cover shadow-sm"
                                    >
                                @else
                                    <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-full border-2 border-white bg-amber-100 text-xl font-bold text-amber-800 shadow-sm">
                                        {{ mb_strtoupper(mb_substr($mentor->name, 0, 1)) }}
                                    </div>
                                @endif

                                <h4 class="text-sm font-bold text-gray-800">
                                    {{ $mentor->name }}
                                </h4>

                                <span class="mt-0.5 text-xs text-gray-500">
                                    {{ $mentor->position ?: 'Mentor Pembimbing' }}
                                </span>

                                @if ($mentor->division)
                                    <span class="mt-2 rounded-md bg-gray-200 px-2.5 py-0.5 text-[10px] font-semibold text-gray-700">
                                        {{ $mentor->division->name }}
                                    </span>
                                @endif
                            </div>

                            <!-- DETAIL MENTOR -->
                            <div class="space-y-2 text-xs text-gray-600">
                                <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-1.5">
                                    <span class="text-gray-400">Email:</span>
                                    <a
                                        href="mailto:{{ $mentor->email }}"
                                        class="break-all text-right font-medium text-gray-700 hover:underline"
                                    >
                                        {{ $mentor->email }}
                                    </a>
                                </div>

                                <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-1.5">
                                    <span class="text-gray-400">No. HP:</span>
                                    <span class="text-right font-medium text-gray-700">
                                        {{ $mentor->phone ?: 'Belum diisi' }}
                                    </span>
                                </div>

                                @if ($mentor->nip)
                                    <div class="flex items-center justify-between gap-3 py-1.5">
                                        <span class="text-gray-400">NIP:</span>
                                        <span class="text-right font-medium text-gray-700">
                                            {{ $mentor->nip }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                        @else

                            <!-- MENTOR BELUM DITETAPKAN -->
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/60 p-5 text-center">
                                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-xl text-gray-400">
                                    ?
                                </div>

                                <p class="text-sm font-bold text-gray-700">
                                    Mentor belum ditetapkan
                                </p>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Hubungi Admin/HR agar mentor pembimbing ditetapkan
                                    melalui menu Manajemen Pengguna.
                                </p>
                            </div>

                        @endif
                    </div>

                    <!-- TOMBOL WHATSAPP (hanya jika nomor HP mentor valid) -->
                    @if ($mentorWhatsapp)
                        <a
                            href="https://wa.me/{{ $mentorWhatsapp }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-50 py-2.5 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-600 hover:text-white"
                        >
                            💬 Hubungi Mentor via WhatsApp
                        </a>
                    @elseif ($mentor)
                        <p class="mt-5 text-center text-[11px] leading-5 text-gray-400">
                            Nomor WhatsApp mentor belum tersedia.
                        </p>
                    @endif
                </div>

            </div>

            <!-- FOOTER -->
            <footer class="mt-8 pb-4 text-center text-xs text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </footer>

        </main>

    </div>

</body>
</html>