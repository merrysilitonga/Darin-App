<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logbook Harian - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fcfbf9;
        }
    </style>
</head>

<body class="min-h-screen text-gray-800">

<div class="flex min-h-screen">

    @include('layouts.sidebar')

    <main class="min-w-0 flex-1 p-5 md:p-8">

        {{-- HEADER --}}
        <header class="mb-6 flex flex-col justify-between gap-5 sm:flex-row sm:items-start">

            <div>
                <h1 class="text-2xl font-extrabold">
                    Logbook Harian Saya
                </h1>

                <div class="mt-3 h-1 w-16 rounded-full bg-amber-400"></div>
            </div>

            <div class="flex flex-wrap items-end gap-3">

                <form action="{{ route('logbook') }}" method="GET">
                    <label for="bulan"
                           class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        Bulan
                    </label>

                    <input
                        type="month"
                        id="bulan"
                        name="bulan"
                        value="{{ $bulan }}"
                        onchange="this.form.submit()"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-amber-400"
                    >
                </form>

                @php
                    $badgeClass = match ($monthStatus) {
                        'Disetujui' => 'border-green-200 bg-green-50 text-green-700',
                        'Menunggu Approval' => 'border-amber-200 bg-amber-50 text-amber-700',
                        default => 'border-gray-200 bg-gray-100 text-gray-600',
                    };
                @endphp

                <span class="rounded-full border px-4 py-2.5 text-xs font-bold {{ $badgeClass }}">
                    {{ $monthStatus === 'Menunggu Approval' ? 'Menunggu Approval' : $monthStatus }}
                </span>

            </div>

        </header>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="mb-2 font-bold">Catatan belum dapat disimpan:</p>

                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- INFORMASI STATUS --}}
        @if ($monthLocked)
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-bold text-amber-800">
                    Logbook {{ $monthLabel }} sedang diperiksa.
                </p>

                <p class="mt-1 text-xs text-amber-700">
                    Entri bulan ini terkunci sampai pemeriksaan Mentor selesai.
                </p>
            </div>
        @endif

        {{-- FORM TAMBAH ENTRI --}}
        @unless ($monthLocked)

            <section class="mb-5 rounded-2xl border border-[#eee4ce] bg-white p-5 shadow-sm">

                <h2 class="mb-5 flex items-center gap-2 font-bold">
                    <span class="text-amber-500">✚</span>
                    Tambah Entri
                </h2>

                <form
                    action="{{ route('logbook.store') }}"
                    method="POST"
                    class="grid grid-cols-1 items-end gap-4 lg:grid-cols-[130px_minmax(0,1fr)_auto]"
                >
                    @csrf

                    <input type="hidden" name="bulan" value="{{ $bulan }}">

                    <div>
                        <label for="tanggal"
                               class="mb-2 block text-xs font-medium text-gray-600">
                            Tanggal
                        </label>

                        <input
                            type="date"
                            id="tanggal"
                            name="date"
                            value="{{ old('date', $defaultDate) }}"
                            min="{{ $monthStart }}"
                            max="{{ $monthEnd }}"
                            required
                            class="w-full rounded-xl border border-[#e8e1d4] bg-[#fcfbf9] px-3 py-3 text-sm outline-none focus:border-amber-400"
                        >
                    </div>

                    <div>
                        <label for="deskripsi"
                               class="mb-2 block text-xs font-medium text-gray-600">
                            Deskripsi Pekerjaan
                        </label>

                        <textarea
                            id="deskripsi"
                            name="description"
                            rows="2"
                            maxlength="5000"
                            required
                            placeholder="Jelaskan pekerjaan yang dilakukan hari ini..."
                            class="w-full resize-y rounded-xl border border-[#e8e1d4] bg-[#fcfbf9] px-3 py-3 text-sm outline-none focus:border-amber-400"
                        >{{ old('description') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        class="rounded-xl bg-[#ffc107] px-5 py-3 text-sm font-bold text-black shadow-sm transition hover:bg-amber-400"
                    >
                        Simpan
                    </button>

                </form>

                <p class="mt-3 text-xs text-gray-400">
                    Setiap tanggal hanya boleh memiliki satu entri.
                    Tanggal harus sesuai periode magang dan memiliki absensi masuk.
                </p>

            </section>

        @endunless

        {{-- TABEL LOGBOOK --}}
        <section class="overflow-hidden rounded-2xl border border-[#eee4ce] bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[760px] border-collapse text-left">

                    <thead>
                        <tr class="bg-[#191712] text-xs font-bold uppercase tracking-wide text-white">
                            <th class="px-4 py-4">Tanggal</th>
                            <th class="px-4 py-4">Hari</th>
                            <th class="px-4 py-4">Deskripsi Pekerjaan</th>
                            <th class="px-4 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#eee4ce]">

                        @forelse ($logbooks as $item)

                            @php
                                $hari = match ($item->date?->dayOfWeekIso) {
                                    1 => 'Senin',
                                    2 => 'Selasa',
                                    3 => 'Rabu',
                                    4 => 'Kamis',
                                    5 => 'Jumat',
                                    6 => 'Sabtu',
                                    7 => 'Minggu',
                                    default => '-',
                                };

                                $editable = !$monthLocked
                                    && in_array($item->status, ['Draft', 'Ditolak'], true);
                            @endphp

                            <tr class="transition hover:bg-amber-50/30">

                                <td class="whitespace-nowrap px-4 py-4 text-xs text-gray-600">
                                    {{ $item->date?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-xs text-gray-500">
                                    {{ $hari }}
                                </td>

                                <td class="min-w-[350px] px-4 py-4">

                                    <p class="whitespace-pre-line break-words text-sm leading-relaxed text-gray-700">{{ $item->description }}</p>

                                    @if ($item->status === 'Disetujui')
                                        <span class="mt-2 inline-block rounded-full bg-green-50 px-2 py-1 text-[10px] font-bold text-green-700">
                                            Disetujui Mentor
                                        </span>
                                    @elseif ($item->status === 'Menunggu')
                                        <span class="mt-2 inline-block rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700">
                                            Menunggu Approval
                                        </span>
                                    @elseif ($item->status === 'Ditolak')
                                        <span class="mt-2 inline-block rounded-full bg-red-50 px-2 py-1 text-[10px] font-bold text-red-700">
                                            Perlu Revisi
                                        </span>

                                        @if ($item->mentor_feedback)
                                            <p class="mt-2 text-xs text-red-600">
                                                Catatan Mentor: {{ $item->mentor_feedback }}
                                            </p>
                                        @endif
                                    @else
                                        <span class="mt-2 inline-block rounded-full bg-gray-100 px-2 py-1 text-[10px] font-bold text-gray-600">
                                            Draft
                                        </span>
                                    @endif

                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-center">

                                    @if ($editable)

                                        {{-- EDIT --}}
                                        <button
                                            type="button"
                                            title="Edit entri"
                                            onclick="editLogbook(
                                                {{ $item->id }},
                                                @js($item->date?->format('Y-m-d') ?? ''),
                                                @js($item->description)
                                            )"
                                            class="mr-2 inline-flex h-9 w-9 items-center justify-center rounded-full border border-amber-200 bg-amber-50 text-amber-700 transition hover:bg-amber-100"
                                        >
                                            ✎
                                        </button>

                                        {{-- HAPUS --}}
                                        <form
                                            action="{{ route('logbook.destroy', ['id' => $item->id, 'bulan' => $bulan]) }}"
                                            method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Yakin ingin menghapus entri ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                title="Hapus entri"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100"
                                            >
                                                🗑
                                            </button>
                                        </form>

                                    @else

                                        <span class="text-xs text-gray-300">—</span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="px-5 py-16 text-center">
                                    <div class="text-4xl">📝</div>

                                    <h3 class="mt-3 font-bold text-gray-700">
                                        Belum Ada Entri Logbook
                                    </h3>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Tambahkan catatan pekerjaan untuk {{ $monthLabel }}.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        {{-- TOTAL DAN KIRIM APPROVAL --}}
        <section class="mt-5 flex flex-col gap-4 rounded-2xl border border-[#eee4ce] bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">

            <div>
                <p class="text-sm font-semibold text-gray-800">
                    Total entri bulan ini:
                    <span class="font-extrabold text-amber-600">
                        {{ $totalCatatan }}
                    </span>
                </p>

                @if ($monthLocked)
                    <p class="mt-1 text-xs text-amber-700">
                        Entri sedang menunggu pemeriksaan Mentor.
                    </p>
                @elseif ($monthStatus === 'Disetujui')
                    <p class="mt-1 text-xs text-green-700">
                        Seluruh entri bulan ini sudah disetujui.
                    </p>
                @else
                    <p class="mt-1 text-xs text-gray-400">
                        Setelah dikirim, entri bulan ini terkunci sampai pemeriksaan selesai.
                    </p>
                @endif
            </div>

            @if ($canSubmit)

                <form
                    action="{{ route('logbook.submit-month') }}"
                    method="POST"
                    onsubmit="return confirm('Kirim logbook {{ $monthLabel }} untuk approval Mentor?')"
                >
                    @csrf

                    <input type="hidden" name="bulan" value="{{ $bulan }}">

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-600"
                    >
                        ➤ Kirim untuk Approval
                    </button>
                </form>

            @else

                <button
                    type="button"
                    disabled
                    class="cursor-not-allowed rounded-xl bg-gray-200 px-5 py-3 text-sm font-bold text-gray-500"
                >
                    @if ($monthLocked)
                        Menunggu Pemeriksaan
                    @elseif ($monthStatus === 'Disetujui')
                        ✓ Sudah Disetujui
                    @else
                        Kirim untuk Approval
                    @endif
                </button>

            @endif

        </section>

        <footer class="py-6 text-center text-xs text-gray-400">
            DARIN · Sistem Presensi Geospasial Terintegrasi ESDM
        </footer>

    </main>

</div>

{{-- MODAL EDIT --}}
<div
    id="modal-edit"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
>
    <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">

        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-extrabold">
                Edit Entri Logbook
            </h2>

            <button
                type="button"
                onclick="closeEditModal()"
                class="text-2xl text-gray-400 hover:text-gray-800"
            >
                &times;
            </button>
        </div>

        <form id="form-edit" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="bulan" value="{{ $bulan }}">

            <div>
                <label for="edit-tanggal"
                       class="mb-2 block text-xs font-bold text-gray-600">
                    Tanggal
                </label>

                <input
                    type="date"
                    id="edit-tanggal"
                    name="date"
                    min="{{ $monthStart }}"
                    max="{{ $monthEnd }}"
                    required
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-amber-400"
                >
            </div>

            <div>
                <label for="edit-deskripsi"
                       class="mb-2 block text-xs font-bold text-gray-600">
                    Deskripsi Pekerjaan
                </label>

                <textarea
                    id="edit-deskripsi"
                    name="description"
                    rows="5"
                    maxlength="5000"
                    required
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-amber-400"
                ></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    onclick="closeEditModal()"
                    class="rounded-xl bg-gray-100 px-5 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-200"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="rounded-xl bg-[#ffc107] px-5 py-3 text-sm font-bold text-black hover:bg-amber-400"
                >
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    const editModal = document.getElementById('modal-edit');
    const editForm = document.getElementById('form-edit');
    const logbookBaseUrl = @js(url('/logbook'));

    function editLogbook(id, date, description) {
        editForm.action = logbookBaseUrl + '/' + id;

        document.getElementById('edit-tanggal').value = date;
        document.getElementById('edit-deskripsi').value = description;

        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
    }

    function closeEditModal() {
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
    }

    editModal.addEventListener('click', function (event) {
        if (event.target === editModal) {
            closeEditModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEditModal();
        }
    });
</script>

</body>
</html>