<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - DARIN</title>

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

    @php
        $admin = auth()->user();
        $adminName = $admin->name ?? 'Administrator';

        $nameParts = preg_split('/\s+/', trim($adminName)) ?: [];
        $initials = '';

        foreach (array_slice($nameParts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
    @endphp

    <div class="flex min-h-screen">

        {{-- SIDEBAR ADMIN --}}
        <aside class="w-64 shrink-0 bg-[#191712] text-white flex flex-col justify-between">

            <div>

                {{-- LOGO DARIN --}}
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
                            Portal Admin
                        </p>
                    </div>

                </div>


                {{-- NAVIGASI ADMIN --}}
                <nav class="p-4 space-y-2 text-sm font-semibold">

                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               bg-[#ffc107] text-black rounded-xl shadow-md"
                    >
                        
                        <span>Dashboard</span>
                    </a>

                    <a
                        href="{{ route('admin.users') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               text-gray-300 hover:bg-white/10 hover:text-white
                               rounded-xl transition"
                    >
                        
                        <span>Manajemen User</span>
                    </a>

                    <a
                        href="{{ route('admin.divisions') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               text-gray-300 hover:bg-white/10 hover:text-white
                               rounded-xl transition"
                    >
                        
                        <span>Divisi</span>
                    </a>

                    <a
                        href="{{ route('admin.attendance') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               text-gray-300 hover:bg-white/10 hover:text-white
                               rounded-xl transition"
                    >
                        
                        <span>Absensi</span>
                    </a>

                    <a
                        href="{{ route('admin.settings') }}"
                        class="flex items-center gap-3 px-4 py-3.5
                               text-gray-300 hover:bg-white/10 hover:text-white
                               rounded-xl transition"
                    >
                        <span>Pengaturan</span>
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
                               px-4 py-3 bg-red-600/20 hover:bg-red-600
                               text-red-300 hover:text-white rounded-xl
                               text-sm font-bold transition"
                    >
                         Logout
                    </button>
                </form>

            </div>

        </aside>


        {{-- KONTEN UTAMA --}}
        <div class="flex-1 min-w-0 flex flex-col">

            {{-- TOPBAR --}}
            <header class="bg-white border-b border-gray-200
                           px-6 md:px-8 py-5 shadow-sm
                           flex items-center justify-between gap-4">

                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                        Dashboard Administrator
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Ringkasan pengelolaan sistem DARIN.
                    </p>
                </div>

                <div class="flex items-center gap-3">

                    <div class="text-right">
                        <p class="text-sm md:text-base font-bold text-gray-800">
                            {{ $adminName }}
                        </p>

                        <p class="text-sm text-gray-500">
                            Administrator
                        </p>
                    </div>

                    <div class="w-11 h-11 shrink-0 bg-[#ffc107]
                                rounded-xl flex items-center justify-center
                                font-extrabold text-black shadow-inner">
                        {{ $initials ?: 'A' }}
                    </div>

                </div>

            </header>


            {{-- ISI DASHBOARD --}}
            <main class="flex-1 p-5 md:p-8 space-y-6">

                {{-- WELCOME BANNER --}}
                <section class="bg-gradient-to-r from-[#191712] to-[#2c2922]
                                text-white p-6 md:p-8 rounded-3xl shadow-lg
                                flex items-center justify-between
                                relative overflow-hidden">

                    <div class="absolute -right-5 -bottom-8 text-white/5
                                text-9xl font-extrabold select-none
                                pointer-events-none">
                        ⚡
                    </div>

                    <div class="relative z-10">

                        <h3 class="text-xl md:text-2xl font-extrabold
                                   text-[#ffc107] mb-2">
                            Selamat Datang, {{ $adminName }}!
                        </h3>

                        <p class="text-sm md:text-base text-gray-300
                                  leading-relaxed max-w-3xl">
                            Kelola pengguna, data divisi, serta informasi
                            kehadiran peserta magang melalui panel
                            administrator DARIN.
                        </p>

                    </div>

                </section>


                {{-- KARTU STATISTIK --}}
                <section class="grid grid-cols-1 sm:grid-cols-2
                                xl:grid-cols-4 gap-5">


                    {{-- TOTAL PENGGUNA --}}
                    <div class="bg-white p-5 md:p-6 rounded-2xl
                                shadow-sm border border-gray-200
                                border-l-4 border-l-blue-500">

                        <div class="flex items-center justify-between gap-3">

                            <div>
                                <p class="text-sm font-semibold text-gray-500">
                                    Total Pengguna
                                </p>

                                <h3 class="text-3xl font-extrabold
                                           text-gray-900 mt-2">
                                    {{ $totalUsers ?? 0 }}
                                </h3>
                            </div>

                            <div class="w-12 h-12 bg-blue-50 text-blue-600
                                        rounded-xl flex items-center
                                        justify-center text-2xl">
                                👥
                            </div>

                        </div>
                    </div>


                    {{-- TOTAL DIVISI --}}
                    <div class="bg-white p-5 md:p-6 rounded-2xl
                                shadow-sm border border-gray-200
                                border-l-4 border-l-green-500">

                        <div class="flex items-center justify-between gap-3">

                            <div>
                                <p class="text-sm font-semibold text-gray-500">
                                    Total Divisi
                                </p>

                                <h3 class="text-3xl font-extrabold
                                           text-gray-900 mt-2">
                                    {{ $totalDivisions ?? 0 }}
                                </h3>
                            </div>

                            <div class="w-12 h-12 bg-green-50 text-green-600
                                        rounded-xl flex items-center
                                        justify-center text-2xl">
                                🏢
                            </div>

                        </div>
                    </div>


                    {{-- PESERTA MAGANG --}}
                    <div class="bg-white p-5 md:p-6 rounded-2xl
                                shadow-sm border border-gray-200
                                border-l-4 border-l-amber-500">

                        <div class="flex items-center justify-between gap-3">

                            <div>
                                <p class="text-sm font-semibold text-gray-500">
                                    Peserta Magang
                                </p>

                                <h3 class="text-3xl font-extrabold
                                           text-gray-900 mt-2">
                                    {{ $totalInterns ?? 0 }}
                                </h3>
                            </div>

                            <div class="w-12 h-12 bg-amber-50 text-amber-600
                                        rounded-xl flex items-center
                                        justify-center text-2xl">
                                🎓
                            </div>

                        </div>
                    </div>


                    {{-- TOTAL MENTOR --}}
                    <div class="bg-white p-5 md:p-6 rounded-2xl
                                shadow-sm border border-gray-200
                                border-l-4 border-l-purple-500">

                        <div class="flex items-center justify-between gap-3">

                            <div>
                                <p class="text-sm font-semibold text-gray-500">
                                    Mentor / Pembimbing
                                </p>

                                <h3 class="text-3xl font-extrabold
                                           text-gray-900 mt-2">
                                    {{ $totalMentors ?? 0 }}
                                </h3>
                            </div>

                            <div class="w-12 h-12 bg-purple-50 text-purple-600
                                        rounded-xl flex items-center
                                        justify-center text-2xl">
                                🧑‍🏫
                            </div>

                        </div>
                    </div>

                </section>


                {{-- INFORMASI SISTEM --}}
                <section class="bg-amber-50 border border-amber-200
                                p-6 md:p-7 rounded-2xl text-amber-900">

                    <div class="flex items-start gap-4">

                        <div class="w-12 h-12 shrink-0 bg-[#ffc107]
                                    rounded-xl flex items-center
                                    justify-center text-2xl">
                            ⚡
                        </div>

                        <div>

                            <h3 class="text-lg font-extrabold mb-2">
                                Panel Administrator DARIN
                            </h3>

                            <p class="text-sm md:text-base leading-relaxed">
                                Gunakan panel ini untuk mengelola akun pengguna,
                                pembagian divisi, dan pemantauan absensi.
                                Angka statistik ditampilkan berdasarkan data
                                yang dikirim oleh controller dashboard.
                            </p>

                        </div>

                    </div>

                </section>

            </main>

        </div>

    </div>

</body>
</html>