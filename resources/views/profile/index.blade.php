<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - DARIN</title>

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

    {{-- SIDEBAR --}}
    @include('layouts.sidebar')

    {{-- KONTEN UTAMA --}}
    <main class="flex-1 min-w-0 overflow-y-auto p-5 md:p-10">
        <div class="max-w-7xl mx-auto">

            {{-- HEADER --}}
            <header class="mb-8">
                <h1 class="text-2xl font-bold tracking-tight">
                    Pengaturan Profil & Akademik
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Informasi data diri, data akademik, dan status peserta magang.
                </p>

                <div class="w-16 h-1 bg-[#ffc107] mt-3 rounded-full"></div>
            </header>

            {{-- PESAN BERHASIL --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- PESAN ERROR --}}
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- VALIDASI --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                    <p class="font-bold mb-2">
                        Periksa kembali data yang kamu masukkan:
                    </p>

                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $tanggalMulai = $user->start_date
                    ? \Carbon\Carbon::parse($user->start_date)
                    : null;

                $tanggalSelesai = $user->end_date
                    ? \Carbon\Carbon::parse($user->end_date)
                    : null;

                $hariIni = now()->startOfDay();

                if (!$tanggalMulai || !$tanggalSelesai) {
                    $statusMagang = 'Periode Belum Diatur';
                    $statusClass = 'bg-gray-100 text-gray-600';
                } elseif ($hariIni->lt($tanggalMulai->copy()->startOfDay())) {
                    $statusMagang = 'Belum Dimulai';
                    $statusClass = 'bg-yellow-50 text-yellow-700';
                } elseif ($hariIni->gt($tanggalSelesai->copy()->endOfDay())) {
                    $statusMagang = 'Selesai';
                    $statusClass = 'bg-gray-100 text-gray-600';
                } else {
                    $statusMagang = 'Aktif / Sedang Berjalan';
                    $statusClass = 'bg-emerald-50 text-emerald-700';
                }
            @endphp

            {{-- FORM EDIT PROFIL --}}
            <form
                action="{{ route('profil.update') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {{-- KOLOM KIRI DAN TENGAH --}}
                    <div class="lg:col-span-2 space-y-8">

                        {{-- FOTO PROFIL --}}
                        <section class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
                            <div class="flex flex-col sm:flex-row items-center gap-6">

                                <div class="w-24 h-24 rounded-full border-4 border-amber-200 flex items-center justify-center bg-gray-100 text-4xl shadow-inner overflow-hidden flex-shrink-0">

                                    <img
                                        id="photoPreview"
                                        src="{{ $user->photo ? asset('storage/' . $user->photo) : '' }}"
                                        alt="Foto profil"
                                        class="w-full h-full object-cover {{ $user->photo ? '' : 'hidden' }}"
                                    >

                                    <div
                                        id="photoFallback"
                                        class="w-full h-full items-center justify-center {{ $user->photo ? 'hidden' : 'flex' }}"
                                    >
                                        👤
                                    </div>
                                </div>

                                <div class="flex-1 w-full text-center sm:text-left">
                                    <h2 class="font-bold text-lg mb-1">
                                        Foto Profil
                                    </h2>

                                    <p class="text-xs text-gray-400 mb-4">
                                        Pilih foto untuk memperbarui foto profil akun Anda.
                                    </p>

                                    <label
                                        for="photo"
                                        class="block text-xs font-bold text-gray-600 mb-2"
                                    >
                                        Unggah Foto Baru
                                    </label>

                                    <input
                                        type="file"
                                        id="photo"
                                        name="photo"
                                        accept=".jpg,.jpeg,.png,.webp"
                                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200"
                                    >

                                    <p class="text-xs text-gray-400 mt-2">
                                        JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                                    </p>

                                    @error('photo')
                                        <p class="text-xs text-red-600 mt-2">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                            </div>
                        </section>

                        {{-- INFORMASI DATA DIRI --}}
                        <section class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">

                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="font-bold text-lg">
                                        Informasi Data Diri
                                    </h2>

                                    <p class="text-xs text-gray-400 mt-1">
                                        Perbarui informasi pribadi Anda.
                                    </p>
                                </div>

                                <span class="p-3 bg-amber-50 rounded-2xl text-xl">
                                    👤
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                                {{-- NAMA --}}
                                <div>
                                    <label for="name" class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Nama Lengkap
                                    </label>

                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        value="{{ old('name', $user->name) }}"
                                        maxlength="255"
                                        required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-300"
                                    >

                                    @error('name')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- NIM --}}
                                <div>
                                    <label for="nim" class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Nomor Induk Mahasiswa / NIM
                                    </label>

                                    <input
                                        id="nim"
                                        type="text"
                                        name="nim"
                                        value="{{ old('nim', $user->nim) }}"
                                        maxlength="50"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-300"
                                    >

                                    @error('nim')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- EMAIL --}}
                                <div>
                                    <label for="email" class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Email Aktif
                                    </label>

                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $user->email) }}"
                                        maxlength="255"
                                        required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-300"
                                    >

                                    @error('email')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- NOMOR TELEPON --}}
                                <div>
                                    <label for="phone" class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Nomor Telepon / WhatsApp
                                    </label>

                                    <input
                                        id="phone"
                                        type="text"
                                        name="phone"
                                        value="{{ old('phone', $user->phone) }}"
                                        maxlength="30"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-300"
                                    >

                                    @error('phone')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </section>

                        {{-- INFORMASI AKADEMIK --}}
                        <section class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">

                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="font-bold text-lg">
                                        Informasi Akademik & Penempatan
                                    </h2>

                                    <p class="text-xs text-gray-400 mt-1">
                                        Perbarui data perguruan tinggi. Periode magang ditetapkan oleh Admin/HR.
                                    </p>
                                </div>

                                <span class="p-3 bg-blue-50 rounded-2xl text-xl">
                                    🎓
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                                {{-- UNIVERSITAS --}}
                                <div>
                                    <label for="university" class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Perguruan Tinggi
                                    </label>

                                    <input
                                        id="university"
                                        type="text"
                                        name="university"
                                        value="{{ old('university', $user->university) }}"
                                        maxlength="255"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-300"
                                    >

                                    @error('university')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- PROGRAM STUDI --}}
                                <div>
                                    <label for="study_program" class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Program Studi / Jurusan
                                    </label>

                                    <input
                                        id="study_program"
                                        type="text"
                                        name="study_program"
                                        value="{{ old('study_program', $user->study_program) }}"
                                        maxlength="255"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-300"
                                    >

                                    @error('study_program')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- TANGGAL MULAI: INFORMASI BACA-SAJA --}}
                                <div>
                                    <p class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Mulai Magang
                                    </p>

                                    <div
                                        id="start_date"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 min-h-[48px] flex items-center"
                                        aria-readonly="true"
                                    >
                                        {{ $tanggalMulai ? $tanggalMulai->format('d-m-Y') : 'Belum ditetapkan' }}
                                    </div>
                                </div>

                                {{-- TANGGAL SELESAI: INFORMASI BACA-SAJA --}}
                                <div>
                                    <p class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Selesai Magang
                                    </p>

                                    <div
                                        id="end_date"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 min-h-[48px] flex items-center"
                                        aria-readonly="true"
                                    >
                                        {{ $tanggalSelesai ? $tanggalSelesai->format('d-m-Y') : 'Belum ditetapkan' }}
                                    </div>
                                </div>

                                {{-- STATUS MAGANG --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-2">
                                        Status Keaktifan
                                    </label>

                                    <div class="w-full px-4 py-3 rounded-xl border border-gray-200 {{ $statusClass }} font-bold">
                                        {{ $statusMagang }}
                                    </div>

                                    <p class="text-xs text-gray-400 mt-2">
                                        Status ditentukan secara otomatis berdasarkan periode magang yang tersimpan.
                                    </p>
                                </div>

                            </div>
                        </section>

                    </div>

                    {{-- KOLOM KANAN --}}
                    <div class="space-y-8">

                        {{-- RINGKASAN AKUN --}}
                        <section class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">

                            <div class="flex items-center justify-between mb-6">
                                <h2 class="font-bold text-base">
                                    Ringkasan Akun
                                </h2>

                                <span class="p-3 bg-amber-50 rounded-2xl text-lg">
                                    🪪
                                </span>
                            </div>

                            <div class="space-y-5">

                                <div>
                                    <p class="text-xs font-bold uppercase text-gray-400">
                                        Role
                                    </p>

                                    <p class="font-semibold text-sm mt-1">
                                        @if(in_array(strtolower(trim((string) $user->role)), ['admin', 'hr'], true))
                                            Admin / HR
                                        @elseif(strtolower(trim((string) $user->role)) === 'mentor')
                                            Mentor / Pembimbing
                                        @elseif(strtolower(trim((string) $user->role)) === 'magang')
                                            Peserta Magang
                                        @else
                                            Role tidak dikenali
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase text-gray-400">
                                        Email
                                    </p>

                                    <p class="font-semibold text-sm mt-1 break-all">
                                        {{ old('email', $user->email) ?: '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase text-gray-400">
                                        NIM
                                    </p>

                                    <p class="font-semibold text-sm mt-1 break-words">
                                        {{ old('nim', $user->nim) ?: '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase text-gray-400">
                                        Universitas
                                    </p>

                                    <p class="font-semibold text-sm mt-1">
                                        {{ old('university', $user->university) ?: '-' }}
                                    </p>
                                </div>

                            </div>
                        </section>

                        {{-- KEAMANAN AKUN --}}
                        <section class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">

                            <div class="flex items-center justify-between mb-6">
                                <h2 class="font-bold text-base">
                                    Keamanan Akun
                                </h2>

                                <span class="p-3 bg-red-50 rounded-2xl text-lg">
                                    🔒
                                </span>
                            </div>

                            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-800 leading-relaxed">
                                💡
                                <strong>Tips Keamanan:</strong>
                                Gunakan password yang kuat dan jangan membagikan password kepada orang lain.
                            </div>

                            <p class="text-xs text-gray-400 mt-4">
                                Perubahan password tidak dilakukan melalui formulir ini.
                            </p>
                        </section>

                    </div>

                </div>

                {{-- TOMBOL AKSI --}}
                <div class="mt-8 flex flex-col-reverse sm:flex-row justify-end gap-3">

                    <a
                        href="{{ route('profil') }}"
                        class="px-6 py-3 text-center rounded-xl border border-gray-200 bg-white text-gray-600 font-semibold hover:bg-gray-50 transition"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="px-6 py-3 rounded-xl bg-[#ffc107] hover:bg-amber-500 text-gray-900 font-bold shadow-sm transition"
                    >
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>
    </main>

    {{-- PREVIEW FOTO SEBELUM DISIMPAN --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photoInput = document.getElementById('photo');
            const photoPreview = document.getElementById('photoPreview');
            const photoFallback = document.getElementById('photoFallback');
            let currentPreviewUrl = null;

            if (photoInput) {
                photoInput.addEventListener('change', function () {
                    const file = this.files && this.files[0];

                    if (!file) {
                        return;
                    }

                    if (!file.type.startsWith('image/')) {
                        alert('Silakan pilih file gambar.');
                        this.value = '';
                        return;
                    }

                    // Lepaskan URL preview sebelumnya agar tidak menumpuk di memori.
                    if (currentPreviewUrl) {
                        URL.revokeObjectURL(currentPreviewUrl);
                    }

                    currentPreviewUrl = URL.createObjectURL(file);
                    photoPreview.src = currentPreviewUrl;
                    photoPreview.classList.remove('hidden');
                    photoFallback.classList.add('hidden');
                });
            }

            window.addEventListener('beforeunload', function () {
                if (currentPreviewUrl) {
                    URL.revokeObjectURL(currentPreviewUrl);
                }
            });
        });
    </script>

</body>
</html>
