{{-- resources/views/layouts/sidebar.blade.php --}}

@php
    $currentUser = Auth::user();
    $role = strtolower(trim((string) ($currentUser->role ?? '')));

    $isMentor = $role === 'mentor';
    $isAdmin = in_array($role, ['admin', 'hr'], true);
    $isStudent = in_array($role, ['magang', 'intern'], true);

    $menuItemClass = 'flex items-center rounded-xl px-4 py-3 transition';
    $activeClass = 'bg-[#ffc107] font-bold text-black';
    $inactiveClass = 'text-gray-300 hover:bg-white/5 hover:text-white';

    // Badge mentor (diisi View Composer di AppServiceProvider).
    $mentorPending = $mentorPending ?? ['logbook' => 0, 'leave' => 0, 'total' => 0, 'overdue' => 0];
    $badgeClass = 'ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold leading-4 text-white';
@endphp

{{--
    Responsif:
    - Layar besar (>= 1024px): sidebar tampil di kiri seperti biasa.
    - HP / tablet: sidebar tersembunyi, dibuka lewat tombol ☰ di bar atas.
--}}
<style>
    @media (max-width: 1023.98px) {
        body { padding-top: 56px; }
    }
</style>

<div class="fixed inset-x-0 top-0 z-30 flex h-14 items-center justify-between bg-[#191712] px-4 text-white shadow-md lg:hidden">
    <button
        type="button"
        data-sidebar-buka
        aria-controls="darin-sidebar"
        aria-expanded="false"
        aria-label="Buka menu"
        class="flex h-10 w-10 items-center justify-center rounded-xl text-2xl leading-none hover:bg-white/10"
    >
        ☰
    </button>

    <span class="flex items-center gap-2 font-extrabold tracking-wider">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#ffc107] text-sm text-[#191712]">D</span>
        DARIN
    </span>

    <span class="w-10"></span>
</div>

<div data-sidebar-latar class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden"></div>

<aside
    id="darin-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 -translate-x-full flex-col justify-between overflow-y-auto bg-[#191712] p-6 text-white shadow-xl transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0"
>

    <div>
        {{-- BRAND --}}
        <a
            href="{{ $isMentor ? route('mentor.dashboard') : ($isAdmin ? route('admin.dashboard') : route('dashboard.magang')) }}"
            class="mb-8 flex items-center gap-3"
        >
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#ffc107] text-lg font-extrabold text-[#191712]">
                D
            </span>

            <span class="text-xl font-extrabold tracking-wider">
                DARIN
            </span>
        </a>

        {{-- PROFIL --}}
        <a
            href="{{ route('profil') }}"
            class="group mb-8 flex flex-col items-center rounded-2xl border border-white/5 bg-[#24211b] p-4 text-center transition hover:bg-[#2c2822]"
        >
            <span class="mb-3 rounded-full bg-[#ffc107]/10 px-3 py-1 text-xs font-bold text-[#ffc107]">
                PROFIL
            </span>

            <div class="mb-3 flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-2 border-[#ffc107] bg-gray-700">
                @if ($currentUser && $currentUser->photo)
                    <img
                        src="{{ asset('storage/' . $currentUser->photo) }}"
                        alt="Foto {{ $currentUser->name }}"
                        class="h-full w-full object-cover"
                    >
                @else
                    <span class="text-2xl font-semibold text-white">
                        {{ strtoupper(substr($currentUser->name ?? 'P', 0, 1)) }}
                    </span>
                @endif
            </div>

            <h3 class="text-base font-bold group-hover:text-[#ffc107]">
                {{ $currentUser->name ?? 'Pengguna DARIN' }}
            </h3>

            <span class="mb-2 mt-1 rounded-full bg-[#ffc107]/20 px-2 py-0.5 text-[10px] font-bold text-[#ffc107]">
                {{ strtoupper($role ?: 'PENGGUNA') }}
            </span>

            <span class="max-w-full break-words rounded-md bg-black/40 px-3 py-1 text-xs text-gray-400">
                {{ $currentUser->university ?? $currentUser->position ?? 'Pengguna DARIN' }}
            </span>
        </a>

        {{-- MENU MAHASISWA --}}
        @if ($isStudent)

            <p class="mb-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-500">
                Menu Mahasiswa
            </p>

            <nav class="space-y-2">

                <a
                    href="{{ route('dashboard.magang') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('dashboard.magang') ? $activeClass : $inactiveClass }}"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('absen') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('absen', 'absen.store', 'absen.checkout') ? $activeClass : $inactiveClass }}"
                >
                    Mulai Absen
                </a>

                <a
                    href="{{ route('riwayat.absen') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('riwayat.absen', 'riwayat.export-*', 'izin.*', 'rekap.saya*') ? $activeClass : $inactiveClass }}"
                >
                    Riwayat Absensi &amp; Izin
                </a>

                <a
                    href="{{ route('logbook') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('logbook', 'logbook.*') ? $activeClass : $inactiveClass }}"
                >
                    Logbook Harian
                </a>

                {{-- SARAN & MASUKAN --}}
                <a
                    href="{{ route('saran') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('saran', 'saran.*') ? $activeClass : $inactiveClass }}"
                >
                    Saran &amp; Masukan
                </a>

            </nav>

        {{-- MENU MENTOR --}}
        @elseif ($isMentor)

            <p class="mb-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-500">
                Menu Mentor
            </p>

            <nav class="space-y-2">

                <a
                    href="{{ route('mentor.dashboard') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.dashboard') ? $activeClass : $inactiveClass }}"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('mentor.interns') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.interns') ? $activeClass : $inactiveClass }}"
                >
                    Mahasiswa Bimbingan
                </a>

                <a
                    href="{{ route('mentor.logbook') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.logbook*') ? $activeClass : $inactiveClass }}"
                >
                    Validasi Logbook
                    @if ($mentorPending['logbook'] > 0)
                        <span class="{{ $badgeClass }}" title="Logbook menunggu validasi">{{ $mentorPending['logbook'] }}</span>
                    @endif
                </a>

                <a
                    href="{{ route('mentor.leave-requests') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.leave-requests*') ? $activeClass : $inactiveClass }}"
                >
                    Pengajuan Izin / Sakit
                    @if ($mentorPending['leave'] > 0)
                        <span class="{{ $badgeClass }}" title="Pengajuan menunggu persetujuan">{{ $mentorPending['leave'] }}</span>
                    @endif
                </a>

                <a
                    href="{{ route('mentor.attendance') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.attendance') ? $activeClass : $inactiveClass }}"
                >
                    Data Kehadiran
                </a>

                <a
                    href="{{ route('mentor.assessment') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.assessment') ? $activeClass : $inactiveClass }}"
                >
                    Penilaian Magang
                </a>

            </nav>

        {{-- MENU ADMIN --}}
        @elseif ($isAdmin)

            <p class="mb-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-500">
                Menu Administrator
            </p>

            {{-- Satu sumber menu admin (sama dengan layouts.admin-layout) --}}
            @include('partials.admin-menu')

        @else

            <p class="rounded-xl bg-amber-500/10 p-4 text-sm text-amber-200">
                Role akun belum dikenali. Silakan hubungi administrator.
            </p>

        @endif
    </div>

    {{-- LOGOUT --}}
    <div class="mt-8 border-t border-white/10 pt-5">

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button
                type="submit"
                class="flex w-full items-center justify-center rounded-xl bg-red-500/10 py-3 font-medium text-red-400 transition hover:bg-red-500 hover:text-white"
            >
                Logout
            </button>
        </form>

    </div>

</aside>

<script>
    (function () {
        const sidebar = document.getElementById('darin-sidebar');
        const latar = document.querySelector('[data-sidebar-latar]');
        const tombol = document.querySelector('[data-sidebar-buka]');

        if (!sidebar || !latar || !tombol) {
            return;
        }

        function buka() {
            sidebar.classList.remove('-translate-x-full');
            latar.classList.remove('hidden');
            tombol.setAttribute('aria-expanded', 'true');
        }

        function tutup() {
            sidebar.classList.add('-translate-x-full');
            latar.classList.add('hidden');
            tombol.setAttribute('aria-expanded', 'false');
        }

        tombol.addEventListener('click', buka);
        latar.addEventListener('click', tutup);
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                tutup();
            }
        });

        // Tutup otomatis setelah memilih menu (di HP).
        sidebar.querySelectorAll('a').forEach(function (tautan) {
            tautan.addEventListener('click', tutup);
        });
    })();
</script>
