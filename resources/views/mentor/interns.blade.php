
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Mahasiswa Bimbingan - DARIN Mentor</title>

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
    $mentorName = auth()->user()->name ?? 'Mentor';

    $mentorInitials = collect(
        preg_split('/\s+/', trim($mentorName))
    )->filter()->take(2)->map(function ($part) {
        return mb_substr($part, 0, 1);
    })->implode('');

    $totalStudents = $students->count();
@endphp

<div class="min-h-screen md:flex">

    <!-- SIDEBAR MENTOR -->
    <aside class="sidebar w-full shrink-0 text-white md:min-h-screen md:w-64">
        <div class="flex items-center gap-3 border-b border-white/10 px-6 py-6">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#ffc107] text-lg font-extrabold text-black">
                ⚡
            </div>

            <div>
                <h1 class="text-xl font-extrabold tracking-wide">
                    DARIN<span class="text-amber-400">.</span>
                </h1>
                <p class="mt-1 text-xs text-gray-400">Portal Mentor</p>
            </div>
        </div>

        <div class="border-b border-white/10 px-6 py-5">
            <p class="text-xs uppercase tracking-widest text-gray-500">
                Login sebagai
            </p>

            <p class="mt-2 font-semibold text-white">
                {{ $mentorName }}
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Mentor / Pembimbing
            </p>
        </div>

        <nav class="space-y-2 px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-widest text-gray-500">
                Menu Utama
            </p>

            <a href="{{ route('mentor.dashboard') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                📊 Dashboard
            </a>

            <a href="{{ route('mentor.interns') }}"
               class="nav-active flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold shadow-md">
                👨‍🎓 Daftar Mahasiswa
            </a>

            <a href="{{ route('mentor.logbook') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                📝 Validasi Logbook
            </a>

            <a href="{{ route('mentor.leave-requests') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                📋 Persetujuan Izin / Sakit
            </a>

            <a href="{{ route('mentor.attendance') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                📅 Mentoring & Kehadiran
            </a>

            <a href="{{ route('mentor.assessment') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                ⭐ Penilaian Magang
            </a>

            <a href="{{ route('profil') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                👤 Profil
            </a>
        </nav>

        <div class="border-t border-white/10 px-4 py-5">
            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit"
                        class="w-full rounded-xl px-4 py-3 text-left text-sm text-red-300 transition hover:bg-red-500/10">
                    🚪 Keluar / Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="min-w-0 flex-1">

        <!-- TOPBAR -->
        <header class="flex flex-col justify-between gap-4 border-b border-gray-200 bg-white px-5 py-5 shadow-sm sm:flex-row sm:items-center sm:px-8">
            <div>
                <p class="text-sm text-gray-500">
                    Portal Mentor / Mahasiswa Bimbingan
                </p>

                <h2 class="mt-1 text-2xl font-extrabold text-gray-900">
                    Daftar Mahasiswa Bimbingan
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    Kelola informasi mahasiswa yang ditugaskan kepada Anda.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 font-bold text-amber-800">
                    {{ $mentorInitials ?: 'M' }}
                </div>

                <div>
                    <p class="text-sm font-bold text-gray-800">
                        {{ $mentorName }}
                    </p>
                    <p class="text-xs text-gray-500">Mentor / Pembimbing</p>
                </div>
            </div>
        </header>

        <div class="space-y-6 p-5 sm:p-8">

            <!-- RINGKASAN DAN PENCARIAN -->
            <section class="card rounded-3xl bg-white p-6">
                <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">
                            Total Mahasiswa Bimbingan
                        </p>

                        <h3 class="mt-2 text-4xl font-extrabold text-gray-900">
                            {{ $totalStudents }}
                        </h3>

                        <p class="mt-2 text-sm text-gray-500">
                            Mahasiswa yang ditugaskan langsung oleh Admin/HR.
                        </p>
                    </div>

                    <div class="w-full lg:max-w-sm">
                        <label for="studentSearch"
                               class="mb-2 block text-sm font-semibold text-gray-700">
                            Cari Mahasiswa
                        </label>

                        <input
                            type="search"
                            id="studentSearch"
                            placeholder="Cari nama, NIM, atau universitas..."
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                        >

                        <p class="mt-2 text-xs text-gray-400">
                            Pencarian dilakukan pada daftar mahasiswa di bawah.
                        </p>
                    </div>
                </div>
            </section>

            <!-- NOTIFIKASI PENCARIAN -->
            <div id="searchEmpty"
                 class="hidden rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                Mahasiswa yang cocok dengan pencarian tidak ditemukan.
            </div>

            <!-- DAFTAR MAHASISWA -->
            <section>
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">
                            Mahasiswa Bimbingan
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Data diperoleh dari database DARIN.
                        </p>
                    </div>

                    <span class="rounded-full bg-amber-100 px-4 py-2 text-xs font-bold text-amber-900">
                        {{ $totalStudents }} Mahasiswa
                    </span>
                </div>

                @if($students->isNotEmpty())

                    <div id="studentGrid"
                         class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

                        @foreach($students as $student)

                            @php
                                $studentName = $student->name ?? 'Mahasiswa';

                                $studentInitials = collect(
                                    preg_split('/\s+/', trim($studentName))
                                )->filter()->take(2)->map(function ($part) {
                                    return mb_substr($part, 0, 1);
                                })->implode('');

                                $searchText = strtolower(implode(' ', [
                                    $student->name ?? '',
                                    $student->nim ?? '',
                                    $student->university ?? '',
                                    $student->study_program ?? '',
                                    $student->email ?? '',
                                    optional($student->division)->name ?? '',
                                ]));

                                $startDate = $student->start_date
                                    ? \Illuminate\Support\Carbon::parse($student->start_date)
                                    : null;

                                $endDate = $student->end_date
                                    ? \Illuminate\Support\Carbon::parse($student->end_date)
                                    : null;

                                $today = \Illuminate\Support\Carbon::now('Asia/Jakarta')
                                    ->startOfDay();

                                if (!$startDate || !$endDate) {
                                    $statusLabel = 'Periode belum diatur';
                                    $statusClass = 'bg-gray-100 text-gray-700';
                                } elseif ($today->lt($startDate->copy()->startOfDay())) {
                                    $statusLabel = 'Belum mulai';
                                    $statusClass = 'bg-blue-50 text-blue-700';
                                } elseif ($today->gt($endDate->copy()->startOfDay())) {
                                    $statusLabel = 'Selesai';
                                    $statusClass = 'bg-gray-100 text-gray-600';
                                } else {
                                    $statusLabel = 'Aktif';
                                    $statusClass = 'bg-emerald-50 text-emerald-700';
                                }

                                $avatarColors = [
                                    'bg-amber-100 text-amber-800',
                                    'bg-blue-100 text-blue-800',
                                    'bg-purple-100 text-purple-800',
                                    'bg-emerald-100 text-emerald-800',
                                    'bg-rose-100 text-rose-800',
                                    'bg-cyan-100 text-cyan-800',
                                ];

                                $avatarClass = $avatarColors[
                                    $loop->index % count($avatarColors)
                                ];

                                $whatsapp = preg_replace(
                                    '/\D+/',
                                    '',
                                    (string) $student->phone
                                );

                                if (str_starts_with($whatsapp, '0')) {
                                    $whatsapp = '62' . substr($whatsapp, 1);
                                }
                            @endphp

                            <article
                                class="student-card card flex flex-col justify-between space-y-5 rounded-3xl bg-white p-6 transition hover:-translate-y-0.5 hover:shadow-md"
                                data-search="{{ $searchText }}"
                            >
                                <div>
                                    <!-- IDENTITAS MAHASISWA -->
                                    <div class="mb-5 flex items-start gap-4">
                                        <div class="{{ $avatarClass }} flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-lg font-extrabold shadow-inner">
                                            {{ $studentInitials ?: 'M' }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <h4 class="break-words text-sm font-extrabold text-gray-900">
                                                {{ $student->name }}
                                            </h4>

                                            <p class="mt-1 break-all text-xs text-gray-500">
                                                {{ $student->email ?: 'Email belum diisi' }}
                                            </p>

                                            <span class="mt-2 inline-flex rounded-md px-2.5 py-1 text-[10px] font-bold {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- INFORMASI MAHASISWA -->
                                    <div class="space-y-3 border-t border-gray-100 pt-4 text-xs">
                                        <div class="flex items-start justify-between gap-3">
                                            <span class="shrink-0 text-gray-400">NIM</span>
                                            <span class="text-right font-semibold text-gray-800">
                                                {{ $student->nim ?: '-' }}
                                            </span>
                                        </div>

                                        <div class="flex items-start justify-between gap-3">
                                            <span class="shrink-0 text-gray-400">Universitas</span>
                                            <span class="max-w-[65%] text-right font-semibold text-gray-800">
                                                {{ $student->university ?: '-' }}
                                            </span>
                                        </div>

                                        <div class="flex items-start justify-between gap-3">
                                            <span class="shrink-0 text-gray-400">Program Studi</span>
                                            <span class="max-w-[65%] text-right font-semibold text-gray-800">
                                                {{ $student->study_program ?: '-' }}
                                            </span>
                                        </div>

                                        <div class="flex items-start justify-between gap-3">
                                            <span class="shrink-0 text-gray-400">Divisi</span>
                                            <span class="max-w-[65%] text-right font-semibold text-gray-800">
                                                {{ optional($student->division)->name ?? 'Belum ditetapkan' }}
                                            </span>
                                        </div>

                                        <div class="flex items-start justify-between gap-3">
                                            <span class="shrink-0 text-gray-400">No. WhatsApp</span>
                                            <span class="break-all text-right font-semibold text-gray-800">
                                                {{ $student->phone ?: '-' }}
                                            </span>
                                        </div>

                                        <div class="flex items-start justify-between gap-3">
                                            <span class="shrink-0 text-gray-400">Periode Magang</span>
                                            <span class="text-right font-semibold text-gray-800">
                                                @if($startDate && $endDate)
                                                    {{ $startDate->format('d M Y') }}
                                                    <span class="block my-1 text-gray-400">sampai</span>
                                                    {{ $endDate->format('d M Y') }}
                                                @else
                                                    Belum diatur
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- AKSI -->
                                <div class="flex items-center gap-2 border-t border-gray-100 pt-4">
                                    @if($whatsapp !== '')
                                        <a
                                            href="https://wa.me/{{ $whatsapp }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex-1 rounded-xl bg-emerald-50 px-3 py-2.5 text-center text-xs font-bold text-emerald-700 transition hover:bg-emerald-100"
                                        >
                                            💬 WhatsApp
                                        </a>
                                    @else
                                        <span class="flex-1 rounded-xl bg-gray-100 px-3 py-2.5 text-center text-xs text-gray-400">
                                            WhatsApp -
                                        </span>
                                    @endif

                                    <button
                                        type="button"
                                        class="open-profile flex-1 rounded-xl bg-[#ffc107] px-3 py-2.5 text-xs font-bold text-black transition hover:bg-amber-400"
                                        data-name="{{ $student->name }}"
                                        data-nim="{{ $student->nim ?: '-' }}"
                                        data-email="{{ $student->email ?: '-' }}"
                                        data-phone="{{ $student->phone ?: '-' }}"
                                        data-university="{{ $student->university ?: '-' }}"
                                        data-study="{{ $student->study_program ?: '-' }}"
                                        data-division="{{ optional($student->division)->name ?? 'Belum ditetapkan' }}"
                                        data-start="{{ $startDate ? $startDate->format('d M Y') : '-' }}"
                                        data-end="{{ $endDate ? $endDate->format('d M Y') : '-' }}"
                                    >
                                        👁️ Profil Lengkap
                                    </button>
                                </div>
                            </article>

                        @endforeach
                    </div>

                @else

                    <!-- KONDISI BELUM ADA MAHASISWA -->
                    <div class="card rounded-3xl bg-white px-6 py-16 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-3xl">
                            👨‍🎓
                        </div>

                        <h4 class="mt-5 text-lg font-extrabold text-gray-900">
                            Belum Ada Mahasiswa Bimbingan
                        </h4>

                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-gray-500">
                            @if($assignmentMissing)
                                Belum ada mahasiswa yang ditugaskan kepada akun mentor ini.
                                Silakan hubungi Admin/HR untuk mengatur penugasan melalui Manajemen Pengguna.
                            @else
                                Belum ada data mahasiswa yang dapat ditampilkan.
                            @endif
                        </p>
                    </div>

                @endif
            </section>

            <!-- FOOTER -->
            <footer class="pb-4 text-center text-xs text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </footer>
        </div>
    </main>
</div>

<!-- MODAL PROFIL -->
<div
    id="detailModal"
    class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalTitle"
>
    <div class="my-auto w-full max-w-lg space-y-5 rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">
                    DARIN Mentor
                </p>
                <h4 id="modalTitle" class="mt-1 text-lg font-extrabold text-gray-900">
                    Profil Lengkap Mahasiswa
                </h4>
            </div>

            <button
                type="button"
                id="closeModalButton"
                class="rounded-xl px-3 py-2 text-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                aria-label="Tutup modal"
            >
                &times;
            </button>
        </div>

        <div class="space-y-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">Nama Lengkap</p>
                <p id="modal-name" class="mt-1 break-words font-bold text-gray-900">-</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-400">NIM</p>
                    <p id="modal-nim" class="mt-1 break-words font-semibold text-gray-800">-</p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">No. WhatsApp</p>
                    <p id="modal-phone" class="mt-1 break-words font-semibold text-gray-800">-</p>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400">Asal Universitas</p>
                <p id="modal-university" class="mt-1 break-words font-semibold text-gray-800">-</p>
            </div>

            <div>
                <p class="text-xs text-gray-400">Program Studi</p>
                <p id="modal-study" class="mt-1 break-words font-semibold text-gray-800">-</p>
            </div>

            <div>
                <p class="text-xs text-gray-400">Alamat Email</p>
                <p id="modal-email" class="mt-1 break-words font-semibold text-gray-800">-</p>
            </div>

            <div>
                <p class="text-xs text-gray-400">Divisi</p>
                <p id="modal-division" class="mt-1 break-words font-semibold text-gray-800">-</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-400">Mulai Magang</p>
                    <p id="modal-start" class="mt-1 font-semibold text-gray-800">-</p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">Selesai Magang</p>
                    <p id="modal-end" class="mt-1 font-semibold text-gray-800">-</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end border-t border-gray-100 pt-4">
            <button
                type="button"
                id="closeModalFooter"
                class="rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-200"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('studentSearch');
    const studentCards = Array.from(document.querySelectorAll('.student-card'));
    const searchEmpty = document.getElementById('searchEmpty');

    // Pencarian nama, NIM, universitas, program studi, email, dan divisi.
    searchInput?.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        let visibleCount = 0;

        studentCards.forEach(function (card) {
            const searchableText = card.dataset.search || '';
            const matches = searchableText.includes(keyword);

            card.classList.toggle('hidden', !matches);

            if (matches) {
                visibleCount++;
            }
        });

        if (searchEmpty) {
            searchEmpty.classList.toggle(
                'hidden',
                keyword === '' || visibleCount > 0
            );
        }
    });

    const detailModal = document.getElementById('detailModal');

    function closeDetailModal() {
        detailModal.classList.add('hidden');
        detailModal.classList.remove('flex');
    }

    function openDetailModal(button) {
        const fields = {
            name: 'modal-name',
            nim: 'modal-nim',
            email: 'modal-email',
            phone: 'modal-phone',
            university: 'modal-university',
            study: 'modal-study',
            division: 'modal-division',
            start: 'modal-start',
            end: 'modal-end'
        };

        Object.entries(fields).forEach(function ([key, elementId]) {
            const element = document.getElementById(elementId);

            if (element) {
                // textContent mencegah data mahasiswa dianggap sebagai HTML.
                element.textContent = button.dataset[key] || '-';
            }
        });

        detailModal.classList.remove('hidden');
        detailModal.classList.add('flex');
    }

    document.querySelectorAll('.open-profile').forEach(function (button) {
        button.addEventListener('click', function () {
            openDetailModal(this);
        });
    });

    document.getElementById('closeModalButton')
        ?.addEventListener('click', closeDetailModal);

    document.getElementById('closeModalFooter')
        ?.addEventListener('click', closeDetailModal);

    detailModal?.addEventListener('click', function (event) {
        if (event.target === detailModal) {
            closeDetailModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && detailModal && !detailModal.classList.contains('hidden')) {
            closeDetailModal();
        }
    });
</script>

</body>
</html>
