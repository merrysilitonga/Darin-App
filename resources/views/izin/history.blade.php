<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Izin - DARIN</title>

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
</head>

<body class="text-gray-800">

<div class="flex min-h-screen">

    @include('layouts.sidebar')

    <main class="min-w-0 flex-1 overflow-y-auto p-5 md:p-10">

        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">
                    Riwayat Pengajuan Izin
                </h1>

                <p class="mt-2 text-sm text-gray-500">
                    Pantau status pengajuan dan keputusan Mentor.
                </p>

                <div class="mt-3 h-1 w-16 rounded-full bg-amber-400"></div>
            </div>

            <a
                href="{{ route('izin.create') }}"
                class="inline-flex justify-center rounded-xl bg-[#191712] px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800"
            >
                + Buat Pengajuan
            </a>
        </header>

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

        <div class="space-y-5">

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

                    <div class="flex flex-wrap items-start justify-between gap-4">

                        <div>
                            <h2 class="text-lg font-bold">
                                Pengajuan {{ $item->request_type }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Dikirim pada
                                {{ $item->created_at
                                    ? $item->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i')
                                    : '-' }}
                                WIB
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                            {{ $status }}
                        </span>

                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 rounded-xl bg-gray-50 p-4 sm:grid-cols-2">

                        <div>
                            <p class="text-xs text-gray-500">
                                Tanggal Mulai
                            </p>

                            <p class="mt-1 font-semibold">
                                {{ $item->start_date?->format('d M Y') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">
                                Tanggal Selesai
                            </p>

                            <p class="mt-1 font-semibold">
                                {{ $item->end_date?->format('d M Y') ?? '-' }}
                            </p>
                        </div>

                    </div>

                    <div class="mt-5">
                        <h3 class="text-sm font-bold">
                            Alasan Pengajuan
                        </h3>

                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $item->reason }}</p>
                    </div>

                    @if ($item->status === 'Menunggu')
                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm font-semibold text-amber-800">
                                Pengajuan sedang menunggu pemeriksaan Mentor.
                            </p>

                            <p class="mt-1 text-xs text-amber-700">
                                Hasil keputusan akan ditampilkan di halaman ini.
                            </p>
                        </div>
                    @endif

                    @if (in_array($item->status, ['Disetujui', 'Ditolak'], true))
                        <div class="mt-5 rounded-xl border border-gray-200 p-4">

                            <h3 class="text-sm font-bold">
                                Hasil Pemeriksaan
                            </h3>

                            <p class="mt-3 text-sm text-gray-600">
                                Diproses oleh:
                                <span class="font-semibold text-gray-800">
                                    {{ $item->reviewer->name ?? 'Mentor' }}
                                </span>
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                Waktu keputusan:
                                <span class="font-semibold text-gray-800">
                                    {{ $item->reviewed_at
                                        ? $item->reviewed_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB'
                                        : '-' }}
                                </span>
                            </p>

                            @if ($item->status === 'Disetujui')
                                <div class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">
                                    Pengajuan kamu telah disetujui.
                                </div>
                            @elseif ($item->status === 'Ditolak')
                                <div class="mt-4 rounded-lg bg-red-50 p-3">
                                    <p class="text-sm font-bold text-red-800">
                                        Catatan Penolakan
                                    </p>

                                    <p class="mt-2 whitespace-pre-line text-sm text-red-700">{{ $item->review_notes ?: 'Tidak ada catatan penolakan.' }}</p>
                                </div>
                            @endif

                        </div>
                    @endif

                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <a
                            href="{{ route('izin.download', $item->id) }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800"
                        >
                            📄 Unduh Surat Pendukung
                        </a>
                    </div>

                </article>

            @empty

                <div class="rounded-2xl border border-gray-100 bg-white px-6 py-16 text-center shadow-sm">

                    <p class="text-4xl">📄</p>

                    <h2 class="mt-4 font-bold">
                        Belum Ada Pengajuan
                    </h2>

                    <p class="mt-2 text-sm text-gray-500">
                        Pengajuan izin atau sakit kamu akan muncul di sini.
                    </p>

                    <a
                        href="{{ route('izin.create') }}"
                        class="mt-5 inline-flex rounded-xl bg-[#191712] px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800"
                    >
                        Buat Pengajuan
                    </a>

                </div>

            @endforelse

        </div>

    </main>

</div>

</body>
</html>