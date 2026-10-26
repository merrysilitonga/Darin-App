<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Mahasiswa - DARIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#191712] text-white flex items-center justify-center min-h-screen p-4 overflow-y-auto">
    <main class="w-full max-w-lg bg-white text-gray-800 rounded-3xl shadow-2xl overflow-hidden relative border border-white/5 my-8">

        <div
            class="h-3 w-full"
            style="background: repeating-linear-gradient(45deg, #ffc107, #ffc107 15px, #191712 15px, #191712 30px);"
        ></div>

        <div class="p-8">
            <header class="flex flex-col items-center mb-6">
                <div class="bg-[#191712] text-[#ffc107] font-bold p-3 rounded-2xl flex items-center justify-center text-xl mb-3 shadow-lg">
                    ⚡
                </div>

                <h1 class="text-3xl font-extrabold tracking-wider text-black">
                    DARIN
                </h1>

                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mt-1 text-center">
                    PENDAFTARAN PESERTA MAGANG
                </p>

                <p class="text-xs text-gray-500 mt-1 text-center">
                    Buat akun mahasiswa untuk menggunakan sistem DARIN.
                </p>
            </header>

            @if ($errors->any())
                <div
                    role="alert"
                    class="mb-4 p-3 bg-red-100 border border-red-200 text-red-700 text-xs rounded-xl"
                >
                    <p class="font-bold mb-1">
                        Pendaftaran belum berhasil.
                    </p>

                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div
                    role="status"
                    class="mb-4 p-3 bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs rounded-xl"
                >
                    {{ session('status') }}
                </div>
            @endif

            <form
                action="{{ route('register') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4"
            >
                @csrf

                {{-- Role tidak dikirim dari form publik.
                     Backend menetapkan role magang. --}}

                <div>
                    <label
                        for="name"
                        class="block text-xs font-bold text-gray-700 mb-1.5"
                    >
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        maxlength="255"
                        required
                        autocomplete="name"
                        placeholder="Masukkan nama lengkap"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                    @error('name')
                        <p class="text-xs text-red-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="nim"
                            class="block text-xs font-bold text-gray-700 mb-1.5"
                        >
                            NIM
                        </label>

                        <input
                            type="text"
                            id="nim"
                            name="nim"
                            value="{{ old('nim') }}"
                            maxlength="50"
                            autocomplete="off"
                            placeholder="Nomor Induk Mahasiswa"
                            class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                        >

                        @error('nim')
                            <p class="text-xs text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="phone"
                            class="block text-xs font-bold text-gray-700 mb-1.5"
                        >
                            No. HP / WhatsApp
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            maxlength="30"
                            autocomplete="tel"
                            placeholder="Contoh: 08123456789"
                            class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                        >

                        @error('phone')
                            <p class="text-xs text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label
                        for="university"
                        class="block text-xs font-bold text-gray-700 mb-1.5"
                    >
                        Asal Universitas
                    </label>

                    <input
                        type="text"
                        id="university"
                        name="university"
                        value="{{ old('university') }}"
                        maxlength="255"
                        autocomplete="organization"
                        placeholder="Contoh: Universitas Bina Sarana Informatika"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                    @error('university')
                        <p class="text-xs text-red-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="study_program"
                        class="block text-xs font-bold text-gray-700 mb-1.5"
                    >
                        Program Studi
                    </label>

                    <input
                        type="text"
                        id="study_program"
                        name="study_program"
                        value="{{ old('study_program') }}"
                        maxlength="255"
                        placeholder="Contoh: Teknik Informatika"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                    @error('study_program')
                        <p class="text-xs text-red-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="photo"
                        class="block text-xs font-bold text-gray-700 mb-1.5"
                    >
                        Foto Profil (Opsional)
                    </label>

                    <input
                        type="file"
                        id="photo"
                        name="photo"
                        accept="image/jpeg,image/png,image/webp"
                        class="w-full px-3 py-2.5 bg-[#fdfdfd] border border-gray-200 rounded-xl text-xs text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#ffc107] file:text-black hover:file:bg-[#e0ac06] transition"
                    >

                    <p class="text-[10px] text-gray-400 mt-1">
                        JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                    </p>

                    @error('photo')
                        <p class="text-xs text-red-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="email"
                        class="block text-xs font-bold text-gray-700 mb-1.5"
                    >
                        Alamat Email Aktif
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        maxlength="255"
                        required
                        autocomplete="email"
                        placeholder="Masukkan email aktif"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                    @error('email')
                        <p class="text-xs text-red-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="password"
                        class="block text-xs font-bold text-gray-700 mb-1.5"
                    >
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                    @error('password')
                        <p class="text-xs text-red-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="password_confirmation"
                        class="block text-xs font-bold text-gray-700 mb-1.5"
                    >
                        Konfirmasi Password
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Masukkan kembali password"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3.5 bg-[#ffc107] hover:bg-[#e0ac06] text-black font-extrabold rounded-xl shadow-lg shadow-[#ffc107]/20 transition mt-2"
                >
                    Daftar sebagai Mahasiswa Magang
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-xs text-gray-500">
                    Sudah punya akun?

                    <a
                        href="{{ route('login') }}"
                        class="text-amber-600 font-bold hover:underline"
                    >
                        Login di sini
                    </a>
                </p>
            </div>
        </div>
    </main>
</body>
</html>