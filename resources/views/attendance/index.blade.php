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
<body class="text-gray-800 flex min-h-screen">
    {{-- SIDEBAR --}}
    @include('layouts.sidebar')

    @php
        $attendanceComplete = $attendance && $attendance->clock_out;
    @endphp

    <main class="flex-1 min-w-0 overflow-y-auto p-5 md:p-10">
        {{-- HEADER --}}
        <header class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight">
                Portal Absensi Harian
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Lakukan absensi harian dengan verifikasi foto selfie dan lokasi GPS.
            </p>
            <div class="w-16 h-1 bg-[#ffc107] mt-2 rounded-full"></div>
        </header>

        {{-- PESAN SUKSES --}}
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl">
                ✅ {{ session('success') }}
            </div>
        @endif

        {{-- PESAN ERROR --}}
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        {{-- VALIDASI --}}
        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl">
                <p class="font-bold mb-2">Terjadi kesalahan:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- SECTION ABSENSI --}}
        <div id="section-absen" class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- KAMERA DAN GPS --}}
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col items-center">
                <h3 class="font-bold text-gray-700 mb-4 self-start">
                    {{ $attendanceComplete ? 'Absensi Hari Ini Selesai' : ($attendance ? 'Selfie & GPS untuk Absen Pulang' : 'Kamera & Verifikasi Lokasi GPS') }}
                </h3>

                @if($attendanceComplete)
                    <div class="w-full max-w-sm mx-auto aspect-[3/4] bg-emerald-50 rounded-2xl flex flex-col items-center justify-center text-emerald-700 border border-emerald-200">
                        <span class="text-4xl mb-2">✅</span>
                        <span class="text-sm font-bold">
                            Anda sudah absen hari ini
                        </span>
                        <span class="text-xs text-emerald-600 mt-2">
                            Jam masuk:
                            {{ $attendance->clock_in
                                ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                                : '-' }} WIB
                        </span>
                    </div>

                @elseif(!$attendance && !$canClockInLocation)

                    <div class="w-full rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center">
                        <span class="text-3xl">📍</span>
                        <h4 class="mt-3 font-bold text-amber-900">Lokasi Kantor Belum Tersedia</h4>
                        <p class="mt-2 text-sm leading-6 text-amber-800">
                            Akun Anda belum ditetapkan ke lokasi kantor yang aktif. Hubungi Admin/HR
                            agar lokasi dan radius absensi ditetapkan sebelum melakukan absensi masuk.
                        </p>
                    </div>

                @else

                    {{-- AREA KAMERA --}}
                    <div class="w-full max-w-sm mx-auto aspect-[3/4] bg-gray-900 rounded-2xl relative overflow-hidden mb-4 border border-gray-800">
                        <video
                            id="video"
                            autoplay
                            playsinline
                            muted
                            class="absolute inset-0 w-full h-full object-cover"
                            style="transform:scaleX(-1);"
                        ></video>

                        {{-- PRATINJAU FOTO --}}
                        <img
                            id="previewFoto"
                            src=""
                            alt="Pratinjau foto selfie absensi"
                            class="hidden absolute inset-0 w-full h-full object-contain"
                        >

                        <canvas id="canvas" class="hidden"></canvas>

                        {{-- STATUS GPS --}}
                        <div class="absolute bottom-3 left-3 bg-black/70 backdrop-blur-md px-3 py-2 rounded-lg text-[10px] text-amber-400 font-mono flex items-center gap-2">
                            <span
                                id="gpsDot"
                                class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"
                            ></span>
                            <span id="gpsText">
                                Mencari lokasi...
                            </span>
                        </div>
                    </div>

                    {{-- PESAN KAMERA --}}
                    <p
                        id="pesanKamera"
                        class="text-xs text-red-500 mb-3 hidden w-full"
                    ></p>

                    {{-- PESAN PRATINJAU --}}
                    <div
                        id="panelPesanFoto"
                        class="hidden w-full mb-4 p-4 rounded-xl border border-amber-200 bg-amber-50"
                    >
                        <p id="judulFoto" class="font-bold text-sm text-amber-800">
                            Periksa Foto Selfie
                        </p>
                        <p id="pesanFoto" class="text-xs text-amber-700 mt-1">
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

                        <input
                            type="hidden"
                            name="latitude"
                            id="inpLat"
                        >

                        <input
                            type="hidden"
                            name="longitude"
                            id="inpLng"
                        >

                        <input
                            type="file"
                            name="photo"
                            id="photoInput"
                            accept="image/jpeg"
                            class="hidden"
                        >

                        @if(!$attendance && $isFriday)
                            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <label for="work_location" class="mb-2 block text-sm font-bold text-amber-900">
                                    Metode Kerja Hari Jumat
                                </label>

                                <select
                                    id="work_location"
                                    name="work_location"
                                    required
                                    class="w-full rounded-lg border border-amber-200 bg-white px-3 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                                >
                                    <option value="">Pilih metode kerja</option>
                                    <option value="WFO" {{ old('work_location') === 'WFO' ? 'selected' : '' }}>
                                        WFO — Bekerja di kantor (wajib dalam radius)
                                    </option>
                                    <option value="WFA" {{ old('work_location') === 'WFA' ? 'selected' : '' }}>
                                        WFA — Bekerja dari lokasi lain
                                    </option>
                                </select>

                                <p class="mt-2 text-xs leading-5 text-amber-800">
                                    Pilihan WFO diverifikasi dengan radius kantor. Pilihan WFA tetap menyimpan koordinat GPS sebagai bukti.
                                </p>
                            </div>
                        @endif

                        {{-- AMBIL FOTO --}}
                        <button
                            type="button"
                            id="btnAmbil"
                            disabled
                            class="w-full py-3 bg-[#ffc107] text-black font-bold rounded-xl shadow-md hover:bg-[#e0a800] transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            📷 Ambil Foto
                        </button>

                        {{-- ULANGI / GUNAKAN FOTO --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                            <button
                                type="button"
                                id="btnUlangi"
                                class="hidden w-full py-3 bg-gray-800 text-white font-bold rounded-xl hover:bg-gray-700 transition"
                            >
                                ↻ Ulangi Foto
                            </button>

                            <button
                                type="button"
                                id="btnGunakan"
                                class="hidden w-full py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition"
                            >
                                ✓ Gunakan Foto
                            </button>
                        </div>

                        {{-- KIRIM ABSENSI --}}
                        <button
                            type="button"
                            id="btnKirim"
                            disabled
                            class="hidden w-full mt-3 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-md hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ➤ Kirim Absensi
                        </button>
                    </form>

                    <p class="text-xs text-gray-400 mt-4 text-center">
                        Foto hanya dikirim setelah Anda menyetujui hasil pratinjau.
                    </p>

                @endif
            </div>

            {{-- STATUS KEHADIRAN --}}
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-gray-700 mb-4">
                        Status Kehadiran Hari Ini
                    </h3>

                    @if($attendance)
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6">
                            <div class="flex items-center gap-3 text-emerald-800 font-bold text-sm mb-1">
                                ✅ Status Absensi: {{ $attendance->status }}
                            </div>
                            <p class="text-xs text-emerald-700 leading-relaxed">
                                {{ $attendance->notes ?? 'Absensi berhasil dicatat.' }}
                            </p>
                        </div>
                    @else
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6">
                            <div class="flex items-center gap-3 text-amber-800 font-bold text-sm mb-1">
                                ⚠️ Status Absensi
                            </div>
                            <p class="text-xs text-amber-700 leading-relaxed">
                                Ambil foto, periksa hasilnya, lalu kirim absensi untuk mencatat kehadiran Anda.
                            </p>
                        </div>
                    @endif

                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                            <span class="text-gray-400">Tanggal</span>
                            <span class="font-semibold text-gray-800 text-right">
                                {{ $now->translatedFormat('l, d F Y') }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                            <span class="text-gray-400">Nama</span>
                            <span class="font-semibold text-gray-800 text-right">
                                {{ $user->name ?? 'Pengguna' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                            <span class="text-gray-400">Email</span>
                            <span class="font-semibold text-gray-800 text-right break-all">
                                {{ $user->email ?? '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                            <span class="text-gray-400">Jam Masuk</span>
                            <span class="font-semibold text-gray-800">
                                {{ $attendance && $attendance->clock_in
                                    ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i:s')
                                    : '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                            <span class="text-gray-400">Jam Pulang</span>
                            <span class="font-semibold text-gray-800">
                                {{ $attendance && $attendance->clock_out
                                    ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i:s')
                                    : 'Belum absen pulang' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                            <span class="text-gray-400">Lokasi Kerja</span>
                            <span class="max-w-[65%] text-right font-semibold text-gray-800">
                                {{ $attendance->location_name ?? ($location->name ?? 'Belum ditetapkan') }}
                            </span>
                        </div>

                        @if($attendance)
                            <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                                <span class="text-gray-400">Jarak Saat Masuk</span>
                                <span class="font-semibold text-gray-800">
                                    {{ $attendance->distance_meters !== null ? $attendance->distance_meters . ' m' : '-' }}
                                </span>
                            </div>

                            @if($attendance->clock_out)
                                <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                                    <span class="text-gray-400">Jarak Saat Pulang</span>
                                    <span class="font-semibold text-gray-800">
                                        {{ $attendance->clock_out_distance_meters !== null ? $attendance->clock_out_distance_meters . ' m' : '-' }}
                                    </span>
                                </div>
                            @endif

                        @elseif($location)
                            <div class="flex justify-between gap-4 py-2 border-b border-gray-100">
                                <span class="text-gray-400">Radius Diizinkan</span>
                                <span class="font-semibold text-gray-800">
                                    {{ $location->radius_meters }} meter
                                </span>
                            </div>
                        @endif

                        {{-- KOORDINAT GPS --}}
                        <div class="py-2 border-b border-gray-100">
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400">Koordinat GPS</span>
                                <span
                                    id="koordinatGPS"
                                    class="font-mono text-xs text-gray-500 text-right break-all max-w-[65%]"
                                >
                                    @if(
                                        $attendance &&
                                        $attendance->latitude !== null &&
                                        $attendance->longitude !== null
                                    )
                                        {{ $attendance->latitude }}, {{ $attendance->longitude }}
                                    @else
                                        Mendeteksi lokasi...
                                    @endif
                                </span>
                            </div>

                            <a
                                id="linkPetaGPS"
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="hidden text-xs text-blue-600 hover:underline mt-2 text-right"
                            >
                                📍 Lihat di Google Maps
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    @if($attendanceComplete)
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                            <p class="font-bold">✅ Absensi hari ini lengkap</p>
                            <p class="mt-1 text-xs leading-5">
                                Foto dan koordinat GPS masuk serta pulang telah tercatat.
                            </p>
                        </div>
                    @elseif($attendance)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            <p class="font-bold">Absensi pulang belum tercatat</p>
                            <p class="mt-1 text-xs leading-5">
                                Ambil selfie baru dan izinkan GPS pada panel sebelah kiri untuk melakukan absensi pulang setelah jadwal tersedia.
                            </p>
                        </div>
                    @else
                        <p class="text-xs leading-5 text-gray-400">
                            Setelah absensi masuk tercatat, Anda dapat melakukan absensi pulang menggunakan selfie dan GPS terbaru.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </main>

    {{-- JAVASCRIPT --}}
    <script>
        // KAMERA, PRATINJAU FOTO, DAN GPS
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
            const gpsText = document.getElementById('gpsText');
            const gpsDot = document.getElementById('gpsDot');
            const koordinatGPS = document.getElementById('koordinatGPS');
            const linkPetaGPS = document.getElementById('linkPetaGPS');

            const pesanKamera = document.getElementById('pesanKamera');
            const panelPesanFoto = document.getElementById('panelPesanFoto');
            const pesanFoto = document.getElementById('pesanFoto');

            let cameraStream = null;
            let cameraReady = false;
            let gpsReady = false;
            let photoApproved = false;
            let previewUrl = null;
            let submitting = false;

            function updateButtons() {
                btnAmbil.disabled = !(cameraReady && gpsReady);
                btnKirim.disabled = !(photoApproved && gpsReady);
            }

            function showCameraError(message) {
                pesanKamera.textContent = message;
                pesanKamera.classList.remove('hidden');
            }

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

            function clearPreviewUrl() {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }
            }

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

            // Menampilkan koordinat GPS di halaman.
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
                    gpsText.textContent =
                        'Browser tidak mendukung GPS';

                    if (koordinatGPS) {
                        koordinatGPS.textContent =
                            'GPS tidak didukung';
                    }

                    gpsDot.className =
                        'w-2 h-2 rounded-full bg-red-500';

                    return;
                }

                gpsReady = false;
                updateButtons();

                gpsText.textContent = 'Mencari lokasi...';

                gpsDot.className =
                    'w-2 h-2 rounded-full bg-amber-500 animate-pulse';

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

                        gpsText.textContent = 'GPS Terdeteksi';

                        gpsDot.className =
                            'w-2 h-2 rounded-full bg-emerald-500';

                        gpsReady = true;
                        updateButtons();
                    },

                    error => {
                        gpsReady = false;

                        if (error.code === 1) {
                            gpsText.textContent = 'Akses GPS ditolak';

                            if (koordinatGPS) {
                                koordinatGPS.textContent =
                                    'Izinkan akses lokasi';
                            }
                        } else if (error.code === 3) {
                            gpsText.textContent = 'GPS timeout';

                            if (koordinatGPS) {
                                koordinatGPS.textContent =
                                    'Waktu deteksi habis';
                            }
                        } else {
                            gpsText.textContent =
                                'GPS gagal terdeteksi';

                            if (koordinatGPS) {
                                koordinatGPS.textContent =
                                    'Lokasi belum terdeteksi';
                            }
                        }

                        if (linkPetaGPS) {
                            linkPetaGPS.classList.add('hidden');
                        }

                        gpsDot.className =
                            'w-2 h-2 rounded-full bg-red-500';

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

                // Batasi dimensi agar ukuran file tidak terlalu besar.
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
                    btnAmbil.textContent = '📷 Ambil Foto';
                    updateButtons();

                    alert('Foto gagal diproses. Silakan coba lagi.');
                    return;
                }

                // Sesuaikan arah foto selfie dengan pratinjau kamera.
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
                        btnAmbil.textContent = '📷 Ambil Foto';
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

                        // Matikan kamera saat memeriksa hasil foto.
                        stopCamera();

                        video.classList.add('hidden');
                        previewFoto.classList.remove('hidden');

                        btnAmbil.classList.add('hidden');
                        btnUlangi.classList.remove('hidden');
                        btnGunakan.classList.remove('hidden');
                        btnKirim.classList.add('hidden');

                        btnUlangi.textContent = '↻ Ulangi Foto';

                        photoApproved = false;

                        panelPesanFoto.classList.remove('hidden');

                        pesanFoto.textContent =
                            'Periksa hasil foto. Pilih Ulangi Foto jika kurang sesuai, atau Gunakan Foto jika sudah benar.';

                        panelPesanFoto.className =
                            'w-full mb-4 p-4 rounded-xl border border-amber-200 bg-amber-50';

                        pesanFoto.className =
                            'text-xs text-amber-700 mt-1';

                        btnAmbil.textContent = '📷 Ambil Foto';

                        updateButtons();

                    } catch (error) {
                        btnAmbil.textContent = '📷 Ambil Foto';

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
                btnAmbil.textContent = '📷 Ambil Foto';
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

                // Foto masih dapat diganti sebelum dikirim.
                btnGunakan.classList.add('hidden');

                btnUlangi.textContent = '↻ Ganti Foto';
                btnUlangi.classList.remove('hidden');

                btnKirim.classList.remove('hidden');

                panelPesanFoto.className =
                    'w-full mb-4 p-4 rounded-xl border border-emerald-200 bg-emerald-50';

                pesanFoto.textContent =
                    'Foto sudah dipilih. Periksa kembali sebelum mengirim absensi.';

                pesanFoto.className =
                    'text-xs text-emerald-700 mt-1';

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

                // Pastikan field wajib valid sebelum mengambil GPS.
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

                // Ambil posisi baru tepat sebelum mengirim ke server.
                navigator.geolocation.getCurrentPosition(
                    function (position) {
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

                        // Perbarui koordinat yang akan dikirim.
                        document.getElementById('inpLat').value = latitude;
                        document.getElementById('inpLng').value = longitude;

                        tampilkanKoordinat(latitude, longitude);

                        gpsReady = true;

                        gpsText.textContent = 'GPS Terbaru Terdeteksi';

                        gpsDot.className =
                            'w-2 h-2 rounded-full bg-emerald-500';

                        // Validasi ulang semua field wajib.
                        if (!formAbsen.reportValidity()) {
                            btnKirim.disabled = false;
                            updateButtons();
                            return;
                        }

                        // Cegah pengiriman ganda.
                        submitting = true;

                        btnKirim.disabled = true;
                        btnKirim.textContent = 'Mengirim absensi...';

                        formAbsen.submit();
                    },

                    function (error) {
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
                        maximumAge: 0
                    }
                );
            });

            // Aktifkan kamera dan GPS.
            startCamera();
            getGPS();

            // Hentikan kamera jika halaman ditinggalkan.
            window.addEventListener('pagehide', stopCamera);
        @endif
    </script>
</body>
</html>
