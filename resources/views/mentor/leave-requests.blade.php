<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Persetujuan Izin - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fcfbf9;
        }
    </style>
<!-- DARIN MENTOR TYPOGRAPHY FIX -->

</head>

<body class="text-gray-800">

<div class="flex min-h-screen">

    @include('layouts.sidebar')

    <main class="min-w-0 flex-1 p-5 md:p-10">

        {{-- HEADER --}}
        <header class="mb-8">
            <h1 class="text-2xl font-extrabold">
                Persetujuan Izin dan Sakit
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Periksa dan proses pengajuan izin mahasiswa bimbingan.
            </p>

            <div class="mt-3 h-1 w-16 rounded-full bg-amber-400"></div>
        </header>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="mb-2 font-bold text-red-700">
                    Terjadi kesalahan:
                </p>

                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">
                        {{ $error }}
                    </p>
                @endforeach
            </div>
        @endif

        {{-- DIVISI BELUM DITETAPKAN --}}
        @if ($assignmentMissing)

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                <h2 class="font-bold text-amber-800">
                    Divisi Mentor belum ditetapkan
                </h2>

                <p class="mt-2 text-sm text-amber-700">
                    Hubungi HR/Admin untuk menetapkan divisi pada akun
                    Mentor sebelum memproses pengajuan mahasiswa.
                </p>
            </div>

        @else

            {{-- STATISTIK --}}
            <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-3">

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">
                        Total Pengajuan
                    </p>

                    <p class="mt-3 text-3xl font-extrabold">
                        {{ $requests->count() }}
                    </p>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">
                        Menunggu
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-amber-600">
                        {{ $requests->where('status', 'Menunggu')->count() }}
                    </p>
                </div>

                <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">
                        Disetujui
                    </p>

                    <p class="mt-3 text-3xl font-extrabold text-green-600">
                        {{ $requests->where('status', 'Disetujui')->count() }}
                    </p>
                </div>

            </section>

            {{-- DAFTAR PENGAJUAN --}}
            <section class="space-y-5">

                @forelse ($requests as $item)

                    @php
                        $status = $item->status ?? 'Menunggu';

                        $statusClass = match ($status) {
                            'Disetujui' => 'bg-green-100 text-green-700',
                            'Ditolak' => 'bg-red-100 text-red-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp

                    <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm md:p-7">

                        {{-- IDENTITAS MAHASISWA --}}
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">

                            <div>
                                <h2 class="text-lg font-extrabold">
                                    {{ $item->user?->name ?? 'Mahasiswa' }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-500">
                                    NIM:
                                    {{ $item->user?->nim ?? '-' }}
                                </p>

                                <p class="mt-2 text-xs text-gray-400">
                                    Diajukan:
                                    {{ $item->created_at
                                        ? $item->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB'
                                        : '-' }}
                                </p>
                            </div>

                            <span class="w-fit rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClass }}">
                                {{ $status }}
                            </span>

                        </div>

                        {{-- DETAIL --}}
                        <div class="my-5 grid grid-cols-1 gap-4 rounded-xl bg-gray-50 p-4 sm:grid-cols-2">

                            <div>
                                <p class="text-xs text-gray-500">
                                    Jenis Pengajuan
                                </p>

                                <p class="mt-2 font-bold">
                                    {{ $item->request_type }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500">
                                    Periode Izin
                                </p>

                                <p class="mt-2 font-bold">
                                    {{ $item->start_date
                                        ? $item->start_date->format('d M Y')
                                        : '-' }}

                                    -

                                    {{ $item->end_date
                                        ? $item->end_date->format('d M Y')
                                        : '-' }}
                                </p>
                            </div>

                        </div>

                        {{-- ALASAN --}}
                        <div class="mb-5">
                            <h3 class="mb-2 text-sm font-bold">
                                Alasan Pengajuan
                            </h3>

                            <p class="whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $item->reason }}</p>
                        </div>

                        {{-- DOKUMEN --}}
                        <div class="mb-5">
                            <a
                                href="{{ route('mentor.leave-requests.document', $item->id) }}"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800"
                            >
                                📄 Unduh Surat Pendukung
                            </a>
                        </div>

                        {{-- CATATAN PENOLAKAN --}}
                        @if ($item->review_notes)
                            <div class="mb-5 rounded-xl border border-red-100 bg-red-50 p-4">
                                <p class="text-sm font-bold text-red-800">
                                    Catatan Penolakan
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm text-red-700">{{ $item->review_notes }}</p>
                            </div>
                        @endif

                        {{-- FORM PERSETUJUAN --}}
                        @if ($status === 'Menunggu')

                            <div class="grid grid-cols-1 gap-5 border-t border-gray-100 pt-5 md:grid-cols-2">

                                {{-- SETUJUI --}}
                                <form
                                    action="{{ route('mentor.leave-requests.approve', $item->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menyetujui pengajuan ini?')"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-green-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-green-700"
                                    >
                                        ✓ Setujui Pengajuan
                                    </button>
                                </form>

                                {{-- TOLAK --}}
                                <form
                                    action="{{ route('mentor.leave-requests.reject', $item->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menolak pengajuan ini?')"
                                >
                                    @csrf

                                    <label
                                        for="review_notes_{{ $item->id }}"
                                        class="mb-2 block text-sm font-bold"
                                    >
                                        Alasan Penolakan
                                    </label>

                                    <textarea
                                        id="review_notes_{{ $item->id }}"
                                        name="review_notes"
                                        rows="3"
                                        maxlength="2000"
                                        required
                                        placeholder="Jelaskan alasan penolakan..."
                                        class="mb-3 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                                    >{{ old('review_notes') }}</textarea>

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-700"
                                    >
                                        ✕ Tolak Pengajuan
                                    </button>
                                </form>

                            </div>

                        @else

                            {{-- RIWAYAT KEPUTUSAN --}}
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-sm text-gray-600">
                                    Diproses oleh:
                                    <span class="font-bold text-gray-800">
                                        {{ $item->reviewer?->name ?? '-' }}
                                    </span>
                                </p>

                                <p class="mt-1 text-sm text-gray-600">
                                    Waktu keputusan:
                                    <span class="font-semibold">
                                        {{ $item->reviewed_at
                                            ? $item->reviewed_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB'
                                            : '-' }}
                                    </span>
                                </p>
                            </div>

                        @endif

                    </article>

                @empty

                    <div class="rounded-2xl border border-gray-100 bg-white px-6 py-16 text-center shadow-sm">

                        <div class="text-4xl">📋</div>

                        <h2 class="mt-4 font-extrabold">
                            Belum Ada Pengajuan Izin
                        </h2>

                        <p class="mt-2 text-sm text-gray-500">
                            Pengajuan mahasiswa dalam divisi kamu akan muncul di halaman ini.
                        </p>

                    </div>

                @endforelse

            </section>

        @endif

        <footer class="mt-10 text-center text-xs text-gray-400">
            DARIN · Sistem Presensi Geospasial Terintegrasi ESDM
        </footer>

    </main>

</div>

</body>
</html>