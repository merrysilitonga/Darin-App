<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - DARIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
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

        {{-- SIDEBAR ADMIN (Sesuai Asli Anda) --}}
        <aside class="w-64 shrink-0 bg-[#191712] text-white flex flex-col justify-between">
            <div>
                <div class="p-6 flex items-center gap-3 border-b border-white/10">
                    <div class="bg-[#ffc107] text-black font-extrabold w-12 h-12 shrink-0 rounded-xl flex items-center justify-center text-2xl shadow-md">⚡</div>
                    <div>
                        <h1 class="font-extrabold tracking-wider text-xl">DARIN</h1>
                        <p class="text-xs uppercase tracking-wider text-amber-400 font-bold mt-1">Portal Admin</p>
                    </div>
                </div>

                <nav class="p-4 space-y-2 text-sm font-semibold">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3.5 bg-[#ffc107] text-black rounded-xl shadow-md"><span>Dashboard</span></a>
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-300 hover:bg-white/10 hover:text-white rounded-xl transition"><span>Manajemen User</span></a>
                    <a href="{{ route('admin.divisions') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-300 hover:bg-white/10 hover:text-white rounded-xl transition"><span>Divisi</span></a>
                    <a href="{{ route('admin.attendance') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-300 hover:bg-white/10 hover:text-white rounded-xl transition"><span>Absensi</span></a>
                    <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-4 py-3.5 text-gray-300 hover:bg-white/10 hover:text-white rounded-xl transition"><span>Pengaturan</span></a>
                </nav>
            </div>

            <div class="p-4 border-t border-white/10">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-600/20 hover:bg-red-600 text-red-300 hover:text-white rounded-xl text-sm font-bold transition">Logout</button>
                </form>
            </div>
        </aside>

        {{-- KONTEN UTAMA --}}
        <div class="flex-1 min-w-0 flex flex-col">

            {{-- TOPBAR --}}
            <header class="bg-white border-b border-gray-200 px-6 md:px-8 py-5 shadow-sm flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Dashboard Administrator</h2>
                    <p class="text-sm text-gray-500 mt-1">Ringkasan pengelolaan sistem DARIN.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm md:text-base font-bold text-gray-800">{{ $adminName }}</p>
                        <p class="text-sm text-gray-500">Administrator</p>
                    </div>
                    <div class="w-11 h-11 shrink-0 bg-[#ffc107] rounded-xl flex items-center justify-center font-extrabold text-black shadow-inner">{{ $initials ?: 'A' }}</div>
                </div>
            </header>

            {{-- ISI DASHBOARD --}}
            <main class="flex-1 p-5 md:p-8 space-y-6">

                {{-- WELCOME BANNER (Dimodifikasi untuk Status Auto-Deactivation) --}}
                <section class="bg-gradient-to-r from-[#191712] to-[#2c2922] text-white p-6 md:p-8 rounded-3xl shadow-lg flex flex-col md:flex-row gap-6 items-start md:items-center justify-between relative overflow-hidden">
                    <div class="absolute -right-5 -bottom-8 text-white/5 text-9xl font-extrabold select-none pointer-events-none">⚡</div>
                    
                    <div class="relative z-10 max-w-3xl">
                        <h3 class="text-xl md:text-2xl font-extrabold text-[#ffc107] mb-2">Selamat Datang, {{ $adminName }}!</h3>
                        <p class="text-sm md:text-base text-gray-300 leading-relaxed mb-4">
                            Kelola pengguna, divisi, dan pemantauan sistem DARIN.
                        </p>
                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1.5 border border-emerald-500/20">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-xs font-semibold text-emerald-300">Auto-Deactivation Sistem Aktif</span>
                        </div>
                    </div>
                </section>

                {{-- KARTU STATISTIK (Sesuai Asli Anda) --}}
                <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    
                    {{-- TOTAL PENGGUNA --}}
                    <div class="bg-white p-5 md:p-6 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-blue-500">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Total Pengguna</p>
                                <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ $totalUsers ?? 0 }}</h3>
                            </div>
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">👥</div>
                        </div>
                    </div>

                    {{-- TOTAL DIVISI --}}
                    <div class="bg-white p-5 md:p-6 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-green-500">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Total Divisi</p>
                                <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ $totalDivisions ?? 0 }}</h3>
                            </div>
                            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-2xl">🏢</div>
                        </div>
                    </div>

                    {{-- PESERTA MAGANG --}}
                    <div class="bg-white p-5 md:p-6 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-amber-500">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Peserta Magang</p>
                                <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ $totalInterns ?? 0 }}</h3>
                            </div>
                            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-2xl">🎓</div>
                        </div>
                    </div>

                    {{-- TOTAL MENTOR --}}
                    <div class="bg-white p-5 md:p-6 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-purple-500">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Total Mentor</p>
                                <h3 class="text-3xl font-extrabold text-gray-900 mt-2">{{ $totalMentors ?? 0 }}</h3>
                            </div>
                            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-2xl">🧑‍🏫</div>
                        </div>
                    </div>
                </section>

                {{-- MENU PINTASAN APLIKASI (Tambahan PRD 1.1) --}}
                <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    
                    {{-- Monitoring Logbook --}}
                    <a href="{{ route('admin.logbooks') }}" class="group block bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:border-[#ffc107] hover:shadow-md transition">
                        <div class="w-10 h-10 bg-slate-50 text-slate-600 rounded-lg flex items-center justify-center text-xl mb-3 group-hover:bg-amber-50 group-hover:text-amber-600 transition">📝</div>
                        <h4 class="text-base font-bold text-gray-800">Monitoring Logbook</h4>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Pantau catatan aktivitas harian peserta magang lintas divisi.</p>
                    </a>

                    {{-- Survei Kepuasan --}}
                    <a href="{{ route('admin.feedbacks') ?? '#' }}" class="group block bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:border-[#ffc107] hover:shadow-md transition">
                        <div class="w-10 h-10 bg-slate-50 text-slate-600 rounded-lg flex items-center justify-center text-xl mb-3 group-hover:bg-blue-50 group-hover:text-blue-600 transition">📊</div>
                        <h4 class="text-base font-bold text-gray-800">Survei Kepuasan</h4>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Lihat masukan dan penilaian peserta terhadap aplikasi.</p>
                    </a>

                    {{-- Danger Zone --}}
                    <a href="{{ route('admin.reset-data.view') }}" class="group block bg-red-50 p-6 rounded-2xl border border-red-200 shadow-sm hover:bg-red-600 transition">
                        <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-xl mb-3 group-hover:bg-white transition">🚨</div>
                        <h4 class="text-base font-bold text-red-700 group-hover:text-white transition">Danger Zone (Reset)</h4>
                        <p class="text-xs text-red-600/80 mt-1 leading-relaxed group-hover:text-white/90">Area pembersihan data saat pergantian periode magang.</p>
                    </a>

                </section>
            </main>
        </div>
    </div>
</body>
</html>