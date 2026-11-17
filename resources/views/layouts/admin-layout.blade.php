<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Admin') - DARIN</title>
    {{-- Tailwind CDN, sama seperti halaman lain (tidak perlu npm run build). --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>

    {{--
        Sidebar responsif. Ditulis dengan CSS biasa (bukan kelas Tailwind baru)
        agar tidak perlu menjalankan npm run build.
    --}}
    <style>
        .darin-admin-aside { flex-shrink: 0; }
        .darin-admin-main { min-width: 0; }
        .darin-admin-topbar { display: none; }
        .darin-admin-latar { display: none; }

        @media (max-width: 767.98px) {
            .darin-admin-topbar {
                display: flex; position: fixed; top: 0; left: 0; right: 0; z-index: 30;
                height: 56px; align-items: center; justify-content: space-between;
                padding: 0 16px; background: #191712; color: #fff;
                box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
            }
            .darin-admin-topbar button {
                width: 40px; height: 40px; border: 0; border-radius: 12px;
                background: transparent; color: #fff; font-size: 24px; line-height: 1; cursor: pointer;
            }
            .darin-admin-aside {
                position: fixed; top: 0; bottom: 0; left: 0; z-index: 50;
                transform: translateX(-100%); transition: transform .2s ease; overflow-y: auto;
            }
            .darin-admin-aside.terbuka { transform: none; }
            .darin-admin-latar.terbuka {
                display: block; position: fixed; inset: 0; z-index: 40; background: rgba(0, 0, 0, .5);
            }
            .darin-admin-main { padding-top: 72px !important; }
        }
    </style>
</head>

<body class="bg-[#f8f9fa] text-gray-800 font-sans antialiased">
    {{-- BAR ATAS (HANYA DI HP) --}}
    <div class="darin-admin-topbar">
        <button type="button" data-admin-sidebar-buka aria-controls="darin-admin-sidebar" aria-expanded="false" aria-label="Buka menu">☰</button>
        <strong style="letter-spacing: .1em;">DARIN</strong>
        <span style="width: 40px;"></span>
    </div>

    <div class="darin-admin-latar" data-admin-sidebar-latar></div>

    <div class="flex h-screen overflow-hidden">

        {{-- SIDEBAR ADMIN --}}
        <aside id="darin-admin-sidebar" class="darin-admin-aside flex w-64 shrink-0 flex-col justify-between bg-[#191712] text-white shadow-xl">

            <div>
                {{-- BRAND --}}
                <div class="flex items-center gap-3 border-b border-white/10 p-6">
                    <h1 class="text-lg font-extrabold tracking-wider">DARIN</h1>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-amber-400">
                        Portal HR / Admin
                    </p>
                </div>

                {{-- NAVIGASI MENU ADMIN (satu sumber: partials.admin-menu) --}}
                <div class="p-4">
                    @include('partials.admin-menu')
                </div>
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
        <main class="darin-admin-main min-w-0 flex-1 overflow-y-auto p-4 md:p-8">
            {{-- Judul halaman (opsional) untuk view yang mengisi section page_title --}}
            @hasSection('page_title')
                <header class="mx-auto mb-6 max-w-7xl">
                    <h1 class="text-2xl font-extrabold text-gray-900">@yield('page_title')</h1>
                    @hasSection('page_subtitle')
                        <p class="mt-1 text-sm text-gray-500">@yield('page_subtitle')</p>
                    @endif
                </header>
            @endif

            @yield('content')
        </main>

    </div>
    <script>
        (function () {
            const sidebar = document.getElementById('darin-admin-sidebar');
            const latar = document.querySelector('[data-admin-sidebar-latar]');
            const tombol = document.querySelector('[data-admin-sidebar-buka]');

            if (!sidebar || !latar || !tombol) {
                return;
            }

            function atur(terbuka) {
                sidebar.classList.toggle('terbuka', terbuka);
                latar.classList.toggle('terbuka', terbuka);
                tombol.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
            }

            tombol.addEventListener('click', function () { atur(true); });
            latar.addEventListener('click', function () { atur(false); });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') { atur(false); }
            });
            sidebar.querySelectorAll('a').forEach(function (tautan) {
                tautan.addEventListener('click', function () { atur(false); });
            });
        })();
    </script>
</body>
</html>