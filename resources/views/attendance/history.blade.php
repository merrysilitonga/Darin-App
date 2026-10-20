<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Absensi - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fcfbf9;
        }
    </style>
</head>

<body class="text-gray-800 flex min-h-screen">

    @include('layouts.sidebar')

    <main class="flex-1 min-w-0 overflow-y-auto p-5 md:p-10">

        {{-- HEADER --}}
        <header class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Riwayat Absensi Kehadiran
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Daftar catatan masuk, pulang, metode kerja, dan validasi lokasi.
                </p>

                <div class="w-16 h-1 bg-[#ffc107] mt-2 rounded-full"></div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('riwayat.export-pdf', ['bulan' => request('bulan', now('Asia/Jakarta')->format('Y-m'))]) }}"
                    class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-sm hover:bg-gray-50 transition"
                >
                    📄 Unduh PDF
                </a>

                <a
                    href="{{ route('riwayat.export-csv', ['bulan' => request('bulan', now('Asia/Jakarta')->format('Y-m'))]) }}"
                    class="px-4 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-emerald-700 transition"
                >
                    📊 Unduh CSV / Excel
                </a>
            </div>
        </header>

        {{-- PESAN --}}
        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- INFORMASI USER --}}
        <section class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 block">
                        Peserta Magang
                    </span>

                    <h2 class="text-lg font-bold text-gray-800">
                        {{ Auth::user()->name ?? 'Pengguna' }}
                    </h2>

                    <p class="text-xs text-gray-500">
                        {{ Auth::user()->email ?? '-' }}
                    </p>
                </div>

                <div class="md:text-right">
                    <span class="text-[10px] font-bold uppercase text-gray-400 block">
                        Periode Laporan
                    </span>

                    <span class="text-sm font-bold text-gray-800">
                        {{ isset($periode) ? $periode->translatedFormat('F Y') : now('Asia/Jakarta')->translatedFormat('F Y') }}
                    </span>
                </div>
            </div>
        </section>

        {{-- FILTER BULAN --}}
        <section class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <form
                action="{{ route('riwayat.absen') }}"
                method="GET"
                class="flex flex-col md:flex-row md:items-end gap-4"
            >
                <div class="flex-1">
                    <label for="bulan" class="block text-xs font-bold text-gray-600 mb-2">
                        Filter Periode Laporan
                    </label>

                    <input
                        type="month"
                        name="bulan"
                        id="bulan"
                        value="{{ request('bulan', now('Asia/Jakarta')->format('Y-m')) }}"
                        required
                        class="w-full md:max-w-xs px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#ffc107] bg-gray-50"
                    >
                </div>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="px-5 py-3 rounded-xl bg-[#ffc107] text-black text-sm font-bold hover:bg-[#e0a800] transition"
                    >
                        Terapkan Filter
                    </button>

                    <a
                        href="{{ route('riwayat.absen') }}"
                        class="px-5 py-3 rounded-xl bg-white border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-50 transition"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </section>

        {{-- TABEL RIWAYAT --}}
        <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="font-bold text-gray-800">
                    Daftar Riwayat Absensi
                </h3>

                <span class="text-xs text-gray-500">
                    Total data: {{ ($attendances ?? collect())->count() }} absensi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-4 px-5">Tanggal</th>
                            <th class="py-4 px-5">Jam Masuk</th>
                            <th class="py-4 px-5">Jam Pulang</th>
                            <th class="py-4 px-5">Metode Kerja</th>
                            <th class="py-4 px-5">Durasi</th>
                            <th class="py-4 px-5">Status</th>
                            <th class="py-4 px-5">GPS</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($attendances ?? [] as $attendance)
                            <tr class="hover:bg-gray-50 transition">

                                {{-- TANGGAL --}}
                                <td class="py-4 px-5 font-semibold text-gray-800 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('D, d M Y') }}
                                </td>

                                {{-- JAM MASUK --}}
                                <td class="py-4 px-5 text-emerald-700 font-semibold whitespace-nowrap">
                                    {{ $attendance->clock_in
                                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') . ' WIB'
                                        : '-' }}
                                </td>

                                {{-- JAM PULANG --}}
                                <td class="py-4 px-5 text-blue-700 font-semibold whitespace-nowrap">
                                    {{ $attendance->clock_out
                                        ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') . ' WIB'
                                        : 'Belum absen' }}
                                </td>

                                {{-- METODE KERJA --}}
                                <td class="py-4 px-5 whitespace-nowrap">
                                    @if($attendance->work_location === 'WFA')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                            WFA
                                        </span>
                                    @elseif($attendance->work_location === 'WFO')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-violet-50 text-violet-700">
                                            WFO
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                {{-- DURASI BEKERJA --}}
                                <td class="py-4 px-5 font-semibold text-gray-700 whitespace-nowrap">
                                    @if($attendance->clock_in && $attendance->clock_out)
                                        @php
                                            $tanggalAbsensi = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');

                                            $masuk = \Carbon\Carbon::parse(
                                                $tanggalAbsensi . ' ' . $attendance->clock_in,
                                                'Asia/Jakarta'
                                            );

                                            $pulang = \Carbon\Carbon::parse(
                                                $tanggalAbsensi . ' ' . $attendance->clock_out,
                                                'Asia/Jakarta'
                                            );

                                            $totalMenit = max(0, $masuk->diffInMinutes($pulang, false));
                                            $jam = intdiv($totalMenit, 60);
                                            $menit = $totalMenit % 60;
                                        @endphp

                                        {{ $jam }} jam {{ $menit }} menit
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- STATUS KEHADIRAN --}}
                                <td class="py-4 px-5 whitespace-nowrap">
                                    @if($attendance->status === 'Hadir')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                            Hadir
                                        </span>
                                    @elseif($attendance->status === 'Terlambat')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                            {{ $attendance->status ?? 'Belum ada status' }}
                                        </span>
                                    @endif
                                </td>

                                {{-- STATUS GPS BERDASARKAN DATA --}}
                                <td class="py-4 px-5 whitespace-nowrap">
                                    @if(
                                        $attendance->latitude !== null &&
                                        $attendance->longitude !== null
                                    )
                                        <a
                                            href="https://www.google.com/maps?q={{ $attendance->latitude }},{{ $attendance->longitude }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100"
                                        >
                                            📍 Lihat lokasi
                                        </a>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                            GPS tidak tersedia
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center">
                                    <div class="text-4xl mb-3">📋</div>

                                    <p class="font-semibold text-gray-700">
                                        Belum ada riwayat absensi
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        Data pada periode ini akan tampil setelah Anda melakukan absensi.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <p class="text-xs text-gray-400 mt-4">
            Durasi di atas dihitung dari jam masuk hingga jam pulang, bukan pengurangan waktu istirahat.
        </p>

    </main>

</body>
</html>
