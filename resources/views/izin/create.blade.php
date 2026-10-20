<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pengajuan Izin - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fcfbf9;
        }
    </style>
</head>

<body class="text-gray-800">

<div class="flex min-h-screen">

    @include('layouts.sidebar')

    <main class="flex-1 min-w-0 overflow-y-auto p-5 md:p-10">

        <header class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight">
                Pengajuan Izin / Sakit
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Ajukan izin ketidakhadiran dan unggah surat pendukung
                untuk diperiksa oleh pihak terkait.
            </p>

            <div class="mt-3 h-1 w-16 rounded-full bg-amber-400"></div>
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

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="mb-2 font-semibold text-red-700">
                    Periksa kembali data pengajuan:
                </p>

                <ul class="list-inside list-disc space-y-1 text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="max-w-3xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm md:p-8">

            <form
                action="{{ route('izin.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                {{-- JENIS PENGAJUAN --}}
                <div class="mb-6">
                    <label
                        for="request_type"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Jenis Pengajuan
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="request_type"
                        name="request_type"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                    >
                        <option value="">Pilih jenis pengajuan</option>

                        <option value="Sakit"
                            @selected(old('request_type') === 'Sakit')>
                            Sakit
                        </option>

                        <option value="Izin"
                            @selected(old('request_type') === 'Izin')>
                            Izin
                        </option>
                    </select>

                    @error('request_type')
                        <p class="mt-2 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- TANGGAL MULAI DAN SELESAI --}}
                <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div>
                        <label
                            for="start_date"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Tanggal Mulai
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date') }}"
                            required
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                        >

                        @error('start_date')
                            <p class="mt-2 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="end_date"
                            class="mb-2 block text-sm font-semibold text-gray-700"
                        >
                            Tanggal Selesai
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ old('end_date') }}"
                            required
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                        >

                        @error('end_date')
                            <p class="mt-2 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                {{-- ALASAN --}}
                <div class="mb-6">
                    <label
                        for="reason"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Alasan Pengajuan
                        <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        id="reason"
                        name="reason"
                        rows="5"
                        maxlength="2000"
                        required
                        placeholder="Jelaskan alasan pengajuan izin atau sakit..."
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                    >{{ old('reason') }}</textarea>

                    <p class="mt-2 text-xs text-gray-400">
                        Maksimal 2.000 karakter.
                    </p>

                    @error('reason')
                        <p class="mt-2 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- SURAT PENDUKUNG --}}
                <div class="mb-8">
                    <label
                        for="document"
                        class="mb-2 block text-sm font-semibold text-gray-700"
                    >
                        Surat Pendukung
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="file"
                        id="document"
                        name="document"
                        required
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-600 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-3 file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                    >

                    <p class="mt-2 text-xs leading-relaxed text-gray-500">
                        Format yang diperbolehkan PDF, JPG, JPEG, atau PNG.
                        Ukuran maksimal 2 MB.
                    </p>

                    @error('document')
                        <p class="mt-2 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- TOMBOL --}}
                <div class="flex flex-col gap-3 sm:flex-row">

                    <button
                        type="submit"
                        class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#191712] px-5 py-3 text-sm font-semibold text-white transition hover:bg-gray-800"
                    >
                        Kirim Pengajuan
                    </button>

                    <a
                        href="{{ route('izin.history') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Lihat Riwayat
                    </a>

                </div>

                <p class="mt-5 text-xs leading-relaxed text-gray-400">
                    Pengajuan akan berstatus Menunggu setelah berhasil
                    disimpan. Persetujuan harus diproses oleh pihak yang
                    memiliki kewenangan.
                </p>

            </form>

        </div>

    </main>

</div>

<script>
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    startDate.addEventListener('change', function () {
        endDate.min = this.value;

        if (endDate.value && endDate.value < this.value) {
            endDate.value = this.value;
        }
    });

    if (startDate.value) {
        endDate.min = startDate.value;
    }
</script>

</body>
</html>