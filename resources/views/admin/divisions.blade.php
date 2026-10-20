
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Penempatan & Divisi - DARIN HR / Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body class="bg-[#f8f9fa] text-gray-800 antialiased">

@php
    $adminName = auth()->user()->name ?? 'Administrator';
    $adminInitials = collect(explode(' ', trim($adminName)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<div class="flex min-h-screen">

    {{-- SIDEBAR ADMIN --}}
    <aside class="w-64 shrink-0 bg-[#191712] text-white flex flex-col justify-between">

        <div>

            {{-- LOGO DARIN --}}
            <div class="p-6 flex items-center gap-3 border-b border-white/10">

                <div class="bg-[#ffc107] text-black font-extrabold
                            w-12 h-12 shrink-0 rounded-xl
                            flex items-center justify-center
                            text-2xl shadow-md">
                    ⚡
                </div>

                <div>
                    <h1 class="font-extrabold tracking-wider text-xl">
                        DARIN
                    </h1>

                    <p class="text-xs uppercase tracking-wider
                              text-amber-400 font-bold mt-1">
                        Portal HR / Admin
                    </p>
                </div>

            </div>


            {{-- NAVIGASI --}}
            <nav class="p-4 space-y-2 text-sm font-semibold">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3.5
                           text-gray-300 hover:bg-white/10 hover:text-white
                           rounded-xl transition"
                >
                    📊 Dashboard Utama
                </a>

                <a
                    href="{{ route('admin.users') }}"
                    class="flex items-center gap-3 px-4 py-3.5
                           text-gray-300 hover:bg-white/10 hover:text-white
                           rounded-xl transition"
                >
                    👥 Manajemen Pengguna
                </a>

                <a
                    href="{{ route('admin.divisions') }}"
                    class="flex items-center gap-3 px-4 py-3.5
                           bg-[#ffc107] text-black rounded-xl shadow-md"
                >
                    🏢 Penempatan & Divisi
                </a>

                <a
                    href="{{ route('admin.attendance') }}"
                    class="flex items-center gap-3 px-4 py-3.5
                           text-gray-300 hover:bg-white/10 hover:text-white
                           rounded-xl transition"
                >
                    📈 Rekap Absensi Global
                </a>

                <a
                    href="{{ route('admin.settings') }}"
                    class="flex items-center gap-3 px-4 py-3.5
                           text-gray-300 hover:bg-white/10 hover:text-white
                           rounded-xl transition"
                >
                    ⚙️ Pengaturan Sistem
                </a>

            </nav>

        </div>


        {{-- LOGOUT --}}
        <div class="p-4 border-t border-white/10">

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2
                           px-4 py-3 bg-red-600/20 hover:bg-red-600
                           text-red-300 hover:text-white rounded-xl
                           text-sm font-bold transition"
                >
                    🚪 Keluar / Logout
                </button>
            </form>

        </div>

    </aside>


    {{-- KONTEN UTAMA --}}
    <div class="flex-1 min-w-0 flex flex-col">

        {{-- TOPBAR --}}
        <header class="bg-white border-b border-gray-200
                       px-6 md:px-8 py-5 flex items-center
                       justify-between gap-4 shadow-sm">

            <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                Penempatan & Manajemen Divisi
            </h2>

            <div class="flex items-center gap-3">

                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">
                        {{ $adminName }}
                    </p>

                    <p class="text-xs text-gray-500">
                        Human Resources / Administrator
                    </p>
                </div>

                <div class="w-11 h-11 shrink-0 bg-[#ffc107]
                            rounded-xl flex items-center justify-center
                            font-extrabold text-black shadow-inner">
                    {{ $adminInitials ?: 'AD' }}
                </div>

            </div>

        </header>


        {{-- ISI HALAMAN --}}
        <main class="p-5 md:p-8 space-y-6">


            {{-- HEADER AKSI --}}
            <section class="bg-white p-6 md:p-7 rounded-3xl
                            border border-gray-200 shadow-sm
                            flex flex-col lg:flex-row
                            lg:items-center justify-between gap-5">

                <div>
                    <h3 class="text-lg font-extrabold text-gray-900">
                        Mapping Mentor & Peserta Magang per Divisi
                    </h3>

                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">
                        Atur penempatan kerja dan tentukan mentor pembimbing
                        untuk setiap kelompok peserta magang.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="alert('Form tambah penempatan belum dihubungkan ke database.')"
                    class="px-5 py-3 bg-[#ffc107] hover:bg-[#e0ac06]
                           text-black font-extrabold rounded-xl
                           text-sm transition shadow-sm whitespace-nowrap"
                >
                    + Tambah Penempatan Baru
                </button>

            </section>


            {{-- KARTU DIVISI --}}
            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">


                {{-- DIVISI 1 --}}
                <article class="bg-white p-6 md:p-7 rounded-3xl
                                border border-gray-200 shadow-sm space-y-5">

                    {{-- JUDUL DIVISI --}}
                    <div class="flex items-start justify-between
                                gap-4 border-b border-gray-100 pb-5">

                        <div class="flex items-center gap-4">

                            <div class="w-12 h-12 shrink-0 bg-amber-100
                                        text-amber-800 rounded-xl
                                        flex items-center justify-center
                                        text-2xl">
                                💻
                            </div>

                            <div>
                                <h4 class="text-base md:text-lg font-extrabold text-gray-900">
                                    Divisi IT & Web Development
                                </h4>

                                <p class="text-sm text-gray-500 mt-1">
                                    Total: 20 Peserta Aktif
                                </p>
                            </div>

                        </div>

                        <span class="px-3 py-1.5 bg-emerald-50
                                     text-emerald-700 font-bold
                                     rounded-lg text-xs shrink-0">
                            Aktif
                        </span>

                    </div>


                    {{-- MENTOR --}}
                    <div class="bg-gray-50 p-4 rounded-2xl
                                flex flex-col sm:flex-row
                                sm:items-center sm:justify-between gap-3">

                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">
                                Mentor Pembimbing
                            </p>

                            <p class="text-sm font-bold text-gray-900">
                                Budi Santoso, M.Kom.
                            </p>
                        </div>

                        <span class="self-start px-3 py-1.5
                                     bg-purple-50 text-purple-700
                                     font-semibold rounded-lg text-xs">
                            Lead Mentor
                        </span>

                    </div>


                    {{-- PESERTA --}}
                    <div class="bg-gray-50 p-4 rounded-2xl space-y-3">

                        <p class="text-xs text-gray-500 uppercase font-bold">
                            Contoh Peserta Binaan
                        </p>

                        <div class="flex flex-wrap gap-2">

                            <span class="px-3 py-2 bg-white border
                                         border-gray-200 text-gray-700
                                         font-medium rounded-lg text-xs">
                                Merry Silitonga (UBSI)
                            </span>

                            <span class="px-3 py-2 bg-white border
                                         border-gray-200 text-gray-700
                                         font-medium rounded-lg text-xs">
                                Ahmad Fauzi (UBSI)
                            </span>

                            <span class="px-3 py-2 bg-amber-100
                                         text-amber-800 font-bold
                                         rounded-lg text-xs">
                                +18 Lainnya
                            </span>

                        </div>

                    </div>


                    {{-- AKSI DIVISI --}}
                    <div class="flex flex-wrap justify-end gap-3 pt-1">

                        <button
                            type="button"
                            onclick="alert('Fitur pengelolaan anggota belum dihubungkan ke database.')"
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200
                                   text-gray-700 font-bold rounded-xl text-sm transition"
                        >
                            Kelola Anggota
                        </button>

                        <button
                            type="button"
                            onclick="alert('Fitur ubah mentor belum dihubungkan ke database.')"
                            class="px-4 py-2.5 bg-amber-50 hover:bg-amber-100
                                   text-amber-800 font-bold rounded-xl
                                   text-sm transition border border-amber-200"
                        >
                            Ubah Mentor
                        </button>

                    </div>

                </article>



                {{-- DIVISI 2 --}}
                <article class="bg-white p-6 md:p-7 rounded-3xl
                                border border-gray-200 shadow-sm space-y-5">

                    {{-- JUDUL DIVISI --}}
                    <div class="flex items-start justify-between
                                gap-4 border-b border-gray-100 pb-5">

                        <div class="flex items-center gap-4">

                            <div class="w-12 h-12 shrink-0 bg-blue-100
                                        text-blue-800 rounded-xl
                                        flex items-center justify-center
                                        text-2xl">
                                🏥
                            </div>

                            <div>
                                <h4 class="text-base md:text-lg font-extrabold text-gray-900">
                                    Divisi Layanan & Kesehatan
                                </h4>

                                <p class="text-sm text-gray-500 mt-1">
                                    Total: 18 Peserta Aktif
                                </p>
                            </div>

                        </div>

                        <span class="px-3 py-1.5 bg-emerald-50
                                     text-emerald-700 font-bold
                                     rounded-lg text-xs shrink-0">
                            Aktif
                        </span>

                    </div>


                    {{-- MENTOR --}}
                    <div class="bg-gray-50 p-4 rounded-2xl
                                flex flex-col sm:flex-row
                                sm:items-center sm:justify-between gap-3">

                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">
                                Mentor Pembimbing
                            </p>

                            <p class="text-sm font-bold text-gray-900">
                                Dr. H. Rahmat, M.Kes.
                            </p>
                        </div>

                        <span class="self-start px-3 py-1.5
                                     bg-purple-50 text-purple-700
                                     font-semibold rounded-lg text-xs">
                            Lead Mentor
                        </span>

                    </div>


                    {{-- PESERTA --}}
                    <div class="bg-gray-50 p-4 rounded-2xl space-y-3">

                        <p class="text-xs text-gray-500 uppercase font-bold">
                            Contoh Peserta Binaan
                        </p>

                        <div class="flex flex-wrap gap-2">

                            <span class="px-3 py-2 bg-white border
                                         border-gray-200 text-gray-700
                                         font-medium rounded-lg text-xs">
                                Siti Rahma (UBSI)
                            </span>

                            <span class="px-3 py-2 bg-amber-100
                                         text-amber-800 font-bold
                                         rounded-lg text-xs">
                                +17 Lainnya
                            </span>

                        </div>

                    </div>


                    {{-- AKSI DIVISI --}}
                    <div class="flex flex-wrap justify-end gap-3 pt-1">

                        <button
                            type="button"
                            onclick="alert('Fitur pengelolaan anggota belum dihubungkan ke database.')"
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200
                                   text-gray-700 font-bold rounded-xl text-sm transition"
                        >
                            Kelola Anggota
                        </button>

                        <button
                            type="button"
                            onclick="alert('Fitur ubah mentor belum dihubungkan ke database.')"
                            class="px-4 py-2.5 bg-amber-50 hover:bg-amber-100
                                   text-amber-800 font-bold rounded-xl
                                   text-sm transition border border-amber-200"
                        >
                            Ubah Mentor
                        </button>

                    </div>

                </article>

            </section>


            <p class="text-xs text-gray-400">
                Catatan: jumlah peserta, nama mentor, dan contoh anggota masih berupa data contoh.
            </p>

        </main>

    </div>

</div>

</body>
</html>
