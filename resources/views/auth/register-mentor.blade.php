<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Akun Mentor - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#191712] text-white flex items-center justify-center min-h-screen p-4 overflow-y-auto">

    <!-- CONTAINER UTAMA -->
    <div class="w-full max-w-lg bg-white text-gray-800 rounded-3xl shadow-2xl overflow-hidden relative border border-white/5 my-8">

        <!-- GARIS HAZARD -->
        <div
            class="h-3 w-full"
            style="background: repeating-linear-gradient(45deg, #ffc107, #ffc107 15px, #191712 15px, #191712 30px);">
        </div>

        <div class="p-8">

            <!-- LOGO -->
            <div class="flex flex-col items-center mb-6">

                <div class="bg-[#191712] text-[#ffc107] font-bold p-3 rounded-2xl flex items-center justify-center text-xl mb-3 shadow-lg">
                    ⚡
                </div>

                <h1 class="text-3xl font-extrabold tracking-wider text-black">
                    DARIN
                </h1>

                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mt-1 text-center">
                    PENDAFTARAN AKUN MENTOR
                </p>

                <p class="text-xs text-gray-500 mt-1">
                    Lengkapi data untuk membuat akun pembimbing
                </p>

            </div>


            <!-- ERROR -->
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-600 text-xs rounded-xl text-center font-medium">
                    {{ $errors->first() }}
                </div>
            @endif


            <!-- FORM -->
            <!-- PERUBAHAN 1: Action diubah ke route mentor.register.process -->
            <form
                id="register-form"
                action="{{ route('mentor.register.process') }}" 
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4"
            >

                @csrf

                <!-- NAMA -->
                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 mb-1.5">
                        Nama Lengkap & Gelar
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama dan gelar"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>


                <!-- ==================== FIELD MENTOR ==================== -->
                <!-- PERUBAHAN 2: Class 'hidden' dihapus, dan atribut 'disabled' pada input dihapus -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">NIP</label>
                            <input type="text" name="nip" value="{{ old('nip') }}" placeholder="Masukkan NIP"
                                class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">No. Telepon / WhatsApp</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 08123456789"
                                class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Divisi</label>
                            <input type="text" name="division" value="{{ old('division') }}" placeholder="Contoh: Teknologi Informasi"
                                class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Jabatan</label>
                            <input type="text" name="position" value="{{ old('position') }}" placeholder="Contoh: Supervisor"
                                class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                        </div>
                    </div>
                </div>

                <!-- EMAIL -->
                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 mb-1.5">Alamat Email Aktif</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="Masukkan email aktif"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>

                <!-- PASSWORD -->
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required minlength="8" placeholder="Buat password minimal 8 karakter"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>

                <!-- KONFIRMASI PASSWORD -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="Masukkan kembali password"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>

                <!-- SUBMIT -->
                <button type="submit" class="w-full py-3.5 bg-[#ffc107] hover:bg-[#e0ac06] text-black font-extrabold rounded-xl shadow-lg shadow-[#ffc107]/20 transition mt-2">
                    Daftar Sebagai Mentor
                </button>

            </form>

            <!-- LOGIN -->
            <div class="text-center mt-6">
                <p class="text-xs text-gray-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-amber-600 font-bold hover:underline">
                        Login di sini
                    </a>
                </p>
            </div>

        </div>
    </div>
</body>
</html>