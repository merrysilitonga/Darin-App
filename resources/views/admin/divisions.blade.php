<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Penempatan & Divisi - DARIN</title>

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
            padding: 11px 13px;
            font-size: 13px;
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
    $totalDivisions = $divisions->count();
    $totalStudents = $divisions->sum('interns_count');
    $totalMentors = \App\Models\User::where('role', 'mentor')->count();
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
                    Administrator / Penempatan
                </p>

                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-gray-900">
                    Penempatan & Manajemen Divisi
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">
                    Kelola divisi dan pantau penempatan mahasiswa serta mentor.
                </p>

                <div class="mt-3 h-1 w-16 rounded-full bg-[#ffc107]"></div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">
                        {{ auth()->user()->name ?? 'Administrator' }}
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

            {{-- VALIDASI --}}
            @if($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    <p class="font-bold">Data belum berhasil disimpan.</p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- KARTU STATISTIK --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                <div class="darin-card rounded-2xl bg-white p-5">
                    <p class="text-xs font-medium text-gray-500">
                        Total Divisi
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-gray-900">
                        {{ $totalDivisions }}
                    </p>

                    <p class="mt-2 text-xs text-gray-400">
                        Divisi terdaftar di sistem
                    </p>
                </div>

                <div class="darin-card rounded-2xl bg-white p-5">
                    <p class="text-xs font-medium text-gray-500">
                        Mahasiswa Magang
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-blue-700">
                        {{ $totalStudents }}
                    </p>

                    <p class="mt-2 text-xs text-gray-400">
                        Mahasiswa yang ditempatkan pada divisi
                    </p>
                </div>

                <div class="darin-card rounded-2xl bg-white p-5">
                    <p class="text-xs font-medium text-gray-500">
                        Total Mentor
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-purple-700">
                        {{ $totalMentors }}
                    </p>

                    <p class="mt-2 text-xs text-gray-400">
                        Akun dengan role Mentor
                    </p>
                </div>

            </section>

            {{-- JUDUL DAN TOMBOL TAMBAH --}}
            <section class="darin-card flex flex-col justify-between gap-5 rounded-2xl bg-white p-5 sm:flex-row sm:items-center md:p-6">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-900">
                        Daftar Divisi
                    </h2>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">
                        Kelola nama divisi dan lihat ringkasan anggota.
                        Penugasan mentor setiap mahasiswa tetap diatur melalui halaman Manajemen Pengguna.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="openCreateModal()"
                    class="shrink-0 rounded-xl bg-[#ffc107] px-5 py-3 text-sm font-extrabold text-black transition hover:bg-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-200"
                >
                    Tambah Divisi
                </button>
            </section>

            {{-- PENCARIAN --}}
            <section class="darin-card rounded-2xl bg-white p-5">
                <label for="divisionSearch" class="darin-label">
                    Cari Divisi
                </label>

                <input
                    type="search"
                    id="divisionSearch"
                    placeholder="Masukkan nama divisi..."
                    class="darin-input"
                    autocomplete="off"
                >

                <p id="searchEmpty" class="mt-3 hidden text-sm text-amber-700">
                    Divisi yang dicari tidak ditemukan.
                </p>
            </section>

            {{-- DAFTAR DIVISI --}}
            <section>
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">
                            Divisi Terdaftar
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Kelola nama divisi dan lihat ringkasan anggota.
                        </p>
                    </div>

                    <span class="rounded-full bg-amber-100 px-4 py-2 text-xs font-bold text-amber-900">
                        {{ $totalDivisions }} Divisi
                    </span>
                </div>

                @if($divisions->isNotEmpty())

                    <div id="divisionGrid" class="grid grid-cols-1 gap-5 xl:grid-cols-2">

                        @foreach($divisions as $division)

                            @php
                                $divisionMentors = $division->users->where('role', 'mentor');
                                $divisionStudents = $division->users->whereIn('role', ['magang', 'intern']);
                            @endphp

                            <article
                                class="division-card darin-card space-y-5 rounded-2xl bg-white p-5 md:p-6"
                                data-search="{{ mb_strtolower($division->name) }}"
                            >

                                {{-- INFORMASI DIVISI --}}
                                <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-5">

                                    <div class="min-w-0">
                                        <h3 class="break-words text-lg font-extrabold text-gray-900">
                                            {{ $division->name }}
                                        </h3>

                                        <p class="mt-2 text-xs leading-5 text-gray-500">
                                            {{ $division->interns_count }} mahasiswa
                                            <span class="mx-1 text-gray-300">|</span>
                                            {{ $division->mentors_count }} mentor
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        Terdaftar
                                    </span>

                                </div>

                                {{-- MENTOR --}}
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <h4 class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-500">
                                        Mentor pada Divisi Ini
                                    </h4>

                                    @if($divisionMentors->isNotEmpty())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($divisionMentors as $mentor)
                                                <span class="rounded-lg border border-purple-100 bg-purple-50 px-3 py-2 text-xs font-semibold text-purple-800">
                                                    {{ $mentor->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm leading-6 text-gray-500">
                                            Belum ada mentor yang ditempatkan pada divisi ini.
                                        </p>
                                    @endif
                                </div>

                                {{-- MAHASISWA --}}
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                        <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                            Mahasiswa pada Divisi Ini
                                        </h4>

                                        <span class="text-xs font-bold text-blue-700">
                                            {{ $divisionStudents->count() }} orang
                                        </span>
                                    </div>

                                    @if($divisionStudents->isNotEmpty())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($divisionStudents->take(5) as $student)
                                                <span class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700">
                                                    {{ $student->name }}
                                                </span>
                                            @endforeach

                                            @if($divisionStudents->count() > 5)
                                                <span class="rounded-lg bg-amber-100 px-3 py-2 text-xs font-bold text-amber-800">
                                                    +{{ $divisionStudents->count() - 5 }} lainnya
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-sm leading-6 text-gray-500">
                                            Belum ada mahasiswa yang ditempatkan pada divisi ini.
                                        </p>
                                    @endif
                                </div>

                                {{-- AKSI DIVISI --}}
                                <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">

                                    <a
                                        href="{{ route('admin.users') }}"
                                        class="rounded-xl bg-gray-100 px-4 py-2.5 text-xs font-bold text-gray-700 transition hover:bg-gray-200"
                                    >
                                        Kelola Pengguna
                                    </a>

                                    <button
                                        type="button"
                                        data-name="{{ $division->name }}"
                                        data-update-url="{{ route('admin.divisions.update', $division->id) }}"
                                        onclick="openEditModal(this)"
                                        class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs font-bold text-amber-800 transition hover:bg-amber-100"
                                    >
                                        Edit Divisi
                                    </button>

                                    @if($division->users_count === 0)

                                        <form
                                            action="{{ route('admin.divisions.destroy', $division->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus divisi ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-xl bg-red-50 px-4 py-2.5 text-xs font-bold text-red-700 transition hover:bg-red-100"
                                            >
                                                Hapus
                                            </button>
                                        </form>

                                    @else

                                        <button
                                            type="button"
                                            disabled
                                            title="Pindahkan anggota terlebih dahulu sebelum menghapus divisi"
                                            class="cursor-not-allowed rounded-xl bg-gray-100 px-4 py-2.5 text-xs font-bold text-gray-400"
                                        >
                                            Hapus (masih digunakan)
                                        </button>

                                    @endif

                                </div>

                            </article>

                        @endforeach

                    </div>

                @else

                    {{-- BELUM ADA DIVISI --}}
                    <div class="darin-card rounded-2xl bg-white px-6 py-16 text-center">
                        <h3 class="text-lg font-extrabold text-gray-900">
                            Belum Ada Divisi
                        </h3>

                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-gray-500">
                            Belum ada data divisi di database.
                            Gunakan tombol Tambah Divisi untuk membuat divisi pertama.
                        </p>

                        <button
                            type="button"
                            onclick="openCreateModal()"
                            class="mt-5 rounded-xl bg-[#ffc107] px-5 py-3 text-sm font-extrabold text-black transition hover:bg-amber-400"
                        >
                            Tambah Divisi
                        </button>
                    </div>

                @endif
            </section>

            {{-- FOOTER --}}
            <footer class="border-t border-gray-200 pb-4 pt-5 text-center text-xs text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </footer>

        </div>
    </main>

</div>

{{-- MODAL TAMBAH DIVISI --}}
<div
    id="createModal"
    class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createModalTitle"
>
    <div class="my-auto w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-2xl">

        <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                    DARIN Administrator
                </p>

                <h2 id="createModalTitle" class="mt-1 text-lg font-extrabold text-gray-900">
                    Tambah Divisi Baru
                </h2>
            </div>

            <button
                type="button"
                onclick="closeCreateModal()"
                class="rounded-lg px-2 text-2xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-800"
                aria-label="Tutup"
            >
                &times;
            </button>
        </div>

        <form action="{{ route('admin.divisions.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="newDivisionName" class="darin-label">
                    Nama Divisi
                </label>

                <input
                    type="text"
                    id="newDivisionName"
                    name="name"
                    maxlength="100"
                    required
                    placeholder="Contoh: Divisi Teknologi Informasi"
                    class="darin-input"
                >
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button
                    type="button"
                    onclick="closeCreateModal()"
                    class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-200"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="rounded-xl bg-[#ffc107] px-5 py-2.5 text-sm font-extrabold text-black transition hover:bg-amber-400"
                >
                    Simpan Divisi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT DIVISI --}}
<div
    id="editModal"
    class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editModalTitle"
>
    <div class="my-auto w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-2xl">

        <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                    DARIN Administrator
                </p>

                <h2 id="editModalTitle" class="mt-1 text-lg font-extrabold text-gray-900">
                    Edit Nama Divisi
                </h2>
            </div>

            <button
                type="button"
                onclick="closeEditModal()"
                class="rounded-lg px-2 text-2xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-800"
                aria-label="Tutup"
            >
                &times;
            </button>
        </div>

        <form id="editDivisionForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="editDivisionName" class="darin-label">
                    Nama Divisi
                </label>

                <input
                    type="text"
                    id="editDivisionName"
                    name="name"
                    maxlength="100"
                    required
                    class="darin-input"
                >
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button
                    type="button"
                    onclick="closeEditModal()"
                    class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-200"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="rounded-xl bg-[#ffc107] px-5 py-2.5 text-sm font-extrabold text-black transition hover:bg-amber-400"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const createModal = document.getElementById('createModal');
    const editModal = document.getElementById('editModal');

    function openCreateModal() {
        createModal.classList.remove('hidden');
        createModal.classList.add('flex');

        document.getElementById('newDivisionName').focus();
    }

    function closeCreateModal() {
        createModal.classList.remove('flex');
        createModal.classList.add('hidden');
    }

    function openEditModal(button) {
        const form = document.getElementById('editDivisionForm');

        form.action = button.dataset.updateUrl;
        document.getElementById('editDivisionName').value = button.dataset.name || '';

        editModal.classList.remove('hidden');
        editModal.classList.add('flex');

        document.getElementById('editDivisionName').focus();
    }

    function closeEditModal() {
        editModal.classList.remove('flex');
        editModal.classList.add('hidden');
    }

    // PENCARIAN DIVISI
    document.getElementById('divisionSearch')?.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        const cards = document.querySelectorAll('.division-card');
        let visible = 0;

        cards.forEach(function (card) {
            const matches = (card.dataset.search || '').includes(keyword);

            card.classList.toggle('hidden', !matches);

            if (matches) {
                visible++;
            }
        });

        document.getElementById('searchEmpty')
            ?.classList.toggle('hidden', keyword === '' || visible > 0);
    });

    // TUTUP MODAL KETIKA AREA LUAR DIKLIK
    [createModal, editModal].forEach(function (modal) {
        modal?.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        });
    });

    // TUTUP MODAL DENGAN ESCAPE
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>

</body>
</html>
