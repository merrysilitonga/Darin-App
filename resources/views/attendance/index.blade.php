<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Absensi Harian - DARIN</title>

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

        button:disabled {
            cursor: not-allowed;
        }

        video,
        #previewFoto {
            background: #191712;
        }
    </style>
</head>

<body class="flex min-h-screen text-gray-800">

    {{-- SIDEBAR DARIN --}}
    @include('layouts.sidebar')

    @php
        $attendanceComplete = $attendance && $attendance->clock_out;
    @endphp

    {{-- KONTEN UTAMA --}}
    <main class="min-w-0 flex-1 p-5 md:p-10">

        {{-- HEADER --}}
        <header class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight">
                Portal Absensi Harian
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Lakukan absensi harian dengan verifikasi foto selfie dan lokasi GPS.
            </p>

            <div class="mt-2 h-1 w-16 rounded-full bg-[#ffc107]"></div>
        </header>

        {{-- PESAN SUKSES --}}
        @if(session('success'))
            <div
                role="status"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700"
            >
                {{ session('success') }}
            </div>
        @endif

        {{-- PESAN ERROR --}}
        @if(session('error'))
            <div
                role="alert"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- VALIDASI (error form izin tampil di dalam form izin sendiri) --}}
        @if($errors->any() && old('_form') !== 'izin')
            <div
                role="alert"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"
            >
                <p class="mb-2 font-bold">
                    Terjadi kesalahan:
                </p>

                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PENGAJUAN IZIN / SAKIT: di atas agar langsung terlihat tanpa scroll --}}
        @include('partials.form-izin')

        {{-- INFO HARI LIBUR --}}
        @include('partials.holiday-info')

        {{-- SECTION ABSENSI --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2" id="section-absen">

            {{-- KAMERA DAN GPS --}}
            <section class="flex flex-col items-center rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">

                <h2 class="mb-4 flex items-center gap-2 self-start font-bold text-gray-800">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#ffc107] text-xs font-extrabold text-[#191712]">1</span>
                    {{ $attendanceComplete
                        ? 'Absensi Hari Ini Selesai'
                        : ($attendance
                            ? 'Ambil Selfie untuk Absen Pulang'
                            : 'Ambil Selfie') }}
                </h2>

                {{-- ABSENSI SUDAH LENGKAP --}}
                @if($attendanceComplete)

                    <div class="mx-auto flex aspect-[3/4] w-full max-w-sm flex-col items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700">

                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                            <span class="text-3xl font-bold">Selesai</span>
                        </div>

                        <p class="text-sm font-bold">
                            Anda sudah absen hari ini
                        </p>

                        <p class="mt-3 text-xs text-emerald-600">
                            Jam masuk:
                            {{ $attendance->clock_in
                                ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                                : '-' }} WIB
                        </p>

                        <p class="mt-1 text-xs text-emerald-600">
                            Jam pulang:
                            {{ $attendance->clock_out
                                ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                                : '-' }} WIB
                        </p>
                    </div>

                {{-- LOKASI KANTOR BELUM TERSEDIA --}}
                @elseif(!$attendance && !$canClockInLocation)

                    <div class="w-full rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center">

                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100">
                            <span class="text-lg font-bold text-amber-800">!</span>
                        </div>

                        <h3 class="mt-3 font-bold text-amber-900">
                            Lokasi Kantor Belum Tersedia
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-amber-800">
                            Akun Anda belum ditetapkan ke lokasi kantor yang aktif.
                            Hubungi Admin/HR agar lokasi dan radius absensi ditetapkan
                            sebelum melakukan absensi masuk.
                        </p>
                    </div>

                {{-- KAMERA DAN FORM ABSENSI --}}
                @else

                    {{-- AREA KAMERA --}}
                    <div class="relative mx-auto mb-4 aspect-[3/4] w-full max-w-sm overflow-hidden rounded-2xl border border-gray-800 bg-gray-900">

                        <video
                            id="video"
                            autoplay
                            playsinline
                            muted
                            class="absolute inset-0 h-full w-full object-cover"
                            style="transform:scaleX(-1);"
                        ></video>

                        {{-- PRATINJAU FOTO --}}
                        <img
                            id="previewFoto"
                            src=""
                            alt="Pratinjau foto selfie absensi"
                            class="absolute inset-0 hidden h-full w-full object-contain"
                        >

                        <canvas id="canvas" class="hidden"></canvas>


                    </div>

                    {{-- PESAN KAMERA --}}
                    <p
                        id="pesanKamera"
                        class="mb-3 hidden w-full text-xs text-red-500"
                    ></p>

                    {{-- PESAN PRATINJAU --}}
                    <div
                        id="panelPesanFoto"
                        class="mb-4 hidden w-full rounded-xl border border-amber-200 bg-amber-50 p-4"
                    >
                        <p
                            id="judulFoto"
                            class="text-sm font-bold text-amber-800"
                        >
                            Periksa Foto Selfie
                        </p>

                        <p
                            id="pesanFoto"
                            class="mt-1 text-xs text-amber-700"
                        >
                            Pastikan wajah terlihat jelas sebelum mengirim absensi.
                        </p>
                    </div>

                    {{-- FORM ABSENSI --}}
                    <form
                        id="formAbsen"
                        action="{{ $attendance ? route('absen.checkout') : route('absen.store') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="w-full"
                    >
                        @csrf

                        {{-- LATITUDE --}}
                        <input
                            type="hidden"
                            name="latitude"
                            id="inpLat"
                        >

                        {{-- LONGITUDE --}}
                        <input
                            type="hidden"
                            name="longitude"
                            id="inpLng"
                        >

                        {{-- FOTO SELFIE --}}
                        <input
                            type="file"
                            name="photo"
                            id="photoInput"
                            accept="image/jpeg"
                            class="hidden"
                        >

                        {{-- METODE KERJA HARI INI: Jumat WFO/WFA/Dinas Luar, hari lain Kantor/Dinas Luar --}}
                        @if(!$attendance)
                            @php
                                $metodeLama = old('work_location');
                                // Hari Izin/Sakit disetujui: hanya boleh absen sebagai Dinas Luar.
                                $dinasDipilih = $metodeLama === 'Dinas Luar' || (bool) $todayLeave;
                            @endphp

                            @if($todayLeave)
                                <div class="mb-4 rounded-xl border border-violet-200 bg-violet-50 p-4 text-sm text-violet-800">
                                    <p class="font-bold">Hari Ini Anda Tercatat {{ $todayLeave->request_type }}</p>
                                    <p class="mt-1 text-xs leading-5">
                                        Pengajuan {{ strtolower($todayLeave->request_type) }}
                                        {{ \Carbon\Carbon::parse($todayLeave->getRawOriginal('start_date'))->locale('id')->translatedFormat('d M Y') }}
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($todayLeave->getRawOriginal('end_date'))->locale('id')->translatedFormat('d M Y') }}
                                        sudah disetujui. Anda tidak perlu absen. Jika ternyata tetap bertugas di luar kantor,
                                        absen sebagai Dinas Luar dan tunggu persetujuan Mentor.
                                    </p>
                                </div>
                            @endif

                            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4">

                                <label
                                    for="work_location"
                                    class="mb-2 block text-sm font-bold text-amber-900"
                                >
                                    {{ $isFriday ? 'Metode Kerja Hari Jumat' : 'Metode Kerja Hari Ini' }}
                                </label>

                                <select
                                    id="work_location"
                                    name="work_location"
                                    @if($isFriday || $todayLeave) required @endif
                                    class="w-full rounded-lg border border-amber-200 bg-white px-3 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                                >
                                    @if($todayLeave)
                                        {{-- Hari izin: hanya Dinas Luar --}}
                                    @elseif($isFriday)
                                        <option value="">Pilih metode kerja</option>
                                        <option value="WFO" @selected($metodeLama === 'WFO')>WFO - Bekerja di kantor (wajib dalam radius)</option>
                                        <option value="WFA" @selected($metodeLama === 'WFA')>WFA - Bekerja dari lokasi lain</option>
                                    @else
                                        <option value="">Di Kantor (wajib dalam radius)</option>
                                    @endif
                                    <option value="Dinas Luar" @selected($dinasDipilih)>Dinas Luar - Ditugaskan di luar kantor</option>
                                </select>

                                {{-- Tujuan dinas: wajib bila Dinas Luar dipilih --}}
                                <div id="kolomTujuanDinas" class="{{ $dinasDipilih ? '' : 'hidden' }} mt-3">
                                    <label for="tujuan_dinas" class="mb-1 block text-xs font-bold text-amber-900">
                                        Tujuan / Keterangan Dinas
                                    </label>
                                    <input
                                        type="text"
                                        id="tujuan_dinas"
                                        name="tujuan_dinas"
                                        value="{{ old('tujuan_dinas') }}"
                                        maxlength="255"
                                        @if($dinasDipilih) required @endif
                                        placeholder="Contoh: Rapat di Dinas ESDM Jawa Barat, Bandung"
                                        class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                                    >
                                </div>

                                <p class="mt-2 text-xs leading-5 text-amber-800">
                                    @if($isFriday)
                                        WFO diverifikasi dengan radius kantor. WFA dan Dinas Luar tidak dibatasi radius,
                                        tetapi koordinat GPS tetap disimpan sebagai bukti.
                                    @else
                                        Pilih Dinas Luar hanya jika Anda ditugaskan di luar kantor. Absen tidak dibatasi radius,
                                        tetapi koordinat GPS dan tujuan dinas tercatat dan dapat dilihat Mentor.
                                    @endif
                                </p>

                                @error('work_location')
                                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                @enderror

                                @error('tujuan_dinas')
                                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                @enderror

                            </div>

                        @endif

                        {{-- AMBIL FOTO --}}
                        <button
                            type="button"
                            id="btnAmbil"
                            disabled
                            class="w-full rounded-xl bg-[#ffc107] py-3 font-bold text-black shadow-md transition hover:bg-[#e0a800] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Ambil Foto
                        </button>

                        {{-- ULANGI / GUNAKAN FOTO --}}
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">

                            <button
                                type="button"
                                id="btnUlangi"
                                class="hidden w-full rounded-xl bg-gray-800 py-3 font-bold text-white transition hover:bg-gray-700"
                            >
                                Ulangi Foto
                            </button>

                            <button
                                type="button"
                                id="btnGunakan"
                                class="hidden w-full rounded-xl bg-emerald-600 py-3 font-bold text-white transition hover:bg-emerald-700"
                            >
                                Gunakan Foto
                            </button>

                        </div>

                        {{-- KIRIM ABSENSI --}}
                        <button
                            type="button"
                            id="btnKirim"
                            disabled
                            class="mt-3 hidden w-full rounded-xl bg-emerald-600 py-3 font-bold text-white shadow-md transition hover:bg-emerald-700 disabled:opacity-50"
                        >
                            Kirim Absensi
                        </button>

                    </form>

                    <p class="mt-4 text-center text-xs text-gray-400">
                        Foto hanya dikirim setelah Anda menyetujui hasil pratinjau.
                    </p>

                @endif

            </section>

            {{-- STATUS KEHADIRAN --}}
            <section class="flex flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">

                <div>

                    <h2 class="mb-4 flex items-center gap-2 font-bold text-gray-800">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-extrabold text-emerald-700">2</span>
                        Verifikasi Lokasi
                    </h2>

                    @php
                        // Absen pulang memakai lokasi yang tercatat saat absen masuk.
                        $lokasiPusat = $attendance?->location ?? $location;
                        // GPS dipantau selama masih ada absensi yang bisa dilakukan hari ini.
                        $pantauGps = (!$attendance && $canClockInLocation) || ($attendance && !$attendance->clock_out);
                    @endphp

                    {{-- KARTU VERIFIKASI LOKASI --}}
                    <div class="mb-6 rounded-2xl border border-gray-100 bg-gray-50 p-4 text-sm">

                        @if($lokasiPusat)

                            {{-- PUSAT MAGANG --}}
                            <div class="flex flex-col gap-2 border-b border-gray-200 pb-3 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-gray-500">Pusat Magang</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-right text-xs text-gray-700 sm:max-w-[70%]">
                                    <span class="font-semibold">{{ $lokasiPusat->name }}</span>
                                    <span class="font-mono text-gray-500">({{ $lokasiPusat->latitude }}, {{ $lokasiPusat->longitude }})</span>
                                </span>
                            </div>

                            @if($pantauGps)
                                {{-- STATUS GPS --}}
                                <div class="flex items-center justify-between gap-4 border-b border-gray-200 py-3">
                                    <span class="text-gray-500">Status GPS</span>
                                    <span id="statusRadius" class="rounded-full bg-gray-200 px-3 py-1 text-xs font-bold text-gray-600">
                                        Menunggu GPS...
                                    </span>
                                </div>

                                {{-- JARAK KE PUSAT (dihitung di browser, divalidasi ulang di server) --}}
                                <div class="flex items-center justify-between gap-4 border-b border-gray-200 py-3">
                                    <span class="text-gray-500">Jarak ke Pusat</span>
                                    <span id="jarakGPS" class="text-right font-bold text-gray-800">-</span>
                                </div>
                            @endif

                            {{-- BATAS DIIZINKAN --}}
                            <div class="flex items-center justify-between gap-4 {{ $pantauGps ? 'border-b border-gray-200' : '' }} py-3">
                                <span class="text-gray-500">Batas Diizinkan</span>
                                <span class="font-bold text-gray-800">{{ number_format((int) $lokasiPusat->radius_meters, 0, ',', '.') }} meter</span>
                            </div>

                            @if($pantauGps)
                                {{-- AKURASI GPS (perkiraan ketelitian lokasi dari browser) --}}
                                <div class="flex items-center justify-between gap-4 border-b border-gray-200 py-3">
                                    <span class="text-gray-500">Akurasi GPS</span>
                                    <span id="akurasiGPS" class="text-right text-xs font-semibold text-gray-500">
                                        Menunggu GPS...
                                    </span>
                                </div>

                                {{-- POSISI ANDA --}}
                                <div class="pt-3">
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="text-gray-500">Posisi Anda</span>
                                        <span id="koordinatGPS" class="max-w-[65%] break-all text-right font-mono text-xs text-gray-500">
                                            Mendeteksi lokasi...
                                        </span>
                                    </div>
                                    <a
                                        id="linkPetaGPS"
                                        href="#"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-1 hidden text-right text-xs text-blue-600 hover:underline"
                                    >
                                        Lihat di Google Maps
                                    </a>
                                </div>

                                <div id="peringatanAkurasi" class="mt-3 hidden rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-800">
                                    <p class="font-bold">Lokasi kurang akurat</p>
                                    <p class="mt-1">
                                        Perangkat ini memperkirakan lokasi dengan selisih <span data-akurasi-teks class="font-semibold"></span>,
                                        sehingga jarak ke kantor bisa tidak sesuai. Ini biasa terjadi di laptop/komputer yang tidak memiliki GPS.
                                        Gunakan HP dengan GPS aktif untuk absensi.
                                    </p>
                                </div>
                            @endif

                        @else

                            <p class="text-xs leading-5 text-gray-500">
                                Lokasi penempatan belum ditetapkan. Hubungi Admin/HR.
                            </p>

                        @endif

                    </div>

                    {{-- STATUS SUDAH ABSEN --}}
                    @if($attendance)

                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">

                            <p class="mb-1 flex items-center gap-3 text-sm font-bold text-emerald-800">
                                Status Absensi: {{ $attendance->status }}
                            </p>

                            <p class="text-xs leading-relaxed text-emerald-700">
                                {{ $attendance->notes ?? 'Absensi berhasil dicatat.' }}
                            </p>

                            @if($attendance->work_location === 'Dinas Luar')
                                <div class="flex flex-col">
                                    @include('partials.status-dinas', ['absen' => $attendance])
                                </div>
                                @if(!$attendance->clock_out)
                                    <p class="mt-2 text-xs text-emerald-700">Absen pulang tetap wajib dilakukan di akhir hari, dari lokasi dinas.</p>
                                @endif
                            @endif

                        </div>

                    @elseif($todayLeave)

                        <div class="mb-6 rounded-2xl border border-violet-200 bg-violet-50 p-4">
                            <p class="mb-1 text-sm font-bold text-violet-800">
                                Status: {{ $todayLeave->request_type }} (Disetujui)
                            </p>
                            <p class="text-xs leading-relaxed text-violet-700">
                                Tidak perlu absen hari ini. Jika tetap bertugas, absen sebagai Dinas Luar (menunggu persetujuan Mentor).
                            </p>
                        </div>

                    @else

                        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">

                            <p class="mb-1 text-sm font-bold text-amber-800">
                                Status Absensi
                            </p>

                            <p class="text-xs leading-relaxed text-amber-700">
                                Ambil foto, periksa hasilnya, lalu kirim absensi
                                untuk mencatat kehadiran Anda.
                            </p>

                        </div>

                    @endif

                    {{-- INFORMASI KEHADIRAN --}}
                    <div class="space-y-3 text-sm text-gray-600">

                        {{-- TANGGAL --}}
                        <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                            <span class="text-gray-400">Tanggal</span>
                            <span class="text-right font-semibold text-gray-800">{{ $now->translatedFormat('l, d F Y') }}</span>
                        </div>

                        {{-- NAMA --}}
                        <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                            <span class="text-gray-400">Nama</span>
                            <span class="text-right font-semibold text-gray-800">{{ $user->name ?? 'Pengguna' }}</span>
                        </div>

                        {{-- EMAIL --}}
                        <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                            <span class="text-gray-400">Email</span>
                            <span class="break-all text-right font-semibold text-gray-800">{{ $user->email ?? '-' }}</span>
                        </div>

                        {{-- JAM MASUK --}}
                        <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                            <span class="text-gray-400">Jam Masuk</span>
                            <span class="font-semibold text-gray-800">
                                {{ $attendance && $attendance->clock_in
                                    ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i:s')
                                    : '-' }}
                            </span>
                        </div>

                        {{-- JAM PULANG --}}
                        <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                            <span class="text-gray-400">Jam Pulang</span>
                            <span class="font-semibold text-gray-800">
                                {{ $attendance && $attendance->clock_out
                                    ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i:s')
                                    : ($todayLeave ? 'Tidak perlu (' . $todayLeave->request_type . ')' : 'Belum absen pulang') }}
                            </span>
                        </div>

                        {{-- JARAK TERCATAT SAAT MASUK / PULANG --}}
                        @if($attendance)
                            <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                                <span class="text-gray-400">Jarak Saat Masuk</span>
                                <span class="font-semibold text-gray-800">
                                    {{ $attendance->distance_meters !== null ? $attendance->distance_meters . ' m' : '-' }}
                                </span>
                            </div>

                            @if($attendance->clock_out)
                                <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                                    <span class="text-gray-400">Jarak Saat Pulang</span>
                                    <span class="font-semibold text-gray-800">
                                        {{ $attendance->clock_out_distance_meters !== null ? $attendance->clock_out_distance_meters . ' m' : '-' }}
                                    </span>
                                </div>
                            @endif
                        @endif

                    </div>

                </div>

                {{-- INFORMASI STATUS AKHIR --}}
                <div class="mt-6">

                    @if($attendanceComplete)

                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">

                            <p class="font-bold">
                                Absensi Hari Ini Lengkap
                            </p>

                            <p class="mt-1 text-xs leading-5">
                                Foto dan koordinat GPS masuk serta pulang telah tercatat.
                            </p>

                        </div>

                    @elseif($attendance)

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">

                            <p class="font-bold">
                                Absensi Pulang Belum Tercatat
                            </p>

                            <p class="mt-1 text-xs leading-5">
                                Ambil selfie baru dan izinkan GPS pada panel sebelah kiri
                                untuk melakukan absensi pulang setelah jadwal tersedia.
                            </p>

                        </div>

                    @elseif(!$todayLeave)

                        <p class="text-xs leading-5 text-gray-400">
                            Setelah absensi masuk tercatat, Anda dapat melakukan
                            absensi pulang menggunakan selfie dan GPS terbaru.
                        </p>

                    @endif

                </div>

            </section>

        </div>

    </main>

</div>

{{-- JAVASCRIPT KAMERA, FOTO, DAN GPS --}}
<script>
    @if((!$attendance && $canClockInLocation) || ($attendance && !$attendance->clock_out))

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const previewFoto = document.getElementById('previewFoto');
        const photoInput = document.getElementById('photoInput');

        const btnAmbil = document.getElementById('btnAmbil');
        const btnUlangi = document.getElementById('btnUlangi');
        const btnGunakan = document.getElementById('btnGunakan');
        const btnKirim = document.getElementById('btnKirim');

        const formAbsen = document.getElementById('formAbsen');

        // Status GPS hanya ditampilkan di satu tempat: badge "Status GPS" pada kartu Verifikasi Lokasi.
        // Kode lama yang mengisi gpsText/gpsDot tetap dipakai; teksnya diteruskan ke badge itu.
        const gpsText = {
            set textContent(teks) {
                tampilkanStatusGps(teks);
            },
        };
        const gpsDot = { className: '' };

        const koordinatGPS = document.getElementById('koordinatGPS');
        const linkPetaGPS = document.getElementById('linkPetaGPS');

        const pesanKamera = document.getElementById('pesanKamera');
        const panelPesanFoto = document.getElementById('panelPesanFoto');
        const pesanFoto = document.getElementById('pesanFoto');

        @php
            // Absen pulang memakai lokasi yang tercatat saat absen masuk.
            $geoLocation = $attendance?->location ?? $location;
        @endphp

        // Titik lokasi & radius untuk pengecekan awal di browser.
        // Server tetap melakukan validasi ulang (sumber kebenaran).
        const geofence = @js($geoLocation ? [
            'lat' => (float) $geoLocation->latitude,
            'lng' => (float) $geoLocation->longitude,
            'radius' => (int) $geoLocation->radius_meters,
            'name' => $geoLocation->name,
        ] : null);

        // Absen pulang tidak dibatasi radius untuk WFA (hari Jumat) dan Dinas Luar.
        const clockOutWfa = @js((bool) ($attendance && (($isFriday && $attendance->work_location === 'WFA') || $attendance->work_location === 'Dinas Luar')));
        const clockOutDinas = @js((bool) ($attendance && $attendance->work_location === 'Dinas Luar'));

        const jarakGPS = document.getElementById('jarakGPS');
        const statusRadius = document.getElementById('statusRadius');
        const akurasiGPS = document.getElementById('akurasiGPS');
        const peringatanAkurasi = document.getElementById('peringatanAkurasi');
        const workLocationSelect = document.getElementById('work_location');

        // Di atas angka ini (meter), lokasi dianggap kurang akurat dan peserta diberi peringatan.
        const BATAS_AKURASI_METER = 100;

        // Posisi GPS terakhir yang berhasil dibaca (dipakai ulang saat Kirim agar tidak timeout).
        const UMUR_POSISI_SEGAR_MS = 30000;      // ≤ 30 detik: langsung dipakai.
        const UMUR_POSISI_CADANGAN_MS = 300000;  // ≤ 5 menit: cadangan jika GPS baru timeout.
        let posisiTerakhir = null;

        function simpanPosisiTerakhir(position) {
            posisiTerakhir = {
                coords: {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                },
                waktu: Date.now(),
            };
        }

        function posisiTerakhirBerumurPaling(batasMs) {
            return posisiTerakhir !== null && (Date.now() - posisiTerakhir.waktu) <= batasMs
                ? posisiTerakhir
                : null;
        }

        let cameraStream = null;
        let cameraReady = false;
        let gpsReady = false;
        let photoApproved = false;
        let previewUrl = null;
        let submitting = false;
        let lastDistance = null;

        // Jarak great-circle (meter) dengan formula Haversine — sama dengan perhitungan server.
        function distanceInMeters(lat1, lng1, lat2, lng2) {
            const toRad = deg => deg * Math.PI / 180;
            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat / 2) ** 2
                + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
            const clamped = Math.min(1, Math.max(0, a));

            return Math.round(6371000 * 2 * Math.atan2(Math.sqrt(clamped), Math.sqrt(1 - clamped)));
        }

        // True jika absen tidak dibatasi radius (WFA hari Jumat atau Dinas Luar).
        function isWfaSelected() {
            return clockOutWfa || (workLocationSelect !== null && ['WFA', 'Dinas Luar'].includes(workLocationSelect.value));
        }

        function isDinasSelected() {
            return clockOutDinas || (workLocationSelect !== null && workLocationSelect.value === 'Dinas Luar');
        }

        // True jika posisi boleh dipakai absen (dalam radius, WFA, atau lokasi tidak diketahui → serahkan ke server).
        function isPositionAllowed() {
            if (!geofence || lastDistance === null || isWfaSelected()) {
                return true;
            }

            return lastDistance <= geofence.radius;
        }

        function updateDistance(latitude, longitude) {
            if (!geofence) {
                return;
            }

            lastDistance = distanceInMeters(latitude, longitude, geofence.lat, geofence.lng);

            if (!jarakGPS) {
                return;
            }

            // [teks badge, warna badge, warna angka jarak]
            const tampilan = isWfaSelected()
                ? [(isDinasSelected() ? 'Dinas Luar' : 'WFA') + ' — tanpa batas radius', 'bg-blue-100 text-blue-700', 'text-blue-700']
                : (lastDistance <= geofence.radius
                    ? ['Sesuai Radius', 'bg-emerald-100 text-emerald-700', 'text-gray-800']
                    : ['Di Luar Radius', 'bg-red-100 text-red-700', 'text-red-600']);

            jarakGPS.textContent = lastDistance.toLocaleString('id-ID') + ' meter';
            jarakGPS.className = 'text-right font-bold ' + tampilan[2];

            if (statusRadius) {
                statusRadius.textContent = tampilan[0];
                statusRadius.className = 'rounded-full px-3 py-1 text-xs font-bold ' + tampilan[1];
            }
        }

        // Badge Status GPS: "Mencari lokasi..." / pesan gagal (merah).
        // Saat GPS berhasil, isi badge diatur updateDistance() (Sesuai / Di Luar Radius / WFA).
        function tampilkanStatusGps(teks) {
            if (!statusRadius || /Terdeteksi/.test(teks)) {
                return;
            }

            const mencari = teks === 'Mencari lokasi...';

            statusRadius.textContent = teks;
            statusRadius.className = 'rounded-full px-3 py-1 text-xs font-bold '
                + (mencari ? 'bg-gray-200 text-gray-600' : 'bg-red-100 text-red-700');
        }

        // Menampilkan perkiraan ketelitian lokasi (coords.accuracy, dalam meter).
        function tampilkanAkurasi(accuracy) {
            if (!akurasiGPS || !Number.isFinite(accuracy)) {
                return;
            }

            const meter = Math.round(accuracy);
            const teks = meter >= 1000
                ? '±' + (meter / 1000).toFixed(1).replace('.', ',') + ' km'
                : '±' + meter + ' m';
            const kurangAkurat = meter > BATAS_AKURASI_METER;

            akurasiGPS.textContent = teks + (kurangAkurat ? ' — kurang akurat' : ' — baik');
            akurasiGPS.className = 'text-right text-xs font-semibold ' + (kurangAkurat ? 'text-amber-600' : 'text-emerald-600');

            if (peringatanAkurasi) {
                peringatanAkurasi.querySelector('[data-akurasi-teks]').textContent = teks;
                peringatanAkurasi.classList.toggle('hidden', !kurangAkurat);
            }
        }

        // Memperbarui tombol berdasarkan kondisi kamera, GPS, foto, dan radius.
        function updateButtons() {
            btnAmbil.disabled = !(cameraReady && gpsReady);
            btnKirim.disabled = !(photoApproved && gpsReady && isPositionAllowed());
        }

        if (workLocationSelect) {
            workLocationSelect.addEventListener('change', function () {
                // Kolom tujuan dinas hanya tampil (dan wajib) saat Dinas Luar dipilih.
                const kolomTujuan = document.getElementById('kolomTujuanDinas');
                const inputTujuan = document.getElementById('tujuan_dinas');

                if (kolomTujuan && inputTujuan) {
                    const dinas = workLocationSelect.value === 'Dinas Luar';
                    kolomTujuan.classList.toggle('hidden', !dinas);
                    inputTujuan.required = dinas;
                }

                const lat = parseFloat(document.getElementById('inpLat').value);
                const lng = parseFloat(document.getElementById('inpLng').value);

                if (Number.isFinite(lat) && Number.isFinite(lng)) {
                    updateDistance(lat, lng);
                }

                updateButtons();
            });
        }

        // Menampilkan kesalahan kamera.
        function showCameraError(message) {
            pesanKamera.textContent = message;
            pesanKamera.classList.remove('hidden');
        }

        // Menghentikan seluruh track kamera.
        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => {
                    track.stop();
                });
            }

            cameraStream = null;
            video.srcObject = null;
            cameraReady = false;

            updateButtons();
        }

        // Membersihkan URL pratinjau foto.
        function clearPreviewUrl() {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }
        }

        // Mengaktifkan kamera perangkat.
        async function startCamera() {
            if (
                !navigator.mediaDevices ||
                !navigator.mediaDevices.getUserMedia
            ) {
                showCameraError(
                    'Browser tidak mendukung kamera. Gunakan Chrome atau Edge melalui localhost.'
                );

                return;
            }

            btnAmbil.disabled = true;

            try {
                pesanKamera.classList.add('hidden');

                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user'
                    },
                    audio: false
                });

                const metadataReady = new Promise(resolve => {
                    if (video.readyState >= 1) {
                        resolve();
                    } else {
                        video.addEventListener(
                            'loadedmetadata',
                            resolve,
                            { once: true }
                        );
                    }
                });

                video.srcObject = cameraStream;

                await metadataReady;
                await video.play();

                cameraReady =
                    video.videoWidth > 0 &&
                    video.videoHeight > 0;

                if (!cameraReady) {
                    showCameraError(
                        'Kamera belum siap. Silakan coba kembali.'
                    );
                }

            } catch (error) {
                stopCamera();

                showCameraError(
                    'Kamera gagal diakses. Izinkan akses kamera di browser, lalu muat ulang halaman.'
                );
            }

            updateButtons();
        }

        // Menampilkan koordinat GPS dan tautan Google Maps.
        function tampilkanKoordinat(latitude, longitude) {
            if (koordinatGPS) {
                koordinatGPS.textContent =
                    latitude.toFixed(7) + ', ' +
                    longitude.toFixed(7);
            }

            if (linkPetaGPS) {
                linkPetaGPS.href =
                    'https://www.google.com/maps?q=' +
                    latitude + ',' + longitude;

                linkPetaGPS.classList.remove('hidden');
            }
        }

        // Mengambil lokasi perangkat untuk tampilan awal.
        function getGPS() {
            if (!navigator.geolocation) {
                gpsText.textContent = 'Browser tidak mendukung GPS';

                if (koordinatGPS) {
                    koordinatGPS.textContent = 'GPS tidak didukung';
                }

                gpsDot.className =
                    'h-2 w-2 rounded-full bg-red-500';

                return;
            }

            gpsReady = false;
            updateButtons();

            gpsText.textContent = 'Mencari lokasi...';

            gpsDot.className =
                'h-2 w-2 animate-pulse rounded-full bg-amber-500';

            if (koordinatGPS) {
                koordinatGPS.textContent = 'Mendeteksi lokasi...';
            }

            navigator.geolocation.getCurrentPosition(

                position => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    document.getElementById('inpLat').value = latitude;
                    document.getElementById('inpLng').value = longitude;

                    tampilkanKoordinat(latitude, longitude);
                    tampilkanAkurasi(position.coords.accuracy);
                    updateDistance(latitude, longitude);
                    simpanPosisiTerakhir(position);

                    gpsText.textContent = 'GPS Terdeteksi';

                    gpsDot.className =
                        'h-2 w-2 rounded-full bg-emerald-500';

                    gpsReady = true;

                    updateButtons();
                },

                error => {
                    gpsReady = false;

                    if (error.code === 1) {
                        gpsText.textContent = 'Akses GPS ditolak';

                        if (koordinatGPS) {
                            koordinatGPS.textContent = 'Izinkan akses lokasi';
                        }

                    } else if (error.code === 3) {
                        gpsText.textContent = 'GPS timeout';

                        if (koordinatGPS) {
                            koordinatGPS.textContent = 'Waktu deteksi habis';
                        }

                    } else {
                        gpsText.textContent = 'GPS gagal terdeteksi';

                        if (koordinatGPS) {
                            koordinatGPS.textContent = 'Lokasi belum terdeteksi';
                        }
                    }

                    if (linkPetaGPS) {
                        linkPetaGPS.classList.add('hidden');
                    }

                    gpsDot.className =
                        'h-2 w-2 rounded-full bg-red-500';

                    updateButtons();
                },

                {
                    enableHighAccuracy: true,
                    timeout: 20000,
                    maximumAge: 0
                }

            );
        }

        // AMBIL FOTO UNTUK PRATINJAU.
        btnAmbil.addEventListener('click', function () {

            if (!cameraReady || !gpsReady) {
                alert(
                    'Pastikan kamera dan GPS sudah siap sebelum mengambil foto.'
                );

                return;
            }

            if (!video.videoWidth || !video.videoHeight) {
                alert(
                    'Kamera belum siap. Silakan tunggu beberapa detik.'
                );

                return;
            }

            btnAmbil.disabled = true;
            btnAmbil.textContent = 'Memproses foto...';

            // Membatasi dimensi gambar agar ukuran file tidak terlalu besar.
            const sourceWidth = video.videoWidth;
            const sourceHeight = video.videoHeight;

            const scale = Math.min(
                1,
                1600 / Math.max(sourceWidth, sourceHeight)
            );

            canvas.width = Math.round(sourceWidth * scale);
            canvas.height = Math.round(sourceHeight * scale);

            const context = canvas.getContext('2d');

            if (!context) {
                btnAmbil.textContent = 'Ambil Foto';

                updateButtons();

                alert('Foto gagal diproses. Silakan coba lagi.');

                return;
            }

            // Menyesuaikan arah foto selfie dengan pratinjau kamera.
            context.save();
            context.translate(canvas.width, 0);
            context.scale(-1, 1);

            context.drawImage(
                video,
                0,
                0,
                canvas.width,
                canvas.height
            );

            context.restore();

            canvas.toBlob(blob => {

                if (!blob) {
                    btnAmbil.textContent = 'Ambil Foto';

                    updateButtons();

                    alert('Foto gagal dibuat. Silakan coba lagi.');

                    return;
                }

                try {
                    const photoFile = new File(
                        [blob],
                        'selfie.jpg',
                        {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        }
                    );

                    const transfer = new DataTransfer();

                    transfer.items.add(photoFile);
                    photoInput.files = transfer.files;

                    clearPreviewUrl();

                    previewUrl = URL.createObjectURL(blob);
                    previewFoto.src = previewUrl;

                    // Kamera dihentikan ketika pengguna memeriksa foto.
                    stopCamera();

                    video.classList.add('hidden');
                    previewFoto.classList.remove('hidden');

                    btnAmbil.classList.add('hidden');

                    btnUlangi.classList.remove('hidden');
                    btnGunakan.classList.remove('hidden');
                    btnKirim.classList.add('hidden');

                    btnUlangi.textContent = 'Ulangi Foto';

                    photoApproved = false;

                    panelPesanFoto.classList.remove('hidden');

                    pesanFoto.textContent =
                        'Periksa hasil foto. Pilih Ulangi Foto jika kurang sesuai, atau Gunakan Foto jika sudah benar.';

                    panelPesanFoto.className =
                        'mb-4 w-full rounded-xl border border-amber-200 bg-amber-50 p-4';

                    pesanFoto.className =
                        'mt-1 text-xs text-amber-700';

                    btnAmbil.textContent = 'Ambil Foto';

                    updateButtons();

                } catch (error) {

                    btnAmbil.textContent = 'Ambil Foto';

                    showCameraError(
                        'Foto gagal disiapkan. Silakan coba lagi.'
                    );

                    startCamera();
                }

            }, 'image/jpeg', 0.82);

        });

        // ULANGI FOTO.
        btnUlangi.addEventListener('click', function () {

            photoInput.value = '';
            photoApproved = false;

            clearPreviewUrl();

            previewFoto.src = '';

            previewFoto.classList.add('hidden');
            video.classList.remove('hidden');

            btnUlangi.classList.add('hidden');
            btnGunakan.classList.add('hidden');
            btnKirim.classList.add('hidden');

            btnAmbil.classList.remove('hidden');
            btnAmbil.textContent = 'Ambil Foto';
            btnAmbil.disabled = true;

            panelPesanFoto.classList.add('hidden');

            startCamera();
        });

        // GUNAKAN FOTO.
        btnGunakan.addEventListener('click', function () {

            if (!photoInput.files || !photoInput.files.length) {
                alert('Ambil foto terlebih dahulu.');

                return;
            }

            if (!gpsReady) {
                alert(
                    'Lokasi GPS belum tersedia. Tunggu sampai GPS terdeteksi.'
                );

                return;
            }

            photoApproved = true;

            btnGunakan.classList.add('hidden');

            // Foto tetap dapat diganti sebelum dikirim.
            btnUlangi.textContent = 'Ganti Foto';
            btnUlangi.classList.remove('hidden');

            btnKirim.classList.remove('hidden');

            panelPesanFoto.className =
                'mb-4 w-full rounded-xl border border-emerald-200 bg-emerald-50 p-4';

            pesanFoto.textContent =
                'Foto sudah dipilih. Periksa kembali sebelum mengirim absensi.';

            pesanFoto.className =
                'mt-1 text-xs text-emerald-700';

            updateButtons();
        });

        // KIRIM ABSENSI DENGAN GPS TERBARU.
        btnKirim.addEventListener('click', function () {

            if (submitting) {
                return;
            }

            if (!photoApproved) {
                alert(
                    'Silakan ambil dan setujui foto terlebih dahulu.'
                );

                return;
            }

            if (!photoInput.files || !photoInput.files.length) {
                alert(
                    'Foto belum tersedia. Silakan ambil foto kembali.'
                );

                return;
            }

            // Memeriksa field wajib sebelum mengirim.
            if (!formAbsen.reportValidity()) {
                return;
            }

            if (!navigator.geolocation) {
                alert(
                    'Browser tidak mendukung GPS. Gunakan browser yang mendukung lokasi.'
                );

                return;
            }

            const originalButtonText = btnKirim.textContent;

            btnKirim.disabled = true;
            btnKirim.textContent = 'Memperbarui GPS...';

            // Memvalidasi posisi lalu mengirim form absensi.
            function kirimDenganPosisi(position) {

                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;

                if (
                    !Number.isFinite(latitude) ||
                    !Number.isFinite(longitude)
                ) {
                    btnKirim.disabled = false;
                    btnKirim.textContent = originalButtonText;

                    alert(
                        'Koordinat GPS tidak valid. Silakan coba kembali.'
                    );

                    return;
                }

                // Memperbarui koordinat yang akan dikirim.
                document.getElementById('inpLat').value = latitude;
                document.getElementById('inpLng').value = longitude;

                tampilkanKoordinat(latitude, longitude);
                tampilkanAkurasi(position.coords.accuracy);
                updateDistance(latitude, longitude);

                // Posisi yang dipakai ulang tidak dihitung sebagai bacaan baru.
                if (position !== posisiTerakhir) {
                    simpanPosisiTerakhir(position);
                }

                gpsReady = true;

                gpsText.textContent = 'GPS Terbaru Terdeteksi';

                gpsDot.className =
                    'h-2 w-2 rounded-full bg-emerald-500';

                // Tolak lebih awal jika posisi terbaru di luar radius (server tetap memvalidasi ulang).
                if (!isPositionAllowed()) {
                    btnKirim.textContent = originalButtonText;
                    updateButtons();

                    alert(
                        'Posisi Anda ' + lastDistance + ' meter dari ' + geofence.name +
                        ', di luar radius ' + geofence.radius + ' meter. Mendekatlah ke lokasi lalu coba lagi.'
                    );

                    return;
                }

                // Validasi ulang seluruh field form.
                if (!formAbsen.reportValidity()) {
                    btnKirim.disabled = false;

                    updateButtons();

                    return;
                }

                // Mencegah pengiriman ganda melalui browser.
                submitting = true;

                btnKirim.disabled = true;
                btnKirim.textContent = 'Mengirim Absensi...';

                formAbsen.submit();
            }

            // Posisi dari pemantauan GPS yang masih segar dipakai langsung.
            const posisiSegar = posisiTerakhirBerumurPaling(UMUR_POSISI_SEGAR_MS);

            if (posisiSegar) {
                kirimDenganPosisi(posisiSegar);

                return;
            }

            // Jika tidak ada, ambil posisi baru tepat sebelum mengirim ke server.
            navigator.geolocation.getCurrentPosition(

                kirimDenganPosisi,

                function (error) {

                    // Timeout tetapi masih ada posisi yang baru terbaca: pakai itu (server tetap memvalidasi jarak).
                    const posisiCadangan = posisiTerakhirBerumurPaling(UMUR_POSISI_CADANGAN_MS);

                    if (error.code === 3 && posisiCadangan) {
                        kirimDenganPosisi(posisiCadangan);

                        return;
                    }

                    btnKirim.disabled = false;
                    btnKirim.textContent = originalButtonText;

                    let pesan = 'Lokasi GPS gagal diperbarui.';

                    if (error.code === 1) {
                        pesan =
                            'Akses lokasi ditolak. Izinkan browser menggunakan lokasi perangkat.';

                        gpsText.textContent = 'Akses GPS ditolak';

                    } else if (error.code === 2) {
                        pesan =
                            'Lokasi perangkat tidak tersedia. Periksa GPS perangkat.';

                        gpsText.textContent = 'GPS tidak tersedia';

                    } else if (error.code === 3) {
                        pesan =
                            'Pengambilan GPS melewati batas waktu. Silakan coba kembali.';

                        gpsText.textContent = 'GPS timeout';
                    }

                    alert(
                        pesan +
                        '\nAbsensi belum dikirim. Silakan coba kembali.'
                    );
                },

                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: UMUR_POSISI_SEGAR_MS
                }

            );
        });

        // Pantau posisi terus-menerus agar jarak & tombol Kirim ikut berubah saat pengguna bergerak.
        let gpsWatchId = null;

        function startGpsWatch() {
            if (!navigator.geolocation || gpsWatchId !== null) {
                return;
            }

            gpsWatchId = navigator.geolocation.watchPosition(
                position => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                        return;
                    }

                    document.getElementById('inpLat').value = latitude;
                    document.getElementById('inpLng').value = longitude;

                    tampilkanKoordinat(latitude, longitude);
                    tampilkanAkurasi(position.coords.accuracy);
                    updateDistance(latitude, longitude);
                    simpanPosisiTerakhir(position);

                    gpsReady = true;
                    updateButtons();
                },
                () => {
                    // Kegagalan watch tidak fatal; status awal tetap dari getGPS().
                },
                {
                    enableHighAccuracy: true,
                    timeout: 20000,
                    maximumAge: 5000
                }
            );
        }

        function stopGpsWatch() {
            if (gpsWatchId !== null) {
                navigator.geolocation.clearWatch(gpsWatchId);
                gpsWatchId = null;
            }
        }

        // AKTIFKAN KAMERA DAN GPS.
        startCamera();
        getGPS();
        startGpsWatch();

        // Hentikan kamera & GPS jika halaman ditinggalkan.
        window.addEventListener('pagehide', function () {
            stopCamera();
            stopGpsWatch();
        });

    @endif
</script>

</body>
</html>
