<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan & Divisi - DARIN Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8f9fa;
        }

        .sidebar {
            background: #191712;
        }

        .nav-link {
            transition: all .2s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, .06);
            color: #fff;
        }

        .nav-active {
            background: #ffc107;
            color: #000;
        }

        .card {
            border: 1px solid #eceef2;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
        }
    </style>
</head>

<body class="min-h-screen text-gray-800">

@php
    $adminName = auth()->user()->name ?? 'Administrator';

    $adminInitials = collect(explode(' ', trim($adminName)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');

    $totalDivisions = $divisions->count();
    $totalStudents = $divisions->sum('interns_count');
    $totalMentors = \App\Models\User::where('role', 'mentor')->count();
@endphp

<div class="flex min-h-screen">

    <aside class="sidebar flex w-64 shrink-0 flex-col justify-between text-white">
        <div>
            <div class="flex items-center gap-3 border-b border-white/10 px-6 py-6">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#ffc107] text-2xl font-extrabold text-black">
                    ⚡
                </div>

                <div>
                    <h1 class="text-xl font-extrabold tracking-wider">DARIN</h1>
                    <p class="mt-1 text-xs font-bold uppercase tracking-wider text-amber-400">
                        Portal HR / Admin
                    </p>
                </div>
            </div>

            <div class="border-b border-white/10 px-6 py-5">
                <p class="text-xs uppercase tracking-widest text-gray-500">Login sebagai</p>
                <p class="mt-2 font-semibold text-white">{{ $adminName }}</p>
                <p class="mt-1 text-xs text-gray-400">Administrator</p>
            </div>

            <nav class="space-y-2 p-4 text-sm font-semibold">
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link flex items-center gap-3 rounded-xl px-4 py-3.5 text-gray-300">
                    Dashboard Utama
                </a>

                <a href="{{ route('admin.users') }}"
                   class="nav-link flex items-center gap-3 rounded-xl px-4 py-3.5 text-gray-300">
                    Manajemen Pengguna
                </a>

                <a href="{{ route('admin.divisions') }}"
                   class="nav-active flex items-center gap-3 rounded-xl px-4 py-3.5 font-bold shadow-md">
                    Penempatan & Divisi
                </a>

                <a href="{{ route('admin.attendance') }}"
                   class="nav-link flex items-center gap-3 rounded-xl px-4 py-3.5 text-gray-300">
                    Rekap Absensi Global
                </a>

                <a href="{{ route('admin.settings') }}"
                   class="nav-link flex items-center gap-3 rounded-xl px-4 py-3.5 text-gray-300">
                    Pengaturan Sistem
                </a>
            </nav>
        </div>

        <div class="border-t border-white/10 p-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl bg-red-600/20 px-4 py-3 text-sm font-bold text-red-300 transition hover:bg-red-600 hover:text-white">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="min-w-0 flex-1">
        <header class="flex flex-col justify-between gap-4 border-b border-gray-200 bg-white px-6 py-5 shadow-sm md:flex-row md:items-center md:px-8">
            <div>
                <p class="text-sm text-gray-500">Administrator / Penempatan</p>
                <h2 class="mt-1 text-2xl font-extrabold text-gray-900">
                    Penempatan & Manajemen Divisi
                </h2>
                <p class="mt-2 text-sm text-gray-500">
                    Kelola divisi dan pantau penempatan mahasiswa serta Mentor.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">{{ $adminName }}</p>
                    <p class="text-xs text-gray-500">Human Resources / Administrator</p>
                </div>

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#ffc107] font-extrabold text-black">
                    {{ $adminInitials ?: 'AD' }}
                </div>
            </div>
        </header>

        <div class="space-y-6 p-5 md:p-8">

            @if(session('success'))
                <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

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

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm font-medium text-gray-500">Total Divisi</p>
                    <p class="mt-4 text-3xl font-extrabold text-gray-900">
                        {{ $totalDivisions }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Divisi terdaftar di sistem</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm font-medium text-gray-500">Mahasiswa Magang</p>
                    <p class="mt-4 text-3xl font-extrabold text-blue-600">
                        {{ $totalStudents }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">
                        Mahasiswa yang ditempatkan pada divisi
                    </p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <p class="text-sm font-medium text-gray-500">Total Mentor</p>
                    <p class="mt-4 text-3xl font-extrabold text-purple-600">
                        {{ $totalMentors }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Akun ber-role Mentor</p>
                </div>
            </section>

            <section class="card flex flex-col justify-between gap-5 rounded-3xl bg-white p-6 md:flex-row md:items-center md:p-7">
                <div>
                    <h3 class="text-lg font-extrabold text-gray-900">Daftar Divisi</h3>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">
                        Data di bawah diambil dari database. Penugasan Mentor setiap
                        mahasiswa tetap diatur melalui halaman Manajemen Pengguna.
                    </p>
                </div>

                <button type="button" onclick="openCreateModal()"
                    class="shrink-0 rounded-xl bg-[#ffc107] px-5 py-3 text-sm font-extrabold text-black shadow-sm transition hover:bg-amber-400">
                    + Tambah Divisi
                </button>
            </section>

            <section class="card rounded-2xl bg-white p-4">
                <label for="divisionSearch" class="mb-2 block text-sm font-semibold text-gray-700">
                    Cari divisi
                </label>

                <input type="search" id="divisionSearch"
                    placeholder="Masukkan nama divisi..."
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100">

                <p id="searchEmpty" class="mt-3 hidden text-sm text-amber-700">
                    Divisi yang dicari tidak ditemukan.
                </p>
            </section>

            <section>
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">Divisi Terdaftar</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Kelola nama divisi dan lihat ringkasan anggota.
                        </p>
                    </div>

                    <span class="rounded-full bg-amber-100 px-4 py-2 text-xs font-bold text-amber-900">
                        {{ $totalDivisions }} Divisi
                    </span>
                </div>

                @if($divisions->isNotEmpty())
                    <div id="divisionGrid" class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                        @foreach($divisions as $division)
                            @php
                                $divisionMentors = $division->users->where('role', 'mentor');
                                $divisionStudents = $division->users->where('role', 'magang');
                            @endphp

                            <article class="division-card card space-y-5 rounded-3xl bg-white p-6 md:p-7"
                                data-search="{{ strtolower($division->name) }}">

                                <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-5">
                                    <div class="flex min-w-0 items-center gap-4">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-2xl text-amber-800">
                                            🏢
                                        </div>

                                        <div class="min-w-0">
                                            <h4 class="break-words text-lg font-extrabold text-gray-900">
                                                {{ $division->name }}
                                            </h4>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ $division->interns_count }} mahasiswa ·
                                                {{ $division->mentors_count }} Mentor
                                            </p>
                                        </div>
                                    </div>

                                    <span class="shrink-0 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        Terdaftar
                                    </span>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-500">
                                        Mentor pada Divisi Ini
                                    </p>

                                    @if($divisionMentors->isNotEmpty())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($divisionMentors as $mentor)
                                                <span class="rounded-lg border border-purple-100 bg-purple-50 px-3 py-2 text-xs font-semibold text-purple-800">
                                                    👤 {{ $mentor->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">
                                            Belum ada Mentor yang ditempatkan pada divisi ini.
                                        </p>
                                    @endif
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                            Mahasiswa pada Divisi Ini
                                        </p>
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
                                        <p class="text-sm text-gray-500">
                                            Belum ada mahasiswa yang ditempatkan pada divisi ini.
                                        </p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-4">
                                    <a href="{{ route('admin.users') }}"
                                        class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-200">
                                        Kelola Pengguna
                                    </a>

                                    <button type="button"
                                        data-name="{{ $division->name }}"
                                        data-update-url="{{ route('admin.divisions.update', $division->id) }}"
                                        onclick="openEditModal(this)"
                                        class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-bold text-amber-800 transition hover:bg-amber-100">
                                        Edit Divisi
                                    </button>

                                    @if($division->users_count === 0)
                                        <form action="{{ route('admin.divisions.destroy', $division->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus divisi ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-bold text-red-700 transition hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled
                                            title="Pindahkan anggota terlebih dahulu sebelum menghapus divisi"
                                            class="cursor-not-allowed rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-400">
                                            Hapus (masih digunakan)
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="card rounded-3xl bg-white px-6 py-16 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-3xl">
                            🏢
                        </div>

                        <h4 class="mt-5 text-lg font-extrabold text-gray-900">
                            Belum Ada Divisi
                        </h4>

                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-gray-500">
                            Belum ada data divisi di database. Gunakan tombol Tambah Divisi
                            untuk membuat divisi pertama.
                        </p>

                        <button type="button" onclick="openCreateModal()"
                            class="mt-5 rounded-xl bg-[#ffc107] px-5 py-3 text-sm font-extrabold text-black transition hover:bg-amber-400">
                            + Tambah Divisi
                        </button>
                    </div>
                @endif
            </section>

            <footer class="pb-4 text-center text-xs text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </footer>
        </div>
    </main>
</div>

<!-- MODAL TAMBAH DIVISI -->
<div id="createModal"
    class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm"
    role="dialog" aria-modal="true">

    <div class="my-auto w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">DARIN Admin</p>
                <h3 class="mt-1 text-lg font-extrabold text-gray-900">Tambah Divisi Baru</h3>
            </div>

            <button type="button" onclick="closeCreateModal()"
                class="text-2xl text-gray-400 hover:text-gray-800" aria-label="Tutup">
                &times;
            </button>
        </div>

        <form action="{{ route('admin.divisions.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="newDivisionName" class="mb-2 block text-sm font-semibold text-gray-700">
                    Nama Divisi
                </label>

                <input type="text" id="newDivisionName" name="name" maxlength="100" required
                    placeholder="Contoh: Divisi Teknologi Informasi"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" onclick="closeCreateModal()"
                    class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-xl bg-[#ffc107] px-5 py-2.5 text-sm font-extrabold text-black hover:bg-amber-400">
                    Simpan Divisi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT DIVISI -->
<div id="editModal"
    class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm"
    role="dialog" aria-modal="true">

    <div class="my-auto w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">DARIN Admin</p>
                <h3 class="mt-1 text-lg font-extrabold text-gray-900">Edit Nama Divisi</h3>
            </div>

            <button type="button" onclick="closeEditModal()"
                class="text-2xl text-gray-400 hover:text-gray-800" aria-label="Tutup">
                &times;
            </button>
        </div>

        <form id="editDivisionForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="editDivisionName" class="mb-2 block text-sm font-semibold text-gray-700">
                    Nama Divisi
                </label>

                <input type="text" id="editDivisionName" name="name" maxlength="100" required
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" onclick="closeEditModal()"
                    class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-xl bg-[#ffc107] px-5 py-2.5 text-sm font-extrabold text-black hover:bg-amber-400">
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

    document.getElementById('divisionSearch')
        ?.addEventListener('input', function () {
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

    [createModal, editModal].forEach(function (modal) {
        modal?.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>

</body>
</html>
