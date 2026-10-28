
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lokasi Kerja - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .form-input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            color: #191712;
            background: #fff;
        }

        .form-input:focus {
            outline: none;
            border-color: #ffc107;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, .15);
        }

        .field-label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        [hidden] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 text-gray-800">

    {{-- HEADER --}}
    <header class="bg-[#191712] text-white shadow-lg">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#ffc107] text-xl text-[#191712]">
                    ⚡
                </span>

                <span>
                    <span class="block text-lg font-extrabold tracking-wide">
                        DARIN<span class="text-[#ffc107]">.</span>
                    </span>
                    <span class="block text-[10px] uppercase tracking-widest text-gray-400">
                        Admin / HR Portal
                    </span>
                </span>
            </a>

            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}"
                   class="rounded-lg px-3 py-2 text-gray-300 hover:bg-white/10 hover:text-white">
                    Dashboard
                </a>

                <a href="{{ route('admin.users') }}"
                   class="rounded-lg px-3 py-2 text-gray-300 hover:bg-white/10 hover:text-white">
                    Pengguna
                </a>

                <a href="{{ route('admin.divisions') }}"
                   class="rounded-lg px-3 py-2 text-gray-300 hover:bg-white/10 hover:text-white">
                    Divisi
                </a>

                <a href="{{ route('admin.locations') }}"
                   aria-current="page"
                   class="rounded-lg bg-[#ffc107] px-3 py-2 font-extrabold text-[#191712]">
                    Lokasi
                </a>

                <a href="{{ route('admin.attendance') }}"
                   class="rounded-lg px-3 py-2 text-gray-300 hover:bg-white/10 hover:text-white">
                    Absensi
                </a>

                <a href="{{ route('admin.settings') }}"
                   class="rounded-lg px-3 py-2 text-gray-300 hover:bg-white/10 hover:text-white">
                    Pengaturan
                </a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- JUDUL --}}
        <div class="mb-7 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="mb-2 text-xs font-extrabold uppercase tracking-widest text-amber-700">
                    Pengelolaan Lokasi
                </p>

                <h1 class="text-2xl font-extrabold text-gray-900 sm:text-3xl">
                    Lokasi Kerja
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">
                    Kelola titik koordinat kantor atau tempat kerja mahasiswa
                    yang digunakan untuk validasi radius absensi DARIN.
                </p>
            </div>

            <a href="{{ route('admin.users') }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-xs font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                Kelola Penempatan Mahasiswa →
            </a>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div role="status"
                 class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div role="alert"
                 class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">
                {{ session('error') }}
            </div>
        @endif

        {{-- ERROR VALIDASI --}}
        @if ($errors->any())
            <div role="alert"
                 class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="mb-2 font-bold">Periksa kembali data yang dimasukkan:</p>

                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- KARTU RINGKASAN --}}
        <div class="mb-7 grid grid-cols-1 gap-4 sm:grid-cols-3">

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500">
                            Total Lokasi
                        </p>
                        <p class="mt-2 text-3xl font-extrabold text-gray-900">
                            {{ $totalLocations }}
                        </p>
                    </div>

                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-2xl">
                        📍
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500">
                            Lokasi Aktif
                        </p>
                        <p class="mt-2 text-3xl font-extrabold text-green-700">
                            {{ $activeLocations }}
                        </p>
                    </div>

                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-2xl">
                        ✓
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500">
                            Mahasiswa Ditempatkan
                        </p>
                        <p class="mt-2 text-3xl font-extrabold text-blue-700">
                            {{ $assignedStudents }}
                        </p>
                    </div>

                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-2xl">
                        👥
                    </span>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">

            {{-- FORM TAMBAH LOKASI --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6 lg:col-span-1">

                <div class="mb-5">
                    <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-xl">
                        📍
                    </div>

                    <h2 class="text-lg font-extrabold text-gray-900">
                        Tambah Lokasi
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Masukkan titik koordinat dan radius yang diizinkan untuk absensi.
                    </p>
                </div>

                <form action="{{ route('admin.locations.store') }}"
                      method="POST"
                      class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="field-label">
                            Nama Lokasi
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            maxlength="120"
                            required
                            class="form-input"
                            placeholder="Contoh: Kantor Pusat ESDM"
                        >
                    </div>

                    <div>
                        <label for="address" class="field-label">
                            Alamat
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            rows="2"
                            maxlength="255"
                            class="form-input"
                            placeholder="Alamat lengkap lokasi"
                        >{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label for="latitude" class="field-label">
                            Latitude
                        </label>

                        <input
                            type="number"
                            id="latitude"
                            name="latitude"
                            value="{{ old('latitude') }}"
                            min="-90"
                            max="90"
                            step="any"
                            required
                            class="form-input"
                            placeholder="-6.200000"
                        >
                    </div>

                    <div>
                        <label for="longitude" class="field-label">
                            Longitude
                        </label>

                        <input
                            type="number"
                            id="longitude"
                            name="longitude"
                            value="{{ old('longitude') }}"
                            min="-180"
                            max="180"
                            step="any"
                            required
                            class="form-input"
                            placeholder="106.816666"
                        >
                    </div>

                    <div>
                        <label for="radius_meters" class="field-label">
                            Radius Absensi (meter)
                        </label>

                        <input
                            type="number"
                            id="radius_meters"
                            name="radius_meters"
                            value="{{ old('radius_meters', 100) }}"
                            min="10"
                            max="5000"
                            step="1"
                            required
                            class="form-input"
                        >

                        <p class="mt-1.5 text-[11px] leading-5 text-gray-400">
                            Minimal 10 meter, maksimal 5.000 meter.
                        </p>
                    </div>

                    <label class="flex cursor-pointer items-center gap-3 rounded-xl bg-gray-50 p-3">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', '1') == '1')
                            class="h-4 w-4 accent-yellow-500"
                        >

                        <span>
                            <span class="block text-xs font-bold text-gray-800">
                                Aktifkan lokasi
                            </span>
                            <span class="mt-1 block text-[11px] text-gray-500">
                                Lokasi ini siap dipilih untuk penempatan mahasiswa.
                            </span>
                        </span>
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-[#ffc107] px-4 py-3 text-sm font-extrabold text-[#191712] transition hover:bg-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-200"
                    >
                        + Simpan Lokasi
                    </button>

                </form>

                <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50 p-3">
                    <p class="text-xs font-bold text-blue-900">
                        Tips koordinat
                    </p>

                    <p class="mt-1 text-[11px] leading-5 text-blue-800">
                        Ambil latitude dan longitude dari titik kantor yang tepat.
                        Pastikan radius sesuai kebijakan absensi di lokasi tersebut.
                    </p>
                </div>

            </section>

            {{-- DAFTAR LOKASI --}}
            <section class="space-y-4 lg:col-span-2">

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">
                            Daftar Lokasi Terdaftar
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Kelola koordinat, radius, dan status lokasi.
                        </p>
                    </div>

                    <span class="rounded-full bg-gray-200 px-3 py-1.5 text-xs font-bold text-gray-600">
                        {{ $locations->total() }} lokasi
                    </span>
                </div>

                @forelse ($locations as $location)

                    <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

                        <div class="p-5 sm:p-6">

                            <div class="flex flex-wrap items-start justify-between gap-3">

                                <div class="flex min-w-0 items-start gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-xl">
                                        📍
                                    </span>

                                    <div class="min-w-0">
                                        <h3 class="break-words text-base font-extrabold text-gray-900">
                                            {{ $location->name }}
                                        </h3>

                                        <p class="mt-1 break-words text-xs leading-5 text-gray-500">
                                            {{ $location->address ?: 'Alamat belum diisi' }}
                                        </p>
                                    </div>
                                </div>

                                @if ($location->is_active)
                                    <span class="rounded-full bg-green-100 px-3 py-1.5 text-[11px] font-bold text-green-700">
                                        ● Aktif
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1.5 text-[11px] font-bold text-gray-500">
                                        ● Nonaktif
                                    </span>
                                @endif

                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                                        Latitude
                                    </p>
                                    <p class="mt-1 break-all text-xs font-bold text-gray-800">
                                        {{ $location->latitude }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                                        Longitude
                                    </p>
                                    <p class="mt-1 break-all text-xs font-bold text-gray-800">
                                        {{ $location->longitude }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-amber-50 p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-amber-700">
                                        Radius Absensi
                                    </p>
                                    <p class="mt-1 text-sm font-extrabold text-gray-900">
                                        {{ $location->radius_meters }} meter
                                    </p>
                                </div>

                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">

                                <p class="text-xs text-gray-500">
                                    Mahasiswa ditempatkan:
                                    <strong class="text-gray-800">
                                        {{ $location->assigned_users_count }}
                                    </strong>
                                </p>

                                <a
                                    href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-xs font-bold text-blue-700 hover:underline"
                                >
                                    Lihat di Google Maps ↗
                                </a>

                            </div>

                            {{-- FORM EDIT LOKASI --}}
                            <details class="mt-5 rounded-xl border border-gray-200">
                                <summary class="cursor-pointer px-4 py-3 text-xs font-extrabold text-gray-700 hover:bg-gray-50">
                                    ✎ Edit Data Lokasi
                                </summary>

                                <div class="border-t border-gray-100 p-4 sm:p-5">

                                    <form
                                        action="{{ route('admin.locations.update', $location) }}"
                                        method="POST"
                                        class="space-y-4"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label for="edit-name-{{ $location->id }}" class="field-label">
                                                Nama Lokasi
                                            </label>

                                            <input
                                                type="text"
                                                id="edit-name-{{ $location->id }}"
                                                name="name"
                                                value="{{ $location->name }}"
                                                maxlength="120"
                                                required
                                                class="form-input"
                                            >
                                        </div>

                                        <div>
                                            <label for="edit-address-{{ $location->id }}" class="field-label">
                                                Alamat
                                            </label>

                                            <textarea
                                                id="edit-address-{{ $location->id }}"
                                                name="address"
                                                rows="2"
                                                maxlength="255"
                                                class="form-input"
                                            >{{ $location->address }}</textarea>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                            <div>
                                                <label for="edit-latitude-{{ $location->id }}" class="field-label">
                                                    Latitude
                                                </label>

                                                <input
                                                    type="number"
                                                    id="edit-latitude-{{ $location->id }}"
                                                    name="latitude"
                                                    value="{{ $location->latitude }}"
                                                    min="-90"
                                                    max="90"
                                                    step="any"
                                                    required
                                                    class="form-input"
                                                >
                                            </div>

                                            <div>
                                                <label for="edit-longitude-{{ $location->id }}" class="field-label">
                                                    Longitude
                                                </label>

                                                <input
                                                    type="number"
                                                    id="edit-longitude-{{ $location->id }}"
                                                    name="longitude"
                                                    value="{{ $location->longitude }}"
                                                    min="-180"
                                                    max="180"
                                                    step="any"
                                                    required
                                                    class="form-input"
                                                >
                                            </div>

                                        </div>

                                        <div>
                                            <label for="edit-radius-{{ $location->id }}" class="field-label">
                                                Radius Absensi (meter)
                                            </label>

                                            <input
                                                type="number"
                                                id="edit-radius-{{ $location->id }}"
                                                name="radius_meters"
                                                value="{{ $location->radius_meters }}"
                                                min="10"
                                                max="5000"
                                                step="1"
                                                required
                                                class="form-input"
                                            >
                                        </div>

                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl bg-gray-50 p-3">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                value="1"
                                                @checked((bool) $location->is_active)
                                                class="h-4 w-4 accent-yellow-500"
                                            >

                                            <span class="text-xs font-bold text-gray-700">
                                                Lokasi aktif
                                            </span>
                                        </label>

                                        <button
                                            type="submit"
                                            class="w-full rounded-xl bg-[#191712] px-4 py-3 text-xs font-bold text-white transition hover:bg-gray-800"
                                        >
                                            Simpan Perubahan
                                        </button>

                                    </form>

                                </div>
                            </details>

                            {{-- HAPUS LOKASI --}}
                            <form
                                action="{{ route('admin.locations.destroy', $location) }}"
                                method="POST"
                                class="mt-3"
                                onsubmit="return confirm('Yakin ingin menghapus lokasi ini? Lokasi yang sudah digunakan tidak dapat dihapus.');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50"
                                >
                                    Hapus Lokasi
                                </button>
                            </form>

                        </div>

                    </article>

                @empty

                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-12 text-center">
                        <div class="mb-3 text-4xl">📍</div>

                        <h3 class="text-base font-extrabold text-gray-900">
                            Belum Ada Lokasi
                        </h3>

                        <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500">
                            Tambahkan lokasi kantor melalui formulir di sebelah kiri.
                            Lokasi yang dibuat akan muncul di sini.
                        </p>
                    </div>

                @endforelse

                {{-- PAGINATION --}}
                @if ($locations->hasPages())
                    <div class="rounded-xl border border-gray-100 bg-white px-4 py-3 shadow-sm">
                        {{ $locations->links() }}
                    </div>
                @endif

            </section>

        </div>

        <footer class="mt-10 border-t border-gray-200 py-5 text-center text-[11px] leading-5 text-gray-400">
            © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
        </footer>

    </main>

</body>
</html>
