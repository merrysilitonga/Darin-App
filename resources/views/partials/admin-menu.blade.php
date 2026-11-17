{{--
    Daftar menu Admin/HR — SATU sumber untuk semua halaman admin
    (dipakai layouts.admin-layout dan layouts.sidebar), agar menu selalu sama.
--}}
@php
    $menuKelas = 'flex items-center justify-between gap-3 rounded-xl px-4 py-3 transition';
    $menuAktif = 'bg-[#ffc107] font-bold text-black';
    $menuBiasa = 'text-gray-300 hover:bg-white/5 hover:text-white';

    $adminMenu = [
        ['route' => 'admin.dashboard', 'aktif' => ['admin.dashboard'], 'label' => 'Dashboard Utama'],
        ['route' => 'admin.users', 'aktif' => ['admin.users*', 'admin.interns.*'], 'label' => 'Manajemen Pengguna'],
        ['route' => 'admin.divisions', 'aktif' => ['admin.divisions*'], 'label' => 'Penempatan & Divisi'],
        ['route' => 'admin.locations', 'aktif' => ['admin.locations*'], 'label' => 'Lokasi Absensi'],
        ['route' => 'admin.attendance', 'aktif' => ['admin.attendance*'], 'label' => 'Rekap Absensi & Logbook'],
        ['route' => 'admin.logbooks', 'aktif' => ['admin.logbooks*'], 'label' => 'Monitoring Logbook'],
        ['route' => 'admin.feedbacks', 'aktif' => ['admin.feedbacks*'], 'label' => 'Saran & Masukan', 'badge' => true],
        ['route' => 'admin.holidays', 'aktif' => ['admin.holidays*'], 'label' => 'Kalender Hari Libur'],
        ['route' => 'admin.settings', 'aktif' => ['admin.settings*'], 'label' => 'Pengaturan Sistem'],
    ];

    $jumlahSaranBaru = \App\Models\Feedback::where('status', \App\Models\Feedback::STATUS_NEW)->count();
    $khususAdmin = strtolower((string) auth()->user()?->role) === 'admin';
@endphp

<nav class="space-y-1.5 text-sm font-semibold">
    @foreach ($adminMenu as $menu)
        @continue(!Route::has($menu['route']))

        <a href="{{ route($menu['route']) }}"
           class="{{ $menuKelas }} {{ request()->routeIs(...$menu['aktif']) ? $menuAktif : $menuBiasa }}">
            <span>{{ $menu['label'] }}</span>

            @if (!empty($menu['badge']) && $jumlahSaranBaru > 0)
                <span class="rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $jumlahSaranBaru }}</span>
            @endif
        </a>
    @endforeach

    {{-- RESET DATA (khusus role admin) --}}
    @if ($khususAdmin && Route::has('admin.reset-data.view'))
        <a href="{{ route('admin.reset-data.view') }}"
           class="{{ $menuKelas }} {{ request()->routeIs('admin.reset-data*') ? $menuAktif : 'text-red-300 hover:bg-red-500/10 hover:text-red-200' }}">
            <span>Reset Data</span>
        </a>
    @endif
</nav>
