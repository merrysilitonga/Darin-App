<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Akun - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .role-option {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            min-height: 145px;
            padding: 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 16px;
            background: #ffffff;
            color: #374151;
            text-align: left;
            transition: all .2s ease;
        }

        .role-option:hover {
            border-color: #e8bd36;
            background: #fffdf6;
            transform: translateY(-2px);
        }

        .role-option[aria-pressed="true"] {
            border-color: #ffc107;
            background: #fffbeb;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, .12);
        }

        .role-option:focus-visible {
            outline: 3px solid rgba(255, 193, 7, .45);
            outline-offset: 3px;
        }

        .role-icon {
            display: flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #f3f4f6;
            font-size: 21px;
        }

        .role-option[aria-pressed="true"] .role-icon {
            background: #ffc107;
            color: #191712;
        }

        .role-check {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            width: 20px;
            height: 20px;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            border-radius: 50%;
            color: transparent;
            font-size: 12px;
        }

        .role-option[aria-pressed="true"] .role-check {
            border-color: #191712;
            background: #191712;
            color: #ffc107;
        }

        .form-input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 11px;
            background: #fdfdfd;
            padding: 12px 14px;
            color: #1f2937;
            font-size: 13px;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-input:focus {
            border-color: #ffc107;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, .15);
        }

        .form-input:disabled {
            cursor: not-allowed;
            background: #f3f4f6;
            color: #9ca3af;
        }

        .field-label {
            display: block;
            margin-bottom: 7px;
            color: #374151;
            font-size: 12px;
            font-weight: 700;
        }

        [hidden] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen overflow-y-auto bg-[#191712] px-4 py-8 text-gray-800 sm:py-12">

    <main class="mx-auto w-full max-w-2xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl">

        {{-- GARIS IDENTITAS DARIN --}}
        <div
            class="h-2.5 w-full"
            style="background: repeating-linear-gradient(45deg, #ffc107, #ffc107 15px, #191712 15px, #191712 30px);"
        ></div>

        <div class="p-5 sm:p-9">

            {{-- HEADER --}}
            <header class="mb-8 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#191712] text-2xl text-[#ffc107] shadow-lg">
                    ⚡
                </div>

                <h1 class="text-3xl font-extrabold tracking-wider text-[#191712]">
                    DARIN<span class="text-[#ffc107]">.</span>
                </h1>

                <p class="mt-2 text-[11px] font-bold uppercase tracking-[0.2em] text-gray-500">
                    Pendaftaran Akun
                </p>

                <p class="mt-3 text-sm leading-6 text-gray-500">
                    Pilih jenis akun untuk melanjutkan pendaftaran
                    di Sistem Presensi Geospasial Terintegrasi ESDM.
                </p>
            </header>

            {{-- PESAN ERROR --}}
            @if ($errors->any())
                <div
                    role="alert"
                    class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                >
                    <p class="mb-2 font-bold">
                        Pendaftaran belum berhasil.
                    </p>

                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- PESAN STATUS --}}
            @if (session('status'))
                <div
                    role="status"
                    class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"
                >
                    {{ session('status') }}
                </div>
            @endif

            {{-- PILIH JENIS AKUN --}}
            <section class="mb-7">

                <div class="mb-4">
                    <h2 class="text-base font-extrabold text-gray-900">
                        Daftar sebagai
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Pilih salah satu jenis akun di bawah ini.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                    {{-- MAHASISWA --}}
                    <button
                        type="button"
                        class="role-option"
                        data-role="magang"
                        aria-pressed="true"
                    >
                        <span class="role-check" aria-hidden="true">✓</span>

                        <span class="role-icon" aria-hidden="true">
                            🎓
                        </span>

                        <span>
                            <span class="block text-sm font-extrabold text-gray-900">
                                Mahasiswa Magang
                            </span>

                            <span class="mt-1.5 block text-xs leading-5 text-gray-500">
                                Untuk peserta magang yang akan menggunakan DARIN.
                            </span>
                        </span>
                    </button>

                    {{-- MENTOR --}}
                    <button
                        type="button"
                        class="role-option"
                        data-role="mentor"
                        aria-pressed="false"
                    >
                        <span class="role-check" aria-hidden="true">✓</span>

                        <span class="role-icon" aria-hidden="true">
                            👥
                        </span>

                        <span>
                            <span class="block text-sm font-extrabold text-gray-900">
                                Mentor
                            </span>

                            <span class="mt-1.5 block text-xs leading-5 text-gray-500">
                                Untuk pembimbing atau supervisor mahasiswa.
                            </span>
                        </span>
                    </button>

                    {{-- ADMIN / HR --}}
                    <button
                        type="button"
                        class="role-option"
                        data-role="admin"
                        aria-pressed="false"
                    >
                        <span class="role-check" aria-hidden="true">✓</span>

                        <span class="role-icon" aria-hidden="true">
                            🛡️
                        </span>

                        <span>
                            <span class="block text-sm font-extrabold text-gray-900">
                                Admin / HR
                            </span>

                            <span class="mt-1.5 block text-xs leading-5 text-gray-500">
                                Untuk pengelola akun dan administrasi magang.
                            </span>
                        </span>
                    </button>

                </div>

                <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">
                    <p
                        id="role-help"
                        class="text-xs leading-5 text-amber-900"
                        aria-live="polite"
                    >
                        Pendaftaran mahasiswa magang tersedia untuk publik.
                    </p>
                </div>

            </section>

            {{-- FORM --}}
            <form
                id="register-form"
                action="{{ route('register') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-5"
            >
                @csrf

                <input
                    type="hidden"
                    name="role"
                    id="role-input"
                    value="{{ old('role', 'magang') }}"
                >

                {{-- NAMA --}}
                <div>
                    <label
                        for="name"
                        id="label-name"
                        class="field-label"
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
                        class="form-input"
                    >

                    @error('name')
                        <p class="mt-1.5 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- DATA MAHASISWA MAGANG --}}
                <section id="fields-magang" class="space-y-5">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label for="nim" class="field-label">
                                NIM
                            </label>

                            <input
                                type="text"
                                id="nim"
                                name="nim"
                                value="{{ old('nim') }}"
                                maxlength="50"
                                placeholder="Nomor Induk Mahasiswa"
                                class="form-input magang-field"
                            >

                            @error('nim')
                                <p class="mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone-magang" class="field-label">
                                No. HP / WhatsApp
                            </label>

                            <input
                                type="tel"
                                id="phone-magang"
                                name="phone"
                                value="{{ old('phone') }}"
                                maxlength="30"
                                autocomplete="tel"
                                placeholder="Contoh: 08123456789"
                                class="form-input magang-field"
                            >

                            @error('phone')
                                <p class="mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                    <div>
                        <label for="university" class="field-label">
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
                            class="form-input magang-field"
                        >

                        @error('university')
                            <p class="mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="study_program" class="field-label">
                            Program Studi
                        </label>

                        <input
                            type="text"
                            id="study_program"
                            name="study_program"
                            value="{{ old('study_program') }}"
                            maxlength="255"
                            placeholder="Contoh: Teknik Informatika"
                            class="form-input magang-field"
                        >

                        @error('study_program')
                            <p class="mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="photo" class="field-label">
                            Foto Profil (Opsional)
                        </label>

                        <input
                            type="file"
                            id="photo"
                            name="photo"
                            accept="image/jpeg,image/png,image/webp"
                            class="form-input magang-field"
                        >

                        <p class="mt-1.5 text-[11px] leading-5 text-gray-400">
                            JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                        </p>

                        @error('photo')
                            <p class="mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </section>

                {{-- DATA MENTOR --}}
                <section id="fields-mentor" class="hidden space-y-5" hidden>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label for="nip-mentor" class="field-label">
                                NIP
                            </label>

                            <input
                                type="text"
                                id="nip-mentor"
                                name="nip"
                                value="{{ old('nip') }}"
                                maxlength="50"
                                placeholder="Masukkan NIP"
                                disabled
                                class="form-input mentor-field"
                            >

                            @error('nip')
                                <p class="mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone-mentor" class="field-label">
                                No. HP / WhatsApp
                            </label>

                            <input
                                type="tel"
                                id="phone-mentor"
                                name="phone"
                                value="{{ old('phone') }}"
                                maxlength="30"
                                placeholder="Contoh: 08123456789"
                                disabled
                                class="form-input mentor-field"
                            >
                        </div>

                    </div>

                    <div>
                        <label for="position-mentor" class="field-label">
                            Jabatan
                        </label>

                        <input
                            type="text"
                            id="position-mentor"
                            name="position"
                            value="{{ old('position') }}"
                            maxlength="255"
                            placeholder="Contoh: Pembimbing / Supervisor"
                            disabled
                            class="form-input mentor-field"
                        >
                    </div>

                </section>

                {{-- DATA ADMIN / HR --}}
                <section id="fields-admin" class="hidden space-y-5" hidden>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label for="nip-admin" class="field-label">
                                NIP / Nomor Induk Pegawai
                            </label>

                            <input
                                type="text"
                                id="nip-admin"
                                name="nip"
                                value="{{ old('nip') }}"
                                maxlength="50"
                                placeholder="Masukkan NIP Admin"
                                disabled
                                class="form-input admin-field"
                            >
                        </div>

                        <div>
                            <label for="phone-admin" class="field-label">
                                No. HP / WhatsApp
                            </label>

                            <input
                                type="tel"
                                id="phone-admin"
                                name="phone"
                                value="{{ old('phone') }}"
                                maxlength="30"
                                placeholder="Contoh: 08123456789"
                                disabled
                                class="form-input admin-field"
                            >
                        </div>

                    </div>

                    <div>
                        <label for="position-admin" class="field-label">
                            Jabatan
                        </label>

                        <input
                            type="text"
                            id="position-admin"
                            name="position"
                            value="{{ old('position') }}"
                            maxlength="255"
                            placeholder="Contoh: Administrator / HR"
                            disabled
                            class="form-input admin-field"
                        >
                    </div>

                </section>

                {{-- KODE UNDANGAN MENTOR / ADMIN --}}
                <div
                    id="registration-code-container"
                    class="hidden rounded-2xl border border-amber-200 bg-amber-50 p-4"
                    hidden
                >
                    <div class="mb-3 flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#191712] text-lg text-[#ffc107]">
                            🔐
                        </span>

                        <div>
                            <h3 class="text-sm font-extrabold text-gray-900">
                                Kode Undangan
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-gray-600">
                                Masukkan kode undangan resmi yang diberikan
                                oleh pengelola DARIN.
                            </p>
                        </div>
                    </div>

                    <input
                        type="password"
                        id="registration_code"
                        name="registration_code"
                        autocomplete="off"
                        placeholder="Masukkan kode undangan"
                        disabled
                        class="form-input border-amber-200 bg-white"
                    >

                    <p
                        id="registration-code-help"
                        class="mt-2 text-[11px] leading-5 text-gray-600"
                    >
                        Kode undangan diperlukan untuk mendaftar.
                    </p>

                    @error('registration_code')
                        <p class="mt-1.5 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- EMAIL --}}
                <div>
                    <label for="email" class="field-label">
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
                        class="form-input"
                    >

                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- PASSWORD --}}
                <div>
                    <label for="password" class="field-label">
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
                        class="form-input"
                    >

                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- KONFIRMASI PASSWORD --}}
                <div>
                    <label for="password_confirmation" class="field-label">
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
                        class="form-input"
                    >
                </div>

                {{-- TOMBOL DAFTAR --}}
                <button
                    type="submit"
                    class="w-full rounded-xl bg-[#ffc107] px-5 py-4 text-sm font-extrabold text-black shadow-lg shadow-amber-100 transition hover:bg-[#e0ac06] focus:outline-none focus:ring-4 focus:ring-amber-200"
                >
                    Daftar sebagai
                    <span id="submit-role-text">Mahasiswa Magang</span>
                    <span aria-hidden="true"> →</span>
                </button>

                <p class="text-center text-[11px] leading-5 text-gray-400">
                    Dengan melanjutkan, pastikan data yang kamu masukkan benar
                    dan sesuai dengan jenis akun yang dipilih.
                </p>

            </form>

            {{-- LOGIN --}}
            <div class="mt-7 border-t border-gray-100 pt-6 text-center">
                <p class="text-sm text-gray-500">
                    Sudah punya akun?

                    <a
                        href="{{ route('login') }}"
                        class="font-extrabold text-amber-700 hover:underline"
                    >
                        Login di sini
                    </a>
                </p>
            </div>

        </div>
    </main>

    <footer class="mx-auto mt-6 max-w-2xl pb-3 text-center text-xs leading-5 text-gray-500">
        © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
    </footer>

    <script>
        function switchRole(role) {
            const allowedRoles = ['magang', 'mentor', 'admin'];

            if (!allowedRoles.includes(role)) {
                role = 'magang';
            }

            const roleInput = document.getElementById('role-input');
            const submitText = document.getElementById('submit-role-text');
            const subtitle = document.querySelector('header > p:nth-of-type(2)');
            const labelName = document.getElementById('label-name');
            const roleHelp = document.getElementById('role-help');

            const codeContainer = document.getElementById(
                'registration-code-container'
            );

            const codeInput = document.getElementById('registration_code');
            const codeHelp = document.getElementById('registration-code-help');

            // Memperbarui tampilan kartu pilihan akun.
            document.querySelectorAll('.role-option').forEach(function (button) {
                const isActive = button.dataset.role === role;

                button.setAttribute(
                    'aria-pressed',
                    isActive ? 'true' : 'false'
                );
            });

            // Mengatur bagian formulir sesuai role.
            allowedRoles.forEach(function (currentRole) {
                const section = document.getElementById(
                    'fields-' + currentRole
                );

                const isActive = currentRole === role;

                section.hidden = !isActive;
                section.classList.toggle('hidden', !isActive);
            });

            // Nonaktifkan field dari jenis akun lain.
            document.querySelectorAll(
                '.magang-field, .mentor-field, .admin-field'
            ).forEach(function (input) {
                input.disabled = true;
            });

            // Aktifkan field milik role yang dipilih.
            document.querySelectorAll(
                '.' + role + '-field'
            ).forEach(function (input) {
                input.disabled = false;
            });

            roleInput.value = role;

            // Mengatur judul dan keterangan sesuai jenis akun.
            if (role === 'magang') {
                submitText.textContent = 'Mahasiswa Magang';
                subtitle.textContent =
                    'Buat akun mahasiswa untuk menggunakan sistem DARIN.';

                labelName.textContent = 'Nama Lengkap';

                roleHelp.textContent =
                    'Pendaftaran mahasiswa magang tersedia untuk publik.';

                codeContainer.hidden = true;
                codeContainer.classList.add('hidden');

                codeInput.disabled = true;
                codeInput.required = false;
                codeInput.value = '';

            } else if (role === 'mentor') {
                submitText.textContent = 'Mentor';

                subtitle.textContent =
                    'Daftarkan akun Mentor untuk mendampingi peserta magang.';

                labelName.textContent = 'Nama Lengkap dan Gelar';

                roleHelp.textContent =
                    'Pendaftaran Mentor memerlukan kode undangan resmi dari pengelola DARIN.';

                codeHelp.textContent =
                    'Gunakan kode undangan Mentor yang diberikan oleh pengelola DARIN.';

                codeContainer.hidden = false;
                codeContainer.classList.remove('hidden');

                codeInput.disabled = false;
                codeInput.required = true;

            } else {
                submitText.textContent = 'Admin / HR';

                subtitle.textContent =
                    'Pendaftaran akun pengelola sistem DARIN.';

                labelName.textContent = 'Nama Lengkap';

                roleHelp.textContent =
                    'Pendaftaran Admin/HR memerlukan kode undangan khusus yang diverifikasi oleh server.';

                codeHelp.textContent =
                    'Gunakan kode undangan Admin/HR. Kode ini berbeda dari kode undangan Mentor.';

                codeContainer.hidden = false;
                codeContainer.classList.remove('hidden');

                codeInput.disabled = false;
                codeInput.required = true;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Pertahankan role yang dipilih ketika validasi gagal.
            const initialRole = @json(old('role', 'magang'));

            switchRole(initialRole);

            document.querySelectorAll('.role-option').forEach(function (button) {
                button.addEventListener('click', function () {
                    switchRole(button.dataset.role);
                });
            });
        });
    </script>

</body>
</html>