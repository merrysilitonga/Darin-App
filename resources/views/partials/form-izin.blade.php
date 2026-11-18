{{--
    Form pengajuan izin/sakit mahasiswa.
    Dipasang di halaman Mulai Absen. Status pengajuan dilihat di Riwayat Absensi & Izin.
    Butuh variabel $user (mahasiswa yang login).
--}}
@php
    $magangSelesai = isset($user) && $user->isInternshipEnded();
    // Penanda "_form=izin" membedakan error form ini dari error form absensi.
    $formIzinGagal = old('_form') === 'izin';
    $bukaFormIzin = request()->boolean('ajukan') || $formIzinGagal;
    $batasMulaiIzin = $user?->getRawOriginal('start_date') ? \Carbon\Carbon::parse($user->getRawOriginal('start_date'))->toDateString() : null;
    $batasSelesaiIzin = $user?->getRawOriginal('end_date') ? \Carbon\Carbon::parse($user->getRawOriginal('end_date'))->toDateString() : null;
@endphp

<details id="form-izin" class="scroll-mt-6 bg-white rounded-3xl shadow-sm border border-gray-100 mt-8 group" @if ($bukaFormIzin) open @endif>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-6">
        <div>
            <h3 class="font-bold text-gray-800">Tidak bisa hadir? Ajukan Izin / Sakit</h3>
            <p class="text-xs text-gray-500 mt-1">Isi form dan unggah surat pendukung. Pengajuan akan diperiksa Mentor.</p>
        </div>
        <span class="shrink-0 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-gray-600 group-open:hidden">Buka Form</span>
        <span class="hidden shrink-0 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-gray-600 group-open:inline">Tutup</span>
    </summary>

    <div class="border-t border-gray-100 p-6">
        @if ($magangSelesai)
            <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Periode magang Anda telah berakhir, sehingga tidak dapat mengajukan izin baru.
            </p>
        @else
            @if ($formIzinGagal && $errors->any())
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
                <input type="hidden" name="_form" value="izin">

                <div class="md:col-span-2">
                    <span class="mb-2 block text-sm font-semibold text-gray-700">Jenis Pengajuan <span class="text-red-500">*</span></span>
                    <div class="grid grid-cols-2 gap-3 md:max-w-sm">
                        @foreach (['Sakit' => 'Sakit', 'Izin' => 'Izin'] as $nilaiJenis => $labelJenis)
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
                    <p class="text-xs text-gray-400">
                        Status pengajuan dapat dilihat di
                        <a href="{{ route('riwayat.absen') }}#riwayat-izin" class="font-semibold text-blue-600 hover:text-blue-800">Riwayat Absensi &amp; Izin</a>.
                    </p>
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

        // Tautan .../absen#form-izin langsung membuka form dan menggulir ke sana.
        if (location.hash === '#form-izin') {
            panel.open = true;
            panel.scrollIntoView({ block: 'start' });
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
