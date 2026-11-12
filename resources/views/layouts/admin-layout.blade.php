<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DARIN - Portal Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f8f9fa] text-gray-800 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">

        {{-- SIDEBAR ADMIN --}}
        <aside class="hidden w-64 flex-col justify-between bg-[#191712] text-white shadow-xl md:flex">

            <div>
                {{-- BRAND --}}
                <div class="flex items-center gap-3 border-b border-white/10 p-6">
                    <h1 class="text-lg font-extrabold tracking-wider">DARIN</h1>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-amber-400">
                        Portal HR / Admin
                    </p>
                </div>

                {{-- NAVIGASI MENU ADMIN --}}
                <nav class="space-y-1.5 p-4 text-sm font-semibold">

                    <a href="/admin/dashboard"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                       {{ request()->is('admin/dashboard*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                        <span>Dashboard Utama</span>
                    </a>

                    <a href="/admin/users"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                       {{ request()->is('admin/users*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                        <span>Manajemen Pengguna</span>
                    </a>

                    <a href="/admin/divisions"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                       {{ request()->is('admin/divisions*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                        <span>Penempatan & Divisi</span>
                    </a>

                    <a href="/admin/attendance"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                       {{ request()->is('admin/attendance*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                        <span>Rekap Absensi & Logbook</span>
                    </a>

                    {{-- SARAN & MASUKAN PESERTA --}}
                    @php
                        $jumlahSaranBaru = \App\Models\Feedback::where('status', \App\Models\Feedback::STATUS_NEW)->count();
                    @endphp
                    <a href="{{ route('admin.feedbacks') }}"
                       class="flex items-center justify-between gap-3 rounded-xl px-4 py-3 transition
                       {{ request()->is('admin/feedbacks*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                        <span>Saran &amp; Masukan</span>
                        @if ($jumlahSaranBaru > 0)
                            <span class="rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $jumlahSaranBaru }}</span>
                        @endif
                    </a>

                    {{-- KALENDER HARI LIBUR --}}
                    <a href="{{ route('admin.holidays') }}"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                       {{ request()->is('admin/holidays*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                        <span>Kalender Hari Libur</span>
                    </a>

                    <a href="/admin/settings"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                       {{ request()->is('admin/settings*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                        <span>Pengaturan Sistem</span>
                    </a>

                </nav>
            </div>

            {{-- TOMBOL LOGOUT --}}
            <div class="border-t border-white/10 p-4">
                <form action="/logout" method="POST">
                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-500/10 px-3 py-2.5 text-sm font-semibold text-red-400 transition hover:bg-red-500 hover:text-white"
                    >
                        Logout
                    </button>
                </form>
            </div>

        </aside>

        {{-- KONTEN UTAMA --}}
        <main class="flex-1 overflow-y-auto p-4 md:p-8">
            @yield('content')
        </main>

    </div>
</body>
</html>