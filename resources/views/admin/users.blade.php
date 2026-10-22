<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Pengguna - DARIN Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8f9fb;
        }

        .sidebar {
            background: #101010;
        }

        .gold {
            color: #d8ad52;
        }

        .gold-bg {
            background: #d8ad52;
        }

        .nav-link {
            transition: all .2s ease;
        }

        .nav-link:hover,
        .nav-active {
            background: rgba(216, 173, 82, .13);
            color: #e7bf68;
        }

        .card {
            border: 1px solid #eceef2;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
        }
    </style>
</head>

<body class="min-h-screen text-gray-800">

<div class="min-h-screen md:flex">

    {{-- SIDEBAR --}}
    <aside class="sidebar w-full shrink-0 text-white md:min-h-screen md:w-64">

        <div class="flex items-center gap-3 border-b border-white/10 px-6 py-6">
            <div class="gold-bg flex h-11 w-11 items-center justify-center rounded-xl text-lg font-extrabold text-black">
                D
            </div>

            <div>
                <h1 class="text-xl font-extrabold tracking-wide">
                    DARIN<span class="gold">.</span>
                </h1>
                <p class="mt-1 text-xs text-gray-400">Administrator Panel</p>
            </div>
        </div>

        <div class="border-b border-white/10 px-6 py-5">
            <p class="text-xs uppercase tracking-widest text-gray-500">
                Login sebagai
            </p>

            <p class="mt-2 font-semibold text-white">
                {{ auth()->user()->name }}
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Administrator
            </p>
        </div>

        <nav class="space-y-2 px-4 py-6">

            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-widest text-gray-500">
                Menu Utama
            </p>

            <a href="{{ route('admin.dashboard') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                Dashboard
            </a>

            <a href="{{ route('admin.users') }}"
               class="nav-link nav-active flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold">
                Manajemen Pengguna
            </a>

            <a href="{{ route('admin.divisions') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                Manajemen Divisi
            </a>

            <a href="{{ route('admin.attendance') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                Data Absensi
            </a>

            <a href="{{ route('admin.settings') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                Pengaturan Sistem
            </a>

            <a href="{{ route('profil') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                Profil
            </a>

        </nav>

        <div class="border-t border-white/10 px-4 py-5">
            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit"
                        class="w-full rounded-xl px-4 py-3 text-left text-sm text-red-300 transition hover:bg-red-500/10">Logout
                </button>
            </form>
        </div>

    </aside>


    {{-- KONTEN --}}
    <main class="min-w-0 flex-1">

        {{-- HEADER --}}
        <header class="flex flex-col justify-between gap-4 border-b border-gray-200 bg-white px-5 py-5 sm:flex-row sm:items-center sm:px-8">
            <div>
                <p class="text-sm text-gray-500">
                    Administrator / Pengguna
                </p>

                <h2 class="mt-1 text-2xl font-extrabold text-gray-900">
                    Manajemen Pengguna
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    Kelola data pengguna, divisi, penugasan mentor, dan periode magang mahasiswa.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 font-bold text-amber-800">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>

                <div>
                    <p class="text-sm font-bold text-gray-800">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-gray-500">Administrator</p>
                </div>
            </div>
        </header>


        <div class="space-y-6 p-5 sm:p-8">

            {{-- NOTIFIKASI BERHASIL --}}
            @if(session('success'))
                <div role="status"
                     class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800">
                    ✓ {{ session('success') }}
                </div>
            @endif

            {{-- NOTIFIKASI ERROR --}}
            @if(session('error'))
                <div role="alert"
                     class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert"
                     class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    <p class="font-bold">Data belum berhasil disimpan.</p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- RINGKASAN --}}
            @php
                $totalPengguna = $users->count();

                $totalMahasiswa = $users->whereIn('role', [
                    'magang',
                    'intern',
                ])->count();

                $totalMentor = $users->where('role', 'mentor')->count();

                $totalPeriode = $users->filter(function ($user) {
                    return in_array($user->role, ['magang', 'intern'], true)
                        && $user->start_date
                        && $user->end_date;
                })->count();
            @endphp

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm font-medium text-gray-500">Total Pengguna</p>
                    <p class="mt-4 text-3xl font-extrabold text-gray-900">
                        {{ $totalPengguna }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Seluruh akun terdaftar</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm font-medium text-gray-500">Mahasiswa Magang</p>
                    <p class="mt-4 text-3xl font-extrabold text-blue-600">
                        {{ $totalMahasiswa }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Akun peserta magang</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm font-medium text-gray-500">Mentor</p>
                    <p class="mt-4 text-3xl font-extrabold text-purple-600">
                        {{ $totalMentor }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Akun pembimbing</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm font-medium text-gray-500">Periode Terisi</p>
                    <p class="mt-4 text-3xl font-extrabold text-green-600">
                        {{ $totalPeriode }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Mahasiswa dengan tanggal mulai dan selesai</p>
                </div>

            </section>


            {{-- INFORMASI PERIODE --}}
            <section class="card rounded-2xl bg-white p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-xl text-amber-800">
                        ▦
                    </div>

                    <div>
                        <h3 class="font-extrabold text-gray-900">
                            Pengaturan Periode Magang
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            Tetapkan tanggal mulai dan durasi magang setiap mahasiswa.
                            Sistem menghitung tanggal selesai secara otomatis.
                        </p>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Pilihan durasi yang tersedia: 3, 4, 5, atau 6 bulan.
                        </p>
                    </div>
                </div>
            </section>


            {{-- DAFTAR PENGGUNA --}}
            <section class="card overflow-hidden rounded-2xl bg-white">

                <div class="border-b border-gray-100 px-5 py-5 sm:px-6">
                    <h3 class="text-lg font-extrabold text-gray-900">
                        Daftar Pengguna
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Atur divisi, tetapkan mentor secara langsung untuk setiap mahasiswa, dan kelola periode magang dari halaman ini.
                    </p>
                </div>


                @if($users->isNotEmpty())

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1350px] text-left text-sm">

                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-4 font-bold">Pengguna</th>
                                    <th class="px-5 py-4 font-bold">Role</th>
                                    <th class="px-5 py-4 font-bold">Divisi / Mentor</th>
                                    <th class="px-5 py-4 font-bold">Periode Magang</th>
                                    <th class="px-5 py-4 font-bold">Atur Periode</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @foreach($users as $user)

                                    <tr class="align-top transition hover:bg-gray-50/80">

                                        {{-- IDENTITAS --}}
                                        <td class="px-5 py-5">
                                            <div class="font-bold text-gray-900">
                                                {{ $user->name }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $user->email }}
                                            </div>

                                            @if($user->nim)
                                                <div class="mt-1 text-xs text-gray-500">
                                                    NIM: {{ $user->nim }}
                                                </div>
                                            @endif
                                        </td>


                                        {{-- ROLE --}}
                                        <td class="whitespace-nowrap px-5 py-5">
                                            @if(in_array($user->role, ['magang', 'intern'], true))
                                                <span class="rounded-full bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-800">
                                                    Mahasiswa Magang
                                                </span>
                                            @elseif($user->role === 'mentor')
                                                <span class="rounded-full bg-purple-100 px-3 py-1.5 text-xs font-bold text-purple-800">
                                                    Mentor
                                                </span>
                                            @elseif(in_array($user->role, ['admin', 'hr'], true))
                                                <span class="rounded-full bg-gray-200 px-3 py-1.5 text-xs font-bold text-gray-800">
                                                    Admin / HR
                                                </span>
                                            @else
                                                <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">
                                                    {{ $user->role }}
                                                </span>
                                            @endif
                                        </td>


                                        {{-- DIVISI DAN PENUGASAN MENTOR LANGSUNG --}}
                                        <td class="min-w-[300px] px-5 py-5">
                                            @php
                                                $roleDapatDivisi = in_array($user->role, ['magang', 'intern', 'mentor'], true);
                                                $mahasiswaMagang = in_array($user->role, ['magang', 'intern'], true);
                                            @endphp

                                            <p class="font-semibold text-gray-800">
                                                {{ optional($user->division)->name ?? 'Belum ditetapkan' }}
                                            </p>

                                            @if($mahasiswaMagang)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Mentor: {{ optional($user->mentor)->name ?? 'Belum ditentukan' }}
                                                </p>
                                            @endif

                                            @if($roleDapatDivisi)
                                                <form action="{{ route('admin.users.division', $user->id) }}" method="POST" class="mt-3 space-y-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <label for="division_id_{{ $user->id }}" class="block text-xs font-semibold text-gray-600">
                                                        {{ $user->role === 'mentor' ? 'Divisi mentor' : 'Tetapkan divisi' }}
                                                    </label>
                                                    <select
                                                        id="division_id_{{ $user->id }}"
                                                        name="division_id"
                                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                                                    >
                                                        <option value="">Belum ditetapkan</option>
                                                        @foreach($divisions as $division)
                                                            <option value="{{ $division->id }}" {{ (string) $user->division_id === (string) $division->id ? 'selected' : '' }}>
                                                                {{ $division->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="gold-bg w-full rounded-lg px-3 py-2 text-xs font-extrabold text-black transition hover:bg-amber-400">
                                                        Simpan Divisi
                                                    </button>
                                                </form>
                                            @else
                                                <p class="mt-1 text-xs text-gray-400">Divisi tidak diatur dari halaman ini.</p>
                                            @endif

                                            @if($mahasiswaMagang)
                                                <form action="{{ route('admin.users.mentor', $user->id) }}" method="POST" class="mt-4 space-y-2 border-t border-gray-100 pt-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <label for="mentor_id_{{ $user->id }}" class="block text-xs font-semibold text-gray-600">
                                                        Tetapkan mentor
                                                    </label>
                                                    <select
                                                        id="mentor_id_{{ $user->id }}"
                                                        name="mentor_id"
                                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                                                    >
                                                        <option value="">Belum ditentukan</option>
                                                        @foreach($mentors as $mentor)
                                                            <option value="{{ $mentor->id }}" {{ (string) $user->mentor_id === (string) $mentor->id ? 'selected' : '' }}>
                                                                {{ $mentor->name }}{{ $mentor->division ? ' — ' . $mentor->division->name : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="w-full rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-extrabold text-amber-900 transition hover:bg-amber-100">
                                                        Simpan Mentor
                                                    </button>
                                                    @if($mentors->isEmpty())
                                                        <p class="text-[11px] leading-4 text-amber-700">Belum ada akun ber-role mentor. Buat akun mentor terlebih dahulu.</p>
                                                    @else
                                                        <p class="text-[11px] leading-4 text-gray-500">Mentor dapat dipilih langsung dan tidak harus dari divisi yang sama.</p>
                                                    @endif
                                                </form>
                                            @endif
                                        </td>


                                        {{-- PERIODE TERSIMPAN --}}
                                        <td class="min-w-[210px] px-5 py-5">

                                            @if(in_array($user->role, ['magang', 'intern'], true))

                                                @if($user->start_date && $user->end_date)

                                                    <p class="font-semibold text-gray-800">
                                                        {{ $user->start_date->format('d M Y') }}
                                                    </p>

                                                    <p class="my-1 text-xs text-gray-400">
                                                        sampai dengan
                                                    </p>

                                                    <p class="font-semibold text-gray-800">
                                                        {{ $user->end_date->format('d M Y') }}
                                                    </p>

                                                    @php
                                                        $durasiTersimpan = (int) \Illuminate\Support\Carbon::parse($user->start_date)
                                                            ->diffInMonths(
                                                                \Illuminate\Support\Carbon::parse($user->end_date)->addDay()
                                                            );

                                                        $durasiValid = in_array($durasiTersimpan, [3, 4, 5, 6], true);
                                                    @endphp

                                                    <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $durasiValid ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                        {{ $durasiValid ? $durasiTersimpan . ' bulan' : 'Periksa durasi' }}
                                                    </span>

                                                @else

                                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800">
                                                        Belum diatur
                                                    </span>

                                                    <p class="mt-2 text-xs leading-5 text-gray-500">
                                                        Tetapkan tanggal mulai dan durasi di formulir sebelah.
                                                    </p>

                                                @endif

                                            @else

                                                <span class="text-xs text-gray-400">
                                                    Tidak berlaku untuk role ini.
                                                </span>

                                            @endif

                                        </td>


                                        {{-- FORM PERIODE --}}
                                        <td class="min-w-[300px] px-5 py-5">

                                            @if(in_array($user->role, ['magang', 'intern'], true))

                                                @php
                                                    $durasiTerpilih = null;

                                                    if ($user->start_date && $user->end_date) {
                                                        $durasiSementara = (int) \Illuminate\Support\Carbon::parse($user->start_date)
                                                            ->diffInMonths(
                                                                \Illuminate\Support\Carbon::parse($user->end_date)->addDay()
                                                            );

                                                        if (in_array($durasiSementara, [3, 4, 5, 6], true)) {
                                                            $durasiTerpilih = $durasiSementara;
                                                        }
                                                    }
                                                @endphp

                                                <form
                                                    action="{{ route('admin.users.internship-period', $user->id) }}"
                                                    method="POST"
                                                    class="space-y-3"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <div>
                                                        <label
                                                            for="start_date_{{ $user->id }}"
                                                            class="mb-1.5 block text-xs font-bold text-gray-700"
                                                        >
                                                            Tanggal Mulai
                                                        </label>

                                                        <input
                                                            type="date"
                                                            id="start_date_{{ $user->id }}"
                                                            name="start_date"
                                                            value="{{ $user->start_date ? $user->start_date->format('Y-m-d') : '' }}"
                                                            required
                                                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="duration_months_{{ $user->id }}"
                                                            class="mb-1.5 block text-xs font-bold text-gray-700"
                                                        >
                                                            Durasi Magang
                                                        </label>

                                                        <select
                                                            id="duration_months_{{ $user->id }}"
                                                            name="duration_months"
                                                            required
                                                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                                                        >
                                                            <option value="">Pilih durasi</option>

                                                            <option value="3" {{ $durasiTerpilih === 3 ? 'selected' : '' }}>
                                                                3 Bulan
                                                            </option>

                                                            <option value="4" {{ $durasiTerpilih === 4 ? 'selected' : '' }}>
                                                                4 Bulan
                                                            </option>

                                                            <option value="5" {{ $durasiTerpilih === 5 ? 'selected' : '' }}>
                                                                5 Bulan
                                                            </option>

                                                            <option value="6" {{ $durasiTerpilih === 6 ? 'selected' : '' }}>
                                                                6 Bulan
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <button
                                                        type="submit"
                                                        class="gold-bg w-full rounded-lg px-4 py-2.5 text-sm font-extrabold text-black transition hover:bg-amber-400"
                                                    >
                                                        Simpan Periode
                                                    </button>
                                                </form>

                                            @else

                                                <p class="text-xs leading-5 text-gray-400">
                                                    Pengaturan periode hanya tersedia untuk mahasiswa magang.
                                                </p>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>
                    </div>

                @else

                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-3xl">
                            ♙
                        </div>

                        <h4 class="mt-5 font-extrabold text-gray-900">
                            Belum Ada Pengguna
                        </h4>

                        <p class="mt-2 text-sm text-gray-500">
                            Data pengguna yang terdaftar akan muncul di halaman ini.
                        </p>
                    </div>

                @endif


                <div class="border-t border-gray-100 bg-gray-50 px-5 py-4 text-xs leading-5 text-gray-500 sm:px-6">
                    Perubahan divisi, penugasan mentor, dan periode magang tersimpan setelah formulir terkait berhasil divalidasi.
                </div>

            </section>


            {{-- FOOTER --}}
            <footer class="pb-4 text-center text-xs text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </footer>

        </div>
    </main>

</div>

</body>
</html>
