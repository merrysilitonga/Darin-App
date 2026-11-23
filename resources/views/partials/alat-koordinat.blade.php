{{--
    Alat bantu pengisian koordinat lokasi kantor (halaman Admin > Lokasi Kerja).
    Variabel: $latId, $lngId = id input latitude & longitude yang akan diisi.

    1. "Tempel koordinat": terima teks dari Google Maps, mis. "-6.18234, 106.82956"
       atau tautan Google Maps yang berisi "@-6.18,106.82" / "q=-6.18,106.82".
    2. "Gunakan lokasi saya sekarang": isi dari GPS perangkat (paling akurat dari HP di lokasi kantor).
--}}
<div data-alat-koordinat data-lat="{{ $latId }}" data-lng="{{ $lngId }}" class="space-y-3 rounded-xl border border-dashed border-gray-200 bg-gray-50 p-3">

    <div>
        <label for="{{ $latId }}-tempel" class="field-label">
            Tempel Koordinat dari Google Maps
        </label>

        <div class="flex gap-2">
            <input
                type="text"
                id="{{ $latId }}-tempel"
                data-koordinat-tempel
                class="form-input"
                placeholder="-6.182340, 106.829560"
                autocomplete="off"
            >

            <button
                type="button"
                data-koordinat-terapkan
                class="shrink-0 rounded-xl border border-gray-200 bg-white px-3 text-xs font-bold text-gray-700 hover:bg-gray-100"
            >
                Terapkan
            </button>
        </div>

        <p class="mt-1.5 text-[11px] leading-5 text-gray-400">
            Di Google Maps, klik kanan tepat di gedung kantor, lalu klik angka koordinat paling atas untuk menyalinnya.
        </p>
    </div>

    <button
        type="button"
        data-koordinat-gps
        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-100"
    >
        Gunakan Lokasi Saya Sekarang
    </button>

    <p data-koordinat-pesan class="hidden text-[11px] leading-5"></p>

    <a
        data-koordinat-peta
        href="#"
        target="_blank"
        rel="noopener noreferrer"
        class="hidden text-[11px] font-semibold text-blue-600 hover:underline"
    >
        Cek titik ini di Google Maps
    </a>
</div>

@once
<script>
    (function () {
        // Di atas angka ini (meter), lokasi dari perangkat dianggap kurang akurat.
        const BATAS_AKURASI_METER = 100;

        function ambilAlat(elemen) {
            const alat = elemen.closest('[data-alat-koordinat]');

            return {
                alat: alat,
                lat: document.getElementById(alat.dataset.lat),
                lng: document.getElementById(alat.dataset.lng),
                tempel: alat.querySelector('[data-koordinat-tempel]'),
                pesan: alat.querySelector('[data-koordinat-pesan]'),
                peta: alat.querySelector('[data-koordinat-peta]'),
            };
        }

        function tampilkanPesan(bagian, teks, jenis) {
            const warna = {
                sukses: 'text-emerald-600',
                peringatan: 'text-amber-600',
                galat: 'text-red-600',
                info: 'text-gray-500',
            };

            bagian.pesan.textContent = teks;
            bagian.pesan.className = 'text-[11px] leading-5 ' + (warna[jenis] || warna.info);
        }

        function isiKoordinat(bagian, lat, lng) {
            bagian.lat.value = lat.toFixed(7);
            bagian.lng.value = lng.toFixed(7);

            bagian.peta.href = 'https://www.google.com/maps?q=' + lat.toFixed(7) + ',' + lng.toFixed(7);
            bagian.peta.classList.remove('hidden');
        }

        // Ambil pasangan angka desimal pertama dari teks/tautan Google Maps.
        function uraikanKoordinat(teks) {
            const cocok = String(teks).match(/(-?\d{1,3}\.\d+)\s*[,;\s]\s*(-?\d{1,3}\.\d+)/);

            if (!cocok) {
                return null;
            }

            let lat = parseFloat(cocok[1]);
            let lng = parseFloat(cocok[2]);
            let ditukar = false;

            // Urutan terbalik (longitude ditulis duluan): tukar otomatis.
            if (Math.abs(lat) > 90 && Math.abs(lng) <= 90) {
                [lat, lng] = [lng, lat];
                ditukar = true;
            }

            if (Math.abs(lat) > 90 || Math.abs(lng) > 180) {
                return null;
            }

            return { lat: lat, lng: lng, ditukar: ditukar };
        }

        function terapkanTempel(tombol) {
            const bagian = ambilAlat(tombol);
            const hasil = uraikanKoordinat(bagian.tempel.value);

            bagian.pesan.classList.remove('hidden');

            if (!hasil) {
                tampilkanPesan(bagian, 'Format koordinat tidak dikenali. Contoh yang benar: -6.182340, 106.829560', 'galat');

                return;
            }

            isiKoordinat(bagian, hasil.lat, hasil.lng);
            tampilkanPesan(
                bagian,
                hasil.ditukar
                    ? 'Urutan angka terbalik, sudah ditukar otomatis. Latitude dan longitude terisi.'
                    : 'Latitude dan longitude terisi. Cek titiknya di Google Maps sebelum menyimpan.',
                hasil.ditukar ? 'peringatan' : 'sukses'
            );
        }

        function gunakanGps(tombol) {
            const bagian = ambilAlat(tombol);
            const teksAwal = tombol.textContent;

            bagian.pesan.classList.remove('hidden');

            if (!navigator.geolocation) {
                tampilkanPesan(bagian, 'Browser ini tidak mendukung GPS.', 'galat');

                return;
            }

            tombol.disabled = true;
            tombol.textContent = 'Mencari lokasi...';
            tampilkanPesan(bagian, 'Izinkan akses lokasi jika browser meminta.', 'info');

            navigator.geolocation.getCurrentPosition(
                function (posisi) {
                    tombol.disabled = false;
                    tombol.textContent = teksAwal;

                    const akurasi = Math.round(posisi.coords.accuracy);

                    isiKoordinat(bagian, posisi.coords.latitude, posisi.coords.longitude);

                    if (akurasi > BATAS_AKURASI_METER) {
                        tampilkanPesan(
                            bagian,
                            'Terisi, tetapi akurasinya rendah (±' + akurasi.toLocaleString('id-ID') + ' m). '
                                + 'Sebaiknya ulangi dari HP dengan GPS aktif saat berada di gedung kantor.',
                            'peringatan'
                        );
                    } else {
                        tampilkanPesan(bagian, 'Terisi dengan akurasi ±' + akurasi + ' m.', 'sukses');
                    }
                },
                function (galat) {
                    tombol.disabled = false;
                    tombol.textContent = teksAwal;

                    const pesan = {
                        1: 'Akses lokasi ditolak. Izinkan lokasi di pengaturan browser.',
                        2: 'Lokasi tidak dapat ditentukan. Pastikan GPS/lokasi perangkat aktif.',
                        3: 'Waktu mencari lokasi habis. Coba lagi.',
                    };

                    tampilkanPesan(bagian, pesan[galat.code] || 'Gagal mengambil lokasi.', 'galat');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 20000,
                    maximumAge: 0,
                }
            );
        }

        document.addEventListener('click', function (event) {
            const terapkan = event.target.closest('[data-koordinat-terapkan]');
            const gps = event.target.closest('[data-koordinat-gps]');

            if (terapkan) {
                terapkanTempel(terapkan);
            } else if (gps) {
                gunakanGps(gps);
            }
        });

        // Enter di kolom tempel = Terapkan (jangan sampai mengirim form).
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && event.target.matches('[data-koordinat-tempel]')) {
                event.preventDefault();
                terapkanTempel(event.target);
            }
        });
    })();
</script>
@endonce
