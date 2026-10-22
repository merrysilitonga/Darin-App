@extends('layouts.mentor-layout')

@section('title', 'Daftar Mahasiswa Bimbingan')
@section('page_title', 'Daftar Mahasiswa Bimbingan')
@section('page_subtitle', 'Kelola informasi mahasiswa yang ditugaskan kepada Anda.')

@section('content')
@php
    $mentorStudents = collect($students ?? []);
@endphp

<div class="space-y-5">

    {{-- HEADER DAN PENCARIAN --}}
    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">

            <div>
                <h3 class="text-sm font-semibold leading-6 text-slate-700">
                    Mahasiswa Bimbingan
                </h3>

                <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                    Informasi profil dan kontak mahasiswa yang ditugaskan oleh Admin/HR.
                </p>

                <p class="mt-2 text-xs font-normal text-slate-400">
                    Total mahasiswa:
                    <span id="student-count" class="font-medium text-slate-600">
                        {{ $mentorStudents->count() }}
                    </span>
                </p>
            </div>

            <div class="w-full lg:max-w-sm">
                <label for="student-search"
                       class="mb-2 block text-xs font-medium text-slate-500">
                    Cari mahasiswa
                </label>

                <input
                    type="search"
                    id="student-search"
                    placeholder="Cari nama, NIM, atau universitas..."
                    autocomplete="off"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs font-normal text-slate-700 transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                >

                <p class="mt-2 text-xs font-normal text-slate-400">
                    Pencarian dilakukan pada daftar mahasiswa di bawah.
                </p>
            </div>

        </div>
    </section>

    {{-- DAFTAR MAHASISWA --}}
    <section>
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-700">
                    Daftar Mahasiswa
                </h3>

                <p class="mt-1 text-xs font-normal text-slate-500">
                    Data mahasiswa yang berada di bawah bimbingan Anda.
                </p>
            </div>

            <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700">
                {{ $mentorStudents->count() }} Mahasiswa
            </span>
        </div>

        @if ($mentorStudents->isNotEmpty())

            <div id="student-grid"
                 class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">

                @foreach ($mentorStudents as $student)

                    @php
                        $studentName = $student->name ?? 'Nama belum tersedia';
                        $studentEmail = $student->email ?? '';
                        $studentNim = $student->nim ?? '';
                        $studentPhone = $student->phone ?? '';
                        $studentUniversity = $student->university ?? '';
                        $studentStudyProgram = $student->study_program ?? '';

                        $initials = collect(
                            preg_split('/\s+/', trim($studentName))
                        )
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->implode('');

                        $searchText = mb_strtolower(implode(' ', array_filter([
                            $studentName,
                            $studentNim,
                            $studentEmail,
                            $studentPhone,
                            $studentUniversity,
                            $studentStudyProgram,
                        ])));

                        $phoneDigits = preg_replace('/\D+/', '', $studentPhone);

                        if (str_starts_with($phoneDigits, '0')) {
                            $whatsappNumber = '62' . substr($phoneDigits, 1);
                        } elseif (str_starts_with($phoneDigits, '62')) {
                            $whatsappNumber = $phoneDigits;
                        } elseif (str_starts_with($phoneDigits, '8')) {
                            $whatsappNumber = '62' . $phoneDigits;
                        } else {
                            $whatsappNumber = '';
                        }

                        $startDate = $student->start_date
                            ? \Illuminate\Support\Carbon::parse($student->start_date)->startOfDay()
                            : null;

                        $endDate = $student->end_date
                            ? \Illuminate\Support\Carbon::parse($student->end_date)->startOfDay()
                            : null;

                        $today = now()->startOfDay();

                        if (!$startDate || !$endDate) {
                            $periodStatus = 'Periode belum ditentukan';
                            $statusClass = 'bg-amber-50 text-amber-700';
                        } elseif ($today->lt($startDate)) {
                            $periodStatus = 'Akan dimulai';
                            $statusClass = 'bg-blue-50 text-blue-700';
                        } elseif ($today->gt($endDate)) {
                            $periodStatus = 'Periode selesai';
                            $statusClass = 'bg-gray-100 text-gray-600';
                        } else {
                            $periodStatus = 'Aktif';
                            $statusClass = 'bg-emerald-50 text-emerald-700';
                        }
                    @endphp

                    <article
                        data-student-card
                        data-search="{{ $searchText }}"
                        class="flex flex-col justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow-md"
                    >

                        <div>
                            {{-- IDENTITAS MAHASISWA --}}
                            <div class="mb-4 flex items-start gap-3">

                                @if (!empty($student->photo))
                                    <img
                                        src="{{ asset('storage/' . $student->photo) }}"
                                        alt="Foto {{ $studentName }}"
                                        class="h-12 w-12 shrink-0 rounded-xl border border-gray-100 object-cover"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-sm font-semibold text-amber-800">
                                        {{ $initials ?: 'M' }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <h4 class="break-words text-sm font-semibold leading-5 text-slate-700">
                                        {{ $studentName }}
                                    </h4>

                                    <p class="mt-1 text-xs font-normal text-slate-500">
                                        NIM: {{ $studentNim !== '' ? $studentNim : '-' }}
                                    </p>

                                    <span class="mt-2 inline-flex rounded-md px-2 py-1 text-[11px] font-medium {{ $statusClass }}">
                                        {{ $periodStatus }}
                                    </span>
                                </div>

                            </div>

                            {{-- INFORMASI MAHASISWA --}}
                            <div class="space-y-3 border-t border-gray-100 pt-4">

                                <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2">
                                    <span class="text-xs font-normal text-slate-400">
                                        Universitas
                                    </span>

                                    <span class="break-words text-right text-xs font-medium text-slate-600">
                                        {{ $studentUniversity !== '' ? $studentUniversity : '-' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2">
                                    <span class="text-xs font-normal text-slate-400">
                                        Program Studi
                                    </span>

                                    <span class="break-words text-right text-xs font-medium text-slate-600">
                                        {{ $studentStudyProgram !== '' ? $studentStudyProgram : '-' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2">
                                    <span class="text-xs font-normal text-slate-400">
                                        No. WhatsApp
                                    </span>

                                    <span class="break-words text-right text-xs font-medium text-slate-600">
                                        {{ $studentPhone !== '' ? $studentPhone : '-' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-[105px_minmax(0,1fr)] gap-2">
                                    <span class="text-xs font-normal text-slate-400">
                                        Email
                                    </span>

                                    <span class="break-all text-right text-xs font-medium text-slate-600">
                                        {{ $studentEmail !== '' ? $studentEmail : '-' }}
                                    </span>
                                </div>

                            </div>
                        </div>

                        {{-- AKSI --}}
                        <div class="flex items-center gap-2 border-t border-gray-100 pt-4">

                            @if ($whatsappNumber !== '')
                                <a
                                    href="https://wa.me/{{ $whatsappNumber }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex flex-1 items-center justify-center rounded-lg bg-emerald-50 px-3 py-2.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100"
                                >
                                    WhatsApp
                                </a>
                            @else
                                <span
                                    class="inline-flex flex-1 cursor-not-allowed items-center justify-center rounded-lg bg-gray-50 px-3 py-2.5 text-xs font-medium text-gray-400"
                                    title="Nomor WhatsApp belum tersedia"
                                >
                                    WhatsApp
                                </span>
                            @endif

                            <button
                                type="button"
                                data-open-profile
                                data-name="{{ $studentName }}"
                                data-nim="{{ $studentNim }}"
                                data-university="{{ $studentUniversity }}"
                                data-study="{{ $studentStudyProgram }}"
                                data-phone="{{ $studentPhone }}"
                                data-email="{{ $studentEmail }}"
                                data-start="{{ $startDate ? $startDate->format('d/m/Y') : '-' }}"
                                data-end="{{ $endDate ? $endDate->format('d/m/Y') : '-' }}"
                                class="inline-flex flex-1 items-center justify-center rounded-lg bg-amber-400 px-3 py-2.5 text-xs font-medium text-slate-800 transition hover:bg-amber-300"
                            >
                                Profil Lengkap
                            </button>

                        </div>

                    </article>

                @endforeach

            </div>

            {{-- HASIL PENCARIAN KOSONG --}}
            <div id="search-empty"
                 class="mt-4 hidden rounded-2xl border border-gray-100 bg-white px-5 py-10 text-center">

                <p class="text-sm font-medium text-slate-600">
                    Mahasiswa tidak ditemukan
                </p>

                <p class="mt-2 text-xs font-normal text-slate-400">
                    Coba gunakan nama, NIM, email, atau universitas yang berbeda.
                </p>

            </div>

        @else

            {{-- KOSONG JIKA BELUM ADA PENUGASAN --}}
            <div class="rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center shadow-sm">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-xl text-amber-700">
                    -
                </div>

                <h3 class="mt-4 text-sm font-semibold text-slate-700">
                    Belum Ada Mahasiswa Bimbingan
                </h3>

                <p class="mx-auto mt-2 max-w-lg text-xs font-normal leading-6 text-slate-500">
                    Belum ada mahasiswa yang ditugaskan kepada akun mentor ini.
                    Silakan hubungi Admin/HR untuk mengatur penugasan melalui Manajemen Pengguna.
                </p>

            </div>

        @endif

    </section>

</div>

{{-- MODAL PROFIL LENGKAP --}}
<div
    id="detailModal"
    class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-900/40 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title"
>
    <div class="my-auto w-full max-w-lg rounded-2xl border border-gray-100 bg-white p-5 shadow-xl sm:p-6">

        <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <h3 id="modal-title" class="text-base font-semibold text-slate-700">
                    Detail Profil Mahasiswa
                </h3>

                <p class="mt-1 text-xs font-normal text-slate-500">
                    Informasi mahasiswa bimbingan.
                </p>
            </div>

            <button
                type="button"
                data-close-profile
                class="rounded-lg px-3 py-2 text-lg font-normal text-slate-400 transition hover:bg-gray-100 hover:text-slate-700"
                aria-label="Tutup detail profil"
            >
                &times;
            </button>
        </div>

        <div class="mt-5 space-y-4">

            <div>
                <p class="text-xs font-normal text-slate-400">Nama Lengkap</p>
                <p id="modal-name" class="mt-1 break-words text-sm font-semibold text-slate-700">-</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-normal text-slate-400">NIM</p>
                    <p id="modal-nim" class="mt-1 break-words text-sm font-medium text-slate-600">-</p>
                </div>

                <div>
                    <p class="text-xs font-normal text-slate-400">Nomor WhatsApp</p>
                    <p id="modal-phone" class="mt-1 break-words text-sm font-medium text-slate-600">-</p>
                </div>
            </div>

            <div>
                <p class="text-xs font-normal text-slate-400">Asal Universitas</p>
                <p id="modal-university" class="mt-1 break-words text-sm font-medium text-slate-600">-</p>
            </div>

            <div>
                <p class="text-xs font-normal text-slate-400">Program Studi</p>
                <p id="modal-study" class="mt-1 break-words text-sm font-medium text-slate-600">-</p>
            </div>

            <div>
                <p class="text-xs font-normal text-slate-400">Alamat Email</p>
                <p id="modal-email" class="mt-1 break-all text-sm font-medium text-slate-600">-</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-normal text-slate-400">Tanggal Mulai Magang</p>
                    <p id="modal-start" class="mt-1 text-sm font-medium text-slate-600">-</p>
                </div>

                <div>
                    <p class="text-xs font-normal text-slate-400">Tanggal Selesai Magang</p>
                    <p id="modal-end" class="mt-1 text-sm font-medium text-slate-600">-</p>
                </div>
            </div>

        </div>

        <div class="mt-6 flex justify-end border-t border-gray-100 pt-4">
            <button
                type="button"
                data-close-profile
                class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 transition hover:bg-gray-50"
            >
                Tutup
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('student-search');
    const studentCards = Array.from(
        document.querySelectorAll('[data-student-card]')
    );

    const studentCount = document.getElementById('student-count');
    const searchEmpty = document.getElementById('search-empty');

    const modal = document.getElementById('detailModal');
    const openButtons = document.querySelectorAll('[data-open-profile]');
    const closeButtons = document.querySelectorAll('[data-close-profile]');

    function setText(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value || '-';
        }
    }

    function filterStudents() {
        if (!searchInput) {
            return;
        }

        const query = searchInput.value.trim().toLocaleLowerCase('id');
        let visibleCount = 0;

        studentCards.forEach(function (card) {
            const searchable = (
                card.dataset.search || ''
            ).toLocaleLowerCase('id');

            const match = searchable.includes(query);

            card.classList.toggle('hidden', !match);

            if (match) {
                visibleCount++;
            }
        });

        if (studentCount) {
            studentCount.textContent = visibleCount;
        }

        if (searchEmpty) {
            searchEmpty.classList.toggle(
                'hidden',
                visibleCount !== 0 || studentCards.length === 0
            );
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterStudents);
    }

    function openProfile(button) {
        setText('modal-name', button.dataset.name);
        setText('modal-nim', button.dataset.nim);
        setText('modal-university', button.dataset.university);
        setText('modal-study', button.dataset.study);
        setText('modal-phone', button.dataset.phone);
        setText('modal-email', button.dataset.email);
        setText('modal-start', button.dataset.start);
        setText('modal-end', button.dataset.end);

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.classList.add('overflow-hidden');

        const closeButton = modal.querySelector('[data-close-profile]');

        if (closeButton) {
            closeButton.focus();
        }
    }

    function closeProfile() {
        modal.classList.remove('flex');
        modal.classList.add('hidden');

        document.body.classList.remove('overflow-hidden');
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openProfile(button);
        });
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeProfile);
    });

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeProfile();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeProfile();
        }
    });
});
</script>
@endpush