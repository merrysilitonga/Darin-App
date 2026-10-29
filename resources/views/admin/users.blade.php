
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Pengguna - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fcfbf9;
        }

        .darin-card {
            border: 1px solid #eceef2;
            box-shadow: 0 2px 5px rgba(15, 23, 42, .035);
        }

        .darin-input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            padding: 10px 12px;
            font-size: 12px;
            color: #191712;
            transition: border-color .2s, box-shadow .2s;
        }

        .darin-input:focus {
            outline: none;
            border-color: #ffc107;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, .15);
        }

        .darin-label {
            display: block;
            margin-bottom: 7px;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        [hidden] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen text-gray-800">

@php
    $totalPengguna = $users->count();

    $totalMahasiswa = $users->where('role', 'magang')->count();

    $totalMentor = $users->where('role', 'mentor')->count();

    $totalPeriode = $users->filter(function ($user) {
        return $user->role === 'magang'
            && $user->start_date
            && $user->end_date;
    })->count();
@endphp

<div class="flex min-h-screen items-stretch">

    {{-- SIDEBAR DARIN BERSAMA --}}
    @include('layouts.sidebar')

    {{-- KONTEN UTAMA --}}
    <main class="min-w-0 flex-1">

        {{-- HEADER --}}
        <header class="flex flex-col justify-between gap-4 border-b border-gray-100 bg-white px-5 py-6 sm:flex-row sm:items-center md:px-8">

            <div>
                <p class="text-xs font-semibold text-gray-500">
                    Administrator / Pengguna
                </p>

                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-gray-900">
                    Manajemen Pengguna
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">
                    Kelola data pengguna, divisi, mentor, lokasi kerja, dan periode magang mahasiswa.
                </p>

                <div class="mt-3 h-1 w-16 rounded-full bg-[#ffc107]"></div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Administrator
                    </p>
                </div>

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 font-bold text-amber-800">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
            </div>

        </header>

        <div class="space-y-6 p-5 md:p-8">

            {{-- NOTIFIKASI BERHASIL --}}
            @if(session('success'))
                <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- NOTIFIKASI ERROR --}}
            @if(session('error'))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ERROR VALIDASI --}}
            @if($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    <p class="font-bold">
                        Data belum berhasil disimpan.
                    </p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- KARTU STATISTIK --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <div class="darin-card rounded-2xl bg-white p-5">
                    <p class="text-xs font-medium text-gray-500">
                        Total Pengguna
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-gray-900">
                        {{ $totalPengguna }}
                    </p>

                    <p class="mt-2 text-xs text-gray-400">
                        Seluruh akun terdaftar
                    </p>
                </div>

                <div class="darin-card rounded-2xl bg-white p-5">
                    <p class="text-xs font-medium text-gray-500">
                        Mahasiswa Magang
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-blue-700">
                        {{ $totalMahasiswa }}
                    </p>

                    <p class="mt-2 text-xs text-gray-400">
                        Akun peserta magang
                    </p>
                </div>

                <div class="darin-card rounded-2xl bg-white p-5">
                    <p class="text-xs font-medium text-gray-500">
                        Mentor
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-purple-700">
                        {{ $totalMentor }}
                    </p>

                    <p class="mt-2 text-xs text-gray-400">
                        Akun pembimbing
                    </p>
                </div>

                <div class="darin-card rounded-2xl bg-white p-5">
                    <p class="text-xs font-medium text-gray-500">
                        Periode Terisi
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-green-700">
                        {{ $totalPeriode }}
                    </p>

                    <p class="mt-2 text-xs text-gray-400">
                        Mahasiswa dengan tanggal mulai dan selesai
                    </p>
                </div>

            </section>

            {{-- INFORMASI PERIODE MAGANG --}}
            <section class="darin-card rounded-2xl bg-white p-5 sm:p-6">
                <div class="flex items-start gap-4">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                        <span class="text-sm font-extrabold text-amber-800">01</span>
                    </div>

                    <div>
                        <h2 class="font-extrabold text-gray-900">
                            Pengaturan Periode Magang
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            Tentukan tanggal mulai dan tanggal selesai magang setiap mahasiswa.
                            Kedua tanggal disimpan sesuai periode yang ditetapkan administrator.
                        </p>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Tanggal selesai tidak boleh lebih awal daripada tanggal mulai.
                        </p>
                    </div>

                </div>
            </section>

            {{-- DAFTAR PENGGUNA --}}
            <section class="darin-card overflow-hidden rounded-2xl bg-white">

                <div class="border-b border-gray-100 px-5 py-5 sm:px-6">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">
                                Daftar Pengguna
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Atur divisi, mentor, lokasi kerja, dan periode magang setiap mahasiswa.
                            </p>
                        </div>

                        <span class="w-fit rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-600">
                            {{ $totalPengguna }} pengguna
                        </span>
                    </div>
                </div>

                @if($users->isNotEmpty())

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1450px] text-left text-sm">

                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-4 font-bold">Pengguna</th>
                                    <th class="px-5 py-4 font-bold">Role</th>
                                    <th class="px-5 py-4 font-bold">Divisi / Penempatan</th>
                                    <th class="px-5 py-4 font-bold">Periode Magang</th>
                                    <th class="px-5 py-4 font-bold">Atur Periode</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @foreach($users as $user)

                                    @php
                                        $roleDapatDivisi = in_array($user->role, ['magang', 'mentor'], true);
                                        $mahasiswaMagang = $user->role === 'magang';
                                    @endphp

                                    <tr class="align-top transition hover:bg-gray-50/70">

                                        {{-- IDENTITAS PENGGUNA --}}
                                        <td class="min-w-[200px] px-5 py-5">
                                            <p class="font-bold text-gray-900">
                                                {{ $user->name }}
                                            </p>

                                            <p class="mt-1 break-all text-xs text-gray-500">
                                                {{ $user->email }}
                                            </p>

                                            @if($user->nim)
                                                <p class="mt-2 text-xs text-gray-500">
                                                    NIM: {{ $user->nim }}
                                                </p>
                                            @endif
                                        </td>

                                        {{-- ROLE --}}
                                        <td class="whitespace-nowrap px-5 py-5">
                                            @if($user->role === 'magang')
                                                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-800">
                                                    Mahasiswa Magang
                                                </span>

                                            @elseif($user->role === 'mentor')
                                                <span class="inline-flex rounded-full bg-purple-100 px-3 py-1.5 text-xs font-bold text-purple-800">
                                                    Mentor
                                                </span>

                                            @elseif(in_array($user->role, ['admin', 'hr'], true))
                                                <span class="inline-flex rounded-full bg-gray-200 px-3 py-1.5 text-xs font-bold text-gray-800">
                                                    Admin / HR
                                                </span>

                                            @else
                                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">
                                                    {{ $user->role }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- DIVISI, MENTOR, DAN LOKASI --}}
                                        <td class="min-w-[300px] px-5 py-5">

                                            <p class="font-semibold text-gray-800">
                                                {{ optional($user->division)->name ?? 'Belum ditetapkan' }}
                                            </p>

                                            @if($mahasiswaMagang)
                                                <p class="mt-2 text-xs text-gray-500">
                                                    Mentor:
                                                    {{ optional($user->mentor)->name ?? 'Belum ditentukan' }}
                                                </p>
                                            @endif

                                            {{-- FORM DIVISI --}}
                                            @if($roleDapatDivisi)

                                                <form
                                                    action="{{ route('admin.users.division', $user->id) }}"
                                                    method="POST"
                                                    class="mt-3 space-y-2"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <label
                                                        for="division_id_{{ $user->id }}"
                                                        class="darin-label"
                                                    >
                                                        {{ $user->role === 'mentor' ? 'Divisi Mentor' : 'Tetapkan Divisi' }}
                                                    </label>

                                                    <select
                                                        id="division_id_{{ $user->id }}"
                                                        name="division_id"
                                                        class="darin-input"
                                                    >
                                                        <option value="">
                                                            Belum ditetapkan
                                                        </option>

                                                        @foreach($divisions as $division)
                                                            <option
                                                                value="{{ $division->id }}"
                                                                @selected((string) $user->division_id === (string) $division->id)
                                                            >
                                                                {{ $division->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <button
                                                        type="submit"
                                                        class="w-full rounded-xl bg-[#ffc107] px-3 py-2.5 text-xs font-extrabold text-black transition hover:bg-amber-400"
                                                    >
                                                        Simpan Divisi
                                                    </button>
                                                </form>

                                            @else

                                                <p class="mt-2 text-xs text-gray-400">
                                                    Divisi tidak diatur dari halaman ini.
                                                </p>

                                            @endif

                                            {{-- FORM MENTOR --}}
                                            @if($mahasiswaMagang)

                                                <form
                                                    action="{{ route('admin.users.mentor', $user->id) }}"
                                                    method="POST"
                                                    class="mt-4 space-y-2 border-t border-gray-100 pt-4"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <label
                                                        for="mentor_id_{{ $user->id }}"
                                                        class="darin-label"
                                                    >
                                                        Tetapkan Mentor
                                                    </label>

                                                    <select
                                                        id="mentor_id_{{ $user->id }}"
                                                        name="mentor_id"
                                                        class="darin-input"
                                                    >
                                                        <option value="">
                                                            Belum ditentukan
                                                        </option>

                                                        @foreach($mentors as $mentor)
                                                            <option
                                                                value="{{ $mentor->id }}"
                                                                @selected((string) $user->mentor_id === (string) $mentor->id)
                                                            >
                                                                {{ $mentor->name }}{{ $mentor->division ? ' — ' . $mentor->division->name : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <button
                                                        type="submit"
                                                        class="w-full rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs font-extrabold text-amber-900 transition hover:bg-amber-100"
                                                    >
                                                        Simpan Mentor
                                                    </button>

                                                    @if($mentors->isEmpty())
                                                        <p class="text-[11px] leading-5 text-amber-700">
                                                            Belum ada akun Mentor. Buat akun Mentor terlebih dahulu.
                                                        </p>
                                                    @else
                                                        <p class="text-[11px] leading-5 text-gray-500">
                                                            Mentor dapat dipilih langsung dan tidak harus dari divisi yang sama.
                                                        </p>
                                                    @endif

                                                </form>

                                                {{-- LOKASI KERJA --}}
                                                <div class="mt-4 border-t border-gray-100 pt-4">

                                                    <p class="text-xs font-bold text-gray-600">
                                                        Lokasi Kerja Saat Ini
                                                    </p>

                                                    <p class="mt-1 text-sm font-bold text-gray-900">
                                                        {{ $user->location?->name ?? 'Belum ditetapkan' }}
                                                    </p>

                                                    @if($user->location && !$user->location->is_active)
                                                        <p class="mt-2 text-xs leading-5 text-red-600">
                                                            Lokasi saat ini nonaktif. Pilih lokasi aktif untuk penempatan berikutnya.
                                                        </p>
                                                    @endif

                                                    <form
                                                        action="{{ route('admin.users.location', $user->id) }}"
                                                        method="POST"
                                                        class="mt-3 space-y-2"
                                                    >
                                                        @csrf
                                                        @method('PUT')

                                                        <label
                                                            for="location_id_{{ $user->id }}"
                                                            class="darin-label"
                                                        >
                                                            Tetapkan Lokasi Kantor
                                                        </label>

                                                        <select
                                                            id="location_id_{{ $user->id }}"
                                                            name="location_id"
                                                            class="darin-input"
                                                        >
                                                            <option value="">
                                                                Belum ditetapkan
                                                            </option>

                                                            @foreach($locations as $lokasiPilihan)
                                                                <option
                                                                    value="{{ $lokasiPilihan->id }}"
                                                                    @selected((string) $user->location_id === (string) $lokasiPilihan->id)
                                                                    @disabled(!$lokasiPilihan->is_active && (int) $user->location_id !== (int) $lokasiPilihan->id)
                                                                >
                                                                    {{ $lokasiPilihan->name }}{{ $lokasiPilihan->is_active ? '' : ' (Nonaktif)' }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <button
                                                            type="submit"
                                                            class="w-full rounded-xl bg-[#ffc107] px-3 py-2.5 text-xs font-extrabold text-black transition hover:bg-amber-400"
                                                        >
                                                            Simpan Lokasi
                                                        </button>

                                                        @if($locations->where('is_active', true)->isEmpty())
                                                            <p class="text-[11px] leading-5 text-amber-700">
                                                                Belum ada lokasi aktif. Tambahkan atau aktifkan lokasi melalui menu Manajemen Lokasi.
                                                            </p>
                                                        @endif

                                                    </form>
                                                </div>

                                            @endif

                                        </td>

                                        {{-- PERIODE TERSIMPAN --}}
                                        <td class="min-w-[200px] px-5 py-5">

                                            @if($mahasiswaMagang)

                                                @if($user->start_date && $user->end_date)

                                                    <div class="rounded-xl bg-gray-50 p-3">
                                                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                                                            Tanggal Mulai
                                                        </p>

                                                        <p class="mt-1 text-sm font-bold text-gray-800">
                                                            {{ $user->start_date->format('d M Y') }}
                                                        </p>

                                                        <div class="my-3 border-t border-gray-200"></div>

                                                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                                                            Tanggal Selesai
                                                        </p>

                                                        <p class="mt-1 text-sm font-bold text-gray-800">
                                                            {{ $user->end_date->format('d M Y') }}
                                                        </p>
                                                    </div>

                                                @else

                                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800">
                                                        Belum diatur
                                                    </span>

                                                    <p class="mt-2 text-xs leading-5 text-gray-500">
                                                        Isi tanggal mulai dan tanggal selesai pada formulir di sebelah.
                                                    </p>

                                                @endif

                                            @else

                                                <span class="text-xs text-gray-400">
                                                    Tidak berlaku untuk role ini.
                                                </span>

                                            @endif

                                        </td>

                                        {{-- FORM PERIODE --}}
                                        <td class="min-w-[260px] px-5 py-5">

                                            @if($mahasiswaMagang)

                                                <form
                                                    action="{{ route('admin.users.internship-period', $user->id) }}"
                                                    method="POST"
                                                    class="space-y-4"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <div>
                                                        <label
                                                            for="start_date_{{ $user->id }}"
                                                            class="darin-label"
                                                        >
                                                            Tanggal Mulai
                                                        </label>

                                                        <input
                                                            type="date"
                                                            id="start_date_{{ $user->id }}"
                                                            name="start_date"
                                                            value="{{ $user->start_date ? $user->start_date->format('Y-m-d') : '' }}"
                                                            required
                                                            class="darin-input"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label
                                                            for="end_date_{{ $user->id }}"
                                                            class="darin-label"
                                                        >
                                                            Tanggal Selesai
                                                        </label>

                                                        <input
                                                            type="date"
                                                            id="end_date_{{ $user->id }}"
                                                            name="end_date"
                                                            value="{{ $user->end_date ? $user->end_date->format('Y-m-d') : '' }}"
                                                            min="{{ $user->start_date ? $user->start_date->format('Y-m-d') : '' }}"
                                                            required
                                                            class="darin-input"
                                                        >
                                                    </div>

                                                    <button
                                                        type="submit"
                                                        class="w-full rounded-xl bg-[#ffc107] px-4 py-2.5 text-xs font-extrabold text-black transition hover:bg-amber-400"
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

                    {{-- DATA KOSONG --}}
                    <div class="px-6 py-16 text-center">
                        <h3 class="text-lg font-extrabold text-gray-900">
                            Belum Ada Pengguna
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Data pengguna yang terdaftar akan muncul di halaman ini.
                        </p>
                    </div>

                @endif

                <div class="border-t border-gray-100 bg-gray-50 px-5 py-4 text-xs leading-5 text-gray-500 sm:px-6">
                    Perubahan divisi, penugasan mentor, lokasi kerja, dan periode magang tersimpan setelah formulir terkait berhasil divalidasi.
                </div>

            </section>

            {{-- FOOTER --}}
            <footer class="border-t border-gray-200 pb-4 pt-5 text-center text-xs text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </footer>

        </div>

    </main>

</div>

</body>
</html>
