<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Absensi &amp; Izin - DARIN</title>

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
                    Riwayat Absensi &amp; Izin
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Catatan masuk, pulang, metode kerja, lokasi, serta pengajuan izin/sakit dalam satu halaman.
                </p>

                <div class="w-16 h-1 bg-[#ffc107] mt-2 rounded-full"></div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="#form-izin"
                    data-buka-form-izin
                    class="px-4 py-2.5 bg-[#191712] text-white text-xs font-bold rounded-xl shadow-sm hover:bg-gray-800 transition"
                >
                    + Ajukan Izin / Sakit
                </a>

                <a
                    href="{{ route('rekap.saya') }}"
                    title="Rekap kehadiran seluruh periode magang (untuk laporan akhir)"
                    class="px-4 py-2.5 bg-white border border-amber-300 text-amber-800 text-xs font-bold rounded-xl shadow-sm hover:bg-amber-50 transition"
                >
                    📄 Laporan Akhir Magang
                </a>

                <a
                    href="{{ route('riwayat.export-pdf', ['bulan' => isset($periode) ? $periode->format('Y-m') : now('Asia/Jakarta')->format('Y-m')]) }}"
                    class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-sm hover:bg-gray-50 transition"
                >
                    📄 Unduh PDF
                </a>

                <a
                    href="{{ route('riwayat.export-csv', ['bulan' => isset($periode) ? $periode->format('Y-m') : now('Asia/Jakarta')->format('Y-m')]) }}"
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

        {{-- FORM PENGAJUAN IZIN / SAKIT (digabung di halaman ini) --}}
        @php
            $magangSelesai = isset($user) && $user->isInternshipEnded();
            $bukaFormIzin = request()->boolean('ajukan') || $errors->any() || old('request_type');
            $batasMulaiIzin = $user?->getRawOriginal('start_date') ? \Carbon\Carbon::parse($user->getRawOriginal('start_date'))->toDateString() : null;
            $batasSelesaiIzin = $user?->getRawOriginal('end_date') ? \Carbon\Carbon::parse($user->getRawOriginal('end_date'))->toDateString() : null;
        @endphp

        <details id="form-izin" class="scroll-mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 group" @if ($bukaFormIzin) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-5">
                <div>
                    <h3 class="font-bold text-gray-800">📝 Ajukan Izin / Sakit</h3>
                    <p class="text-xs text-gray-500 mt-1">Isi form dan unggah surat pendukung. Status pengajuan langsung terlihat di halaman ini.</p>
                </div>
                <span class="shrink-0 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-gray-600 group-open:hidden">Buka Form</span>
                <span class="hidden shrink-0 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-gray-600 group-open:inline">Tutup</span>
            </summary>

            <div class="border-t border-gray-100 p-5">
                @if ($magangSelesai)
                    <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        Periode magang Anda telah berakhir, sehingga tidak dapat mengajukan izin baru.
                    </p>
                @else
                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4">
                            <p class="mb-2 text-sm font-semibold text-red-700">Pengajuan belum terkirim. Periksa kembali:</p>
                            <ul class="list-inside list-disc space-y-1 text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        @csrf

                        <div class="md:col-span-2">
                            <span class="mb-2 block text-sm font-semibold text-gray-700">Jenis Pengajuan <span class="text-red-500">*</span></span>
                            <div class="grid grid-cols-2 gap-3 md:max-w-sm">
                                @foreach (['Sakit' => '🤒 Sakit', 'Izin' => '📌 Izin'] as $nilaiJenis => $labelJenis)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50">
                                        <input type="radio" name="request_type" value="{{ $nilaiJenis }}" required
                                               class="accent-amber-500" @checked(old('request_type') === $nilaiJenis)>
                                        {{ $labelJenis }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label for="start_date" class="mb-2 block text-sm font-semibold text-gray-700">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                                   @if ($batasMulaiIzin) min="{{ $batasMulaiIzin }}" @endif
                                   @if ($batasSelesaiIzin) max="{{ $batasSelesaiIzin }}" @endif
                                   class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                        </div>

                        <div>
                            <label for="end_date" class="mb-2 block text-sm font-semibold text-gray-700">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                                   @if ($batasMulaiIzin) min="{{ $batasMulaiIzin }}" @endif
                                   @if ($batasSelesaiIzin) max="{{ $batasSelesaiIzin }}" @endif
                                   class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                            <p class="mt-1 text-xs text-gray-400">Untuk 1 hari, isi sama dengan tanggal mulai.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="reason" class="mb-2 block text-sm font-semibold text-gray-700">Alasan Pengajuan <span class="text-red-500">*</span></label>
                            <textarea id="reason" name="reason" rows="3" maxlength="2000" required
                                      placeholder="Jelaskan alasan pengajuan izin atau sakit..."
                                      class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">{{ old('reason') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="document" class="mb-2 block text-sm font-semibold text-gray-700">Surat Pendukung <span class="text-red-500">*</span></label>
                            <input type="file" id="document" name="document" required accept=".pdf,.jpg,.jpeg,.png"
                                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-600 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-3 file:font-semibold file:text-gray-700 hover:file:bg-gray-200">
                            <p class="mt-1 text-xs text-gray-400">PDF, JPG, JPEG, atau PNG. Maksimal 2 MB.</p>
                        </div>

                        <div class="md:col-span-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-gray-400">Pengajuan berstatus <b>Menunggu</b> sampai diperiksa Mentor.</p>
                            <button type="submit" class="rounded-xl bg-[#191712] px-6 py-3 text-sm font-bold text-white hover:bg-gray-800">
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </details>

        <script>
            (function () {
                const panel = document.getElementById('form-izin');
                const mulai = document.getElementById('start_date');
                const selesai = document.getElementById('end_date');

                // Tombol "+ Ajukan Izin / Sakit" membuka form lalu menggulir ke sana.
                document.querySelectorAll('[data-buka-form-izin]').forEach(function (tombol) {
                    tombol.addEventListener('click', function (event) {
                        event.preventDefault();
                        panel.open = true;
                        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                });

                if (location.hash === '#form-izin') {
                    panel.open = true;
                }

                // Tanggal selesai tidak boleh sebelum tanggal mulai; default sama dengan tanggal mulai.
                if (mulai && selesai) {
                    mulai.addEventListener('change', function () {
                        selesai.min = mulai.value || selesai.min;

                        if (!selesai.value || selesai.value < mulai.value) {
                            selesai.value = mulai.value;
                        }
                    });
                }
            })();
        </script>

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
                        value="{{ isset($periode) ? $periode->format('Y-m') : now('Asia/Jakarta')->format('Y-m') }}"
                        @if (!empty($bulanPertama)) min="{{ $bulanPertama->format('Y-m') }}" @endif
                        @if (!empty($bulanTerakhir)) max="{{ $bulanTerakhir->format('Y-m') }}" @endif
                        required
                        class="w-full md:max-w-xs px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#ffc107] bg-gray-50"
                    >

                    @if (!empty($bulanPertama) && !empty($bulanTerakhir))
                        <p class="mt-2 text-xs text-gray-400">
                            Periode magang: {{ $bulanPertama->translatedFormat('F Y') }} – {{ $bulanTerakhir->translatedFormat('F Y') }}
                        </p>
                    @endif
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

        {{-- RINGKASAN BULAN INI --}}
        @php
            $ringkasan = array_merge(['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'menunggu' => 0], $ringkasan ?? []);
            $riwayat = $riwayat ?? [];
            $leaveRequests = $leaveRequests ?? collect();
        @endphp

        @php
            $kartuRingkasan = [
                'hadir' => ['Tepat Waktu', 'text-emerald-700'],
                'terlambat' => ['Terlambat', 'text-amber-700'],
                'izin' => ['Izin (disetujui)', 'text-blue-700'],
                'sakit' => ['Sakit (disetujui)', 'text-violet-700'],
                'alfa' => ['Alfa', 'text-red-700'],
                'menunggu' => ['Izin Menunggu', 'text-gray-700'],
            ];
        @endphp

        <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">
            @foreach ($kartuRingkasan as $kunci => $kartu)
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-[11px] font-bold uppercase text-gray-400">{{ $kartu[0] }}</p>
                    <p class="mt-1 text-2xl font-extrabold {{ $kartu[1] }}">{{ $ringkasan[$kunci] }}</p>
                </div>
            @endforeach
        </section>

        {{-- TABEL RIWAYAT --}}
        <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="font-bold text-gray-800">
                    Daftar Riwayat Harian
                </h3>

                <span class="text-xs text-gray-500">
                    {{ ($attendances ?? collect())->count() }} absensi · {{ collect($riwayat)->where('jenis', 'izin')->count() }} hari izin/sakit · {{ collect($riwayat)->where('jenis', 'alfa')->count() }} alfa
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
                        @forelse($riwayat as $baris)
                            @if (in_array($baris['jenis'], ['alfa', 'libur'], true))
                                {{-- BARIS ALFA / LIBUR --}}
                                <tr class="{{ $baris['jenis'] === 'alfa' ? 'bg-red-50/40' : 'bg-gray-50' }}">
                                    <td class="py-4 px-5 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($baris['tanggal'])->translatedFormat('D, d M Y') }}
                                    </td>
                                    <td colspan="4" class="py-4 px-5 text-xs text-gray-500">
                                        {{ $baris['keterangan'] ?? '' }}
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $baris['jenis'] === 'alfa' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $baris['jenis'] === 'alfa' ? 'Alfa' : 'Libur' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-xs text-gray-400">-</td>
                                </tr>
                                @continue
                            @endif

                            @if ($baris['jenis'] === 'izin')
                                @php
                                    $izin = $baris['leave'];
                                    $warnaIzin = match ($izin->status) {
                                        'Disetujui' => $izin->request_type === 'Sakit' ? 'bg-violet-50 text-violet-700' : 'bg-blue-50 text-blue-700',
                                        'Ditolak' => 'bg-red-50 text-red-700',
                                        default => 'bg-amber-50 text-amber-700',
                                    };
                                @endphp

                                {{-- BARIS IZIN / SAKIT --}}
                                <tr class="bg-blue-50/30 hover:bg-blue-50/60 transition">
                                    <td class="py-4 px-5 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($baris['tanggal'])->translatedFormat('D, d M Y') }}
                                    </td>
                                    <td colspan="4" class="py-4 px-5 text-gray-600">
                                        <span class="font-semibold">{{ $izin->request_type }}</span>
                                        <span class="text-gray-400">·</span>
                                        <span class="text-xs">{{ \Illuminate\Support\Str::limit($izin->reason, 70) }}</span>
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $warnaIzin }}">
                                            {{ $izin->request_type }} · {{ $izin->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <a href="#izin-{{ $izin->id }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                                            Lihat detail
                                        </a>
                                    </td>
                                </tr>
                                @continue
                            @endif

                            @php $attendance = $baris['attendance']; @endphp

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
                                        Belum ada riwayat absensi atau izin
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        Data pada periode ini akan tampil setelah Anda melakukan absensi atau mengajukan izin.
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

        {{-- PENGAJUAN IZIN / SAKIT --}}
        <section id="riwayat-izin" class="mt-8 scroll-mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <h3 class="font-bold text-gray-800">Pengajuan Izin / Sakit</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Pengajuan pada periode {{ isset($periode) ? $periode->translatedFormat('F Y') : '' }} beserta keputusan Mentor.
                    </p>
                </div>

                <span class="text-xs text-gray-500">Total: {{ $leaveRequests->count() }} pengajuan</span>
            </div>

            <div class="space-y-4">
                @forelse ($leaveRequests as $item)
                    @php
                        $statusIzin = $item->status ?? 'Menunggu';
                        $kelasStatus = match ($statusIzin) {
                            'Disetujui' => 'bg-green-100 text-green-700',
                            'Ditolak' => 'bg-red-100 text-red-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp

                    <article id="izin-{{ $item->id }}" class="scroll-mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-gray-800">
                                    {{ $item->request_type }}:
                                    {{ $item->start_date?->translatedFormat('d M Y') ?? '-' }}
                                    @if ($item->start_date && $item->end_date && !$item->start_date->equalTo($item->end_date))
                                        – {{ $item->end_date->translatedFormat('d M Y') }}
                                    @endif
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    Dikirim {{ $item->created_at ? $item->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                                </p>
                            </div>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $kelasStatus }}">{{ $statusIzin }}</span>
                        </div>

                        <p class="mt-3 whitespace-pre-line text-sm text-gray-600">{{ $item->reason }}</p>

                        @if ($statusIzin === 'Menunggu')
                            <p class="mt-3 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">
                                Menunggu pemeriksaan Mentor.
                            </p>
                        @elseif ($statusIzin === 'Ditolak')
                            <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm">
                                <p class="font-bold text-red-800">Catatan Penolakan</p>
                                <p class="mt-1 whitespace-pre-line text-red-700">{{ $item->review_notes ?: 'Tidak ada catatan penolakan.' }}</p>
                            </div>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500">
                            <span>
                                @if (in_array($statusIzin, ['Disetujui', 'Ditolak'], true))
                                    Diproses oleh <span class="font-semibold text-gray-700">{{ $item->reviewer->name ?? 'Mentor' }}</span>
                                    @if ($item->reviewed_at)
                                        · {{ $item->reviewed_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    @endif
                                @endif
                            </span>

                            <a href="{{ route('izin.download', $item->id) }}" class="font-semibold text-blue-600 hover:text-blue-800">
                                📄 Unduh Surat Pendukung
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-10 text-center">
                        <p class="text-3xl">📄</p>
                        <p class="mt-2 font-semibold text-gray-700">Tidak ada pengajuan izin/sakit pada periode ini</p>
                        <a href="#form-izin" data-buka-form-izin class="mt-4 inline-flex rounded-xl bg-[#191712] px-5 py-2.5 text-xs font-bold text-white hover:bg-gray-800">
                            + Ajukan Izin / Sakit
                        </a>
                    </div>
                @endforelse
            </div>
        </section>

    </main>

</body>
</html>
