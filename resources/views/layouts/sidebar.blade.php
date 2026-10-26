{{-- resources/views/layouts/sidebar.blade.php --}}

@php
    $currentUser = Auth::user();
    $role = strtolower(trim((string) ($currentUser->role ?? '')));

    $isMentor = $role === 'mentor';
    $isAdmin = in_array($role, ['admin', 'hr'], true);
    $isStudent = $role === 'magang';

    $menuItemClass = 'flex items-center gap-3 rounded-xl px-4 py-3 transition';
    $activeClass = 'bg-[#ffc107] font-bold text-black';
    $inactiveClass = 'text-gray-300 hover:bg-white/5';
@endphp

<aside class="flex w-72 shrink-0 flex-col justify-between bg-[#191712] p-6 text-white shadow-xl">

    <div>

        {{-- BRAND --}}
        <a
            href="{{ $isMentor ? route('mentor.dashboard') : ($isAdmin ? route('admin.dashboard') : ($isStudent ? route('dashboard.magang') : route('login')) ) }}"
            class="mb-8 flex items-center gap-3"
        >
            <div class="flex items-center justify-center rounded-xl bg-[#ffc107] p-2 font-bold text-black">
                ⚡
            </div>

            <span class="text-xl font-extrabold tracking-wider">
                DARIN
            </span>
        </a>

        {{-- PROFIL PENGGUNA --}}
        <a
            href="{{ route('profil') }}"
            class="group mb-8 flex flex-col items-center rounded-2xl border border-white/5 bg-[#24211b] p-4 text-center transition hover:bg-[#2c2822]"
        >
            <span class="mb-3 rounded-full bg-[#ffc107]/10 px-3 py-1 text-[13px] font-bold text-[#ffc107] transition group-hover:bg-[#ffc107] group-hover:text-black">
                PROFIL
            </span>

            <div class="relative mb-3 flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-2 border-[#ffc107] bg-gray-700 p-1 shadow-md transition group-hover:scale-105">

                @if ($currentUser && $currentUser->photo)
                    <img
                        src="{{ asset('storage/' . $currentUser->photo) }}"
                        alt="Foto {{ $currentUser->name }}"
                        class="h-full w-full rounded-full object-cover"
                    >
                @else
                    <span class="text-3xl">👤</span>
                @endif

            </div>

            <h3 class="text-base font-bold transition group-hover:text-[#ffc107]">
                {{ $currentUser->name ?? 'Pengguna' }}
            </h3>

            <span class="mb-2 mt-1 rounded-full bg-[#ffc107]/20 px-2 py-0.5 text-[10px] font-bold text-[#ffc107]">
                {{ strtoupper($role ?: 'TIDAK DIKENAL') }}
            </span>

            <span class="rounded-md bg-black/40 px-3 py-1 text-xs text-gray-400">
                {{ $currentUser->university ?? $currentUser->position ?? 'Pengguna DARIN' }}
            </span>
        </a>

        {{-- NAVIGASI --}}
        <nav class="space-y-2">

            {{-- MENU MAHASISWA --}}
            @if ($isStudent)

                <p class="mb-2 px-4 text-xs font-bold uppercase tracking-wider text-gray-500">
                    Menu Mahasiswa
                </p>

                <a
                    href="{{ route('dashboard.magang') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('dashboard.magang') ? $activeClass : $inactiveClass }}"
                >
                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('absen') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('absen', 'absen.store', 'absen.checkout') ? $activeClass : $inactiveClass }}"
                >
                    <span>Mulai Absen</span>
                </a>

                <a
                    href="{{ route('riwayat.absen') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('riwayat.absen', 'riwayat.export-*') ? $activeClass : $inactiveClass }}"
                >
                    <span>Riwayat Absensi</span>
                </a>

                <a
                    href="{{ route('logbook') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('logbook', 'logbook.*') ? $activeClass : $inactiveClass }}"
                >
                    <span>Logbook Harian</span>
                </a>

                <a
                    href="{{ route('izin.create') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('izin.create', 'izin.store') ? $activeClass : $inactiveClass }}"
                >
                    <span>Pengajuan Izin</span>
                </a>

                <a
                    href="{{ route('izin.history') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('izin.history', 'izin.download') ? $activeClass : $inactiveClass }}"
                >
                    <span>Riwayat Izin</span>
                </a>

            {{-- MENU MENTOR --}}
            @elseif ($isMentor)

                <p class="mb-2 px-4 text-xs font-bold uppercase tracking-wider text-gray-500">
                    Menu Mentor
                </p>

                <a
                    href="{{ route('mentor.dashboard') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.dashboard') ? $activeClass : $inactiveClass }}"
                >
                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('mentor.interns') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.interns') ? $activeClass : $inactiveClass }}"
                >
                    <span>Mahasiswa Bimbingan</span>
                </a>

                <a
                    href="{{ route('mentor.logbook') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.logbook*') ? $activeClass : $inactiveClass }}"
                >
                    <span>Review Logbook</span>
                </a>

                <a
                    href="{{ route('mentor.leave-requests') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.leave-requests*') ? $activeClass : $inactiveClass }}"
                >
                    <span>Persetujuan Izin</span>
                </a>

                <a
                    href="{{ route('mentor.attendance') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.attendance') ? $activeClass : $inactiveClass }}"
                >
                    <span>Rekap Absensi</span>
                </a>

                <a
                    href="{{ route('mentor.assessment') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('mentor.assessment') ? $activeClass : $inactiveClass }}"
                >
                    <span>Penilaian</span>
                </a>

            {{-- MENU HR / ADMIN --}}
            @elseif ($isAdmin)

                <p class="mb-2 px-4 text-xs font-bold uppercase tracking-wider text-gray-500">
                    Menu Administrasi
                </p>

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}"
                >
                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('admin.users') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('admin.users*') ? $activeClass : $inactiveClass }}"
                >
                    <span>Manajemen Pengguna</span>
                </a>

                <a
                    href="{{ route('admin.divisions') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('admin.divisions*') ? $activeClass : $inactiveClass }}"
                >
                    <span>Manajemen Divisi</span>
                </a>

                <a
                    href="{{ route('admin.attendance') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('admin.attendance*') ? $activeClass : $inactiveClass }}"
                >
                    <span>Rekap Absensi</span>
                </a>

                <a
                    href="{{ route('admin.settings') }}"
                    class="{{ $menuItemClass }} {{ request()->routeIs('admin.settings') ? $activeClass : $inactiveClass }}"
                >
                    <span>Pengaturan</span>
                </a>

            @else

                <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-200">
                    Role akun belum dikenali. Silakan hubungi administrator.
                </div>

            @endif

        </nav>

    </div>

    {{-- LOGOUT --}}
    <div class="mt-8 border-t border-white/10 pt-5">

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-500/10 py-3 font-medium text-red-400 transition hover:bg-red-500 hover:text-white"
            >
                <span>Logout</span>
            </button>
        </form>

    </div>

</aside>
