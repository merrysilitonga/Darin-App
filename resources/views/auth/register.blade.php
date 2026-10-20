<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar Akun - DARIN</title>

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
                    PENDAFTARAN AKUN DARIN
                </p>

                <p class="text-xs text-gray-500 mt-1">
                    Lengkapi data untuk membuat akun
                </p>

            </div>


            <!-- ERROR -->
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-600 text-xs rounded-xl text-center font-medium">
                    {{ $errors->first() }}
                </div>
            @endif


            <!-- FORM -->
            <form
                id="register-form"
                action="{{ url('/register') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4"
            >

                @csrf


                <!-- ROLE -->
                <input
                    type="hidden"
                    name="role"
                    id="role-input"
                    value="magang"
                >


                <!-- PILIH ROLE -->
                <div class="grid grid-cols-3 gap-1 bg-gray-100 p-1.5 rounded-2xl mb-6">

                    <button
                        type="button"
                        id="btn-intern"
                        onclick="switchRole('intern')"
                        class="py-2 text-[11px] font-bold rounded-xl transition bg-[#ffc107] text-black shadow-sm text-center"
                    >
                        Magang
                    </button>

                    <button
                        type="button"
                        id="btn-mentor"
                        onclick="switchRole('mentor')"
                        class="py-2 text-[11px] font-bold rounded-xl transition text-gray-500 hover:text-gray-800 text-center"
                    >
                        Mentor
                    </button>

                    <button
                        type="button"
                        id="btn-hr"
                        onclick="switchRole('hr')"
                        class="py-2 text-[11px] font-bold rounded-xl transition text-gray-500 hover:text-gray-800 text-center"
                    >
                        HR / Admin
                    </button>

                </div>


                <!-- NAMA -->
                <div>

                    <label
                        for="name"
                        id="label-name"
                        class="block text-xs font-bold text-gray-700 mb-1.5"
                    >
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        placeholder="Masukkan nama lengkap"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                </div>


                <!-- ==================== MAGANG ==================== -->
                <div
                    id="fields-intern"
                    class="space-y-4"
                >

                    <!-- NIM & HP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                NIM
                            </label>

                            <input
                                type="text"
                                name="nim"
                                value="{{ old('nim') }}"
                                placeholder="Nomor Induk Mahasiswa"
                                class="intern-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                            >

                        </div>


                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                No. HP / WhatsApp
                            </label>

                            <input
                                type="tel"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="Contoh: 08123456789"
                                class="intern-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                            >

                        </div>

                    </div>


                    <!-- UNIVERSITAS & PRODI -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                Asal Universitas
                            </label>

                            <input
                                type="text"
                                name="university"
                                value="{{ old('university') }}"
                                placeholder="Contoh: UBSI"
                                class="intern-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                            >

                        </div>


                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                Program Studi
                            </label>

                            <input
                                type="text"
                                name="study_program"
                                value="{{ old('study_program') }}"
                                placeholder="Contoh: Informatika"
                                class="intern-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                            >

                        </div>

                    </div>


                    <!-- FOTO -->
                    <div>

                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            Unggah Foto Profil / Kartu Identitas
                        </label>

                        <input
                            type="file"
                            name="photo"
                            accept="image/jpeg,image/png,image/jpg"
                            class="intern-field w-full px-3 py-2.5 bg-[#fdfdfd] border border-gray-200 rounded-xl text-xs text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#ffc107] file:text-black hover:file:bg-[#e0ac06] transition"
                        >

                        <p class="text-[10px] text-gray-400 mt-1">
                            JPG atau PNG, maksimal 2MB.
                        </p>

                    </div>

                </div>


                <!-- ==================== MENTOR ==================== -->
                <div
                    id="fields-mentor"
                    class="space-y-4 hidden"
                >

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                NIP
                            </label>

                            <input
                                type="text"
                                name="nip"
                                value="{{ old('nip') }}"
                                placeholder="Masukkan NIP"
                                class="mentor-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                                disabled
                            >

                        </div>


                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                No. Telepon / WhatsApp
                            </label>

                            <input
                                type="tel"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="Contoh: 08123456789"
                                class="mentor-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                                disabled
                            >

                        </div>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                Divisi
                            </label>

                            <input
                                type="text"
                                name="division"
                                value="{{ old('division') }}"
                                placeholder="Contoh: Teknologi Informasi"
                                class="mentor-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                                disabled
                            >

                        </div>


                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                Jabatan
                            </label>

                            <input
                                type="text"
                                name="position"
                                value="{{ old('position') }}"
                                placeholder="Contoh: Supervisor"
                                class="mentor-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                                disabled
                            >

                        </div>

                    </div>

                </div>


                <!-- ==================== ADMIN ==================== -->
                <div
                    id="fields-hr"
                    class="space-y-4 hidden"
                >

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                NIP / Nomor Induk Pegawai
                            </label>

                            <input
                                type="text"
                                name="nip"
                                value="{{ old('nip') }}"
                                placeholder="Masukkan NIP Admin"
                                class="hr-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                                disabled
                            >

                        </div>


                        <div>

                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                No. Telepon / WhatsApp
                            </label>

                            <input
                                type="tel"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="Contoh: 08123456789"
                                class="hr-field w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                                disabled
                            >

                        </div>

                    </div>

                </div>


                <!-- EMAIL -->
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
                        required
                        placeholder="Masukkan email aktif"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                </div>


                <!-- PASSWORD -->
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
                        minlength="6"
                        placeholder="Buat password minimal 6 karakter"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                </div>


                <!-- KONFIRMASI PASSWORD -->
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
                        minlength="6"
                        placeholder="Masukkan kembali password"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"
                    >

                </div>


                <!-- SUBMIT -->
                <button
                    type="submit"
                    class="w-full py-3.5 bg-[#ffc107] hover:bg-[#e0ac06] text-black font-extrabold rounded-xl shadow-lg shadow-[#ffc107]/20 transition mt-2"
                >
                    Daftar Sebagai
                    <span id="submit-role-text">
                        Anak Magang
                    </span>
                </button>

            </form>


            <!-- LOGIN -->
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

    </div>


    <!-- JAVASCRIPT ROLE -->
    <script>

        function switchRole(role) {

            const btnIntern = document.getElementById('btn-intern');
            const btnMentor = document.getElementById('btn-mentor');
            const btnHr = document.getElementById('btn-hr');

            const fieldsIntern = document.getElementById('fields-intern');
            const fieldsMentor = document.getElementById('fields-mentor');
            const fieldsHr = document.getElementById('fields-hr');

            const roleInput = document.getElementById('role-input');
            const submitRoleText = document.getElementById('submit-role-text');
            const labelName = document.getElementById('label-name');

            const activeClass =
                "py-2 text-[11px] font-bold rounded-xl transition bg-[#ffc107] text-black shadow-sm text-center";

            const inactiveClass =
                "py-2 text-[11px] font-bold rounded-xl transition text-gray-500 hover:text-gray-800 text-center";


            // RESET BUTTON
            btnIntern.className = inactiveClass;
            btnMentor.className = inactiveClass;
            btnHr.className = inactiveClass;


            // SEMBUNYIKAN SEMUA FIELD
            fieldsIntern.classList.add('hidden');
            fieldsMentor.classList.add('hidden');
            fieldsHr.classList.add('hidden');


            // NONAKTIFKAN SEMUA FIELD
            document.querySelectorAll(
                '.intern-field, .mentor-field, .hr-field'
            ).forEach(input => {

                input.disabled = true;
                input.required = false;

            });


            // =========================
            // MAGANG
            // =========================

            if (role === 'intern') {

                btnIntern.className = activeClass;

                fieldsIntern.classList.remove('hidden');

                roleInput.value = 'magang';

                submitRoleText.innerText = 'Anak Magang';

                labelName.innerText = 'Nama Lengkap';


                document.querySelectorAll('.intern-field').forEach(input => {

                    input.disabled = false;

                });

            }


            // =========================
            // MENTOR
            // =========================

            else if (role === 'mentor') {

                btnMentor.className = activeClass;

                fieldsMentor.classList.remove('hidden');

                roleInput.value = 'mentor';

                submitRoleText.innerText = 'Mentor / Pembimbing';

                labelName.innerText = 'Nama Lengkap & Gelar';


                document.querySelectorAll('.mentor-field').forEach(input => {

                    input.disabled = false;

                });

            }


            // =========================
            // ADMIN
            // =========================

            else if (role === 'hr') {

                btnHr.className = activeClass;

                fieldsHr.classList.remove('hidden');

                roleInput.value = 'admin';

                submitRoleText.innerText = 'HR / Administrator';

                labelName.innerText = 'Nama Lengkap';


                document.querySelectorAll('.hr-field').forEach(input => {

                    input.disabled = false;

                });

            }

        }


        // DEFAULT: MAGANG
        document.addEventListener('DOMContentLoaded', function () {

            switchRole('intern');

        });

    </script>

</body>

</html>