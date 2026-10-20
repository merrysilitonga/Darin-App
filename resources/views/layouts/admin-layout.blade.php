<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DARIN - Portal Admin</title>
    <!-- Tailwind CSS (Sesuaikan dengan setup aset Anda, misal Vite/CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8f9fa] text-gray-800 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR ADMIN -->
        <aside class="w-64 bg-[#191712] text-white flex flex-col justify-between hidden md:flex shadow-xl">
            <div>
                <div class="p-6 flex items-center gap-3 border-b border-white/10">
                    <h1 class="font-extrabold tracking-wider text-lg">DARIN</h1>
                    <p class="text-[9px] uppercase tracking-widest text-amber-400 font-bold">Portal HR / Admin</p>
                </div>

                <!-- Navigasi Menu Admin -->
                <nav class="p-4 space-y-1.5 text-xs font-semibold">
                    <a href="/admin/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('admin/dashboard*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                        <span>📊</span>
                        <span>Dashboard Utama</span>
                    </a>
                    <a href="/admin/users" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('admin/users*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                        <span>👥</span>
                        <span>Manajemen Pengguna</span>
                    </a>
                    <a href="/admin/divisions" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('admin/divisions*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                        <span>🏢</span>
                        <span>Penempatan & Divisi</span>
                    </a>
                    <a href="/admin/attendance" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('admin/attendance*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                        <span>📅</span>
                        <span>Rekap Absensi & Logbook</span>
                    </a>
                    <a href="/admin/settings" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('admin/settings*') ? 'bg-[#ffc107] text-black' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                        <span>⚙️</span>
                        <span>Pengaturan Sistem</span>
                    </a>
                </nav>
            </div>

            <!-- Tombol Logout -->
            <div class="p-4 border-t border-white/10">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-xl transition text-xs font-semibold">
                        Keluar / Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </main>

    </div>
</body>
</html>