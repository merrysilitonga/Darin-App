<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Review Logbook - DARIN Mentor</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8f9fb;
        }

        .sidebar {
            background: #101010;
        }

        .gold {
            color: #d8ad52;
        }

        .gold-bg {
            background: #d8ad52;
        }

        .nav-link {
            transition: all .2s ease;
        }

        .nav-link:hover,
        .nav-active {
            background: rgba(216, 173, 82, .13);
            color: #e7bf68;
        }

        .card {
            border: 1px solid #eceef2;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
        }
    </style>
</head>

<body class="min-h-screen text-gray-800">

<div class="min-h-screen md:flex">

    {{-- SIDEBAR --}}
    <aside class="sidebar w-full shrink-0 text-white md:min-h-screen md:w-64">
        <div class="flex items-center gap-3 border-b border-white/10 px-6 py-6">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl gold-bg text-lg font-extrabold text-black">
                D
            </div>

            <div>
                <h1 class="text-xl font-extrabold tracking-wide">
                    DARIN<span class="gold">.</span>
                </h1>
                <p class="mt-1 text-xs text-gray-400">Portal Mentor</p>
            </div>
        </div>

        <div class="border-b border-white/10 px-6 py-5">
            <p class="text-xs uppercase tracking-widest text-gray-500">
                Login sebagai
            </p>

            <p class="mt-2 font-semibold text-white">
                {{ auth()->user()->name }}
            </p>

            <p class="mt-1 text-xs text-gray-400">Mentor Magang</p>

            <p class="mt-2 text-xs text-gray-500">
                {{ optional(auth()->user()->division)->name ?? 'Divisi belum ditetapkan' }}
            </p>
        </div>

        <nav class="space-y-2 px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-widest text-gray-500">
                Menu Utama
            </p>

            <a href="{{ route('mentor.dashboard') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('mentor.interns') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                <span>♙</span>
                <span>Mahasiswa Magang</span>
            </a>

            <a href="{{ route('mentor.logbook') }}"
               class="nav-link nav-active flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold">
                <span>▤</span>
                <span>Review Logbook</span>
            </a>

            <a href="{{ route('mentor.attendance') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                <span>◷</span>
                <span>Data Absensi</span>
            </a>

            <a href="{{ route('mentor.assessment') }}"
               class="nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-300">
                <span>☆</span>
                <span>Penilaian</span>
            </a>
        </nav>

        <div class="border-t border-white/10 px-4 py-5">
            <a href="{{ route('profil') }}"
               class="nav-link mb-2 block rounded-xl px-4 py-3 text-sm text-gray-300">
                ⚙️ Pengaturan Profil
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit"
                        class="w-full rounded-xl px-4 py-3 text-left text-sm text-red-300 transition hover:bg-red-500/10">
                    ↪ Keluar
                </button>
            </form>
        </div>
    </aside>


    {{-- KONTEN UTAMA --}}
    <main class="min-w-0 flex-1">

        {{-- HEADER --}}
        <header class="flex flex-col justify-between gap-4 border-b border-gray-200 bg-white px-5 py-5 sm:flex-row sm:items-center sm:px-8">
            <div>
                <p class="text-sm text-gray-500">Portal Mentor / Logbook</p>

                <h2 class="mt-1 text-2xl font-extrabold text-gray-900">
                    Review Logbook Mahasiswa
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    Periksa laporan harian mahasiswa, lalu berikan persetujuan atau catatan revisi.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 font-bold text-amber-800">
                    {{ strtoupper(substr(auth()->user()->name ?? 'M', 0, 1)) }}
                </div>

                <div>
                    <p class="text-sm font-bold text-gray-800">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-gray-500">Mentor</p>
                </div>
            </div>
        </header>


        <div class="space-y-6 p-5 sm:p-8">

            {{-- NOTIFIKASI --}}
            @if(session('success'))
                <div role="status"
                     class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div role="alert"
                     class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert"
                     class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    <p class="font-bold">Ada yang perlu diperbaiki:</p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- PERINGATAN DIVISI --}}
            @if(!empty($assignmentMissing))
                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5">
                    <h3 class="font-bold text-amber-900">
                        Divisi mentor belum ditetapkan
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-amber-800">
                        Akun mentor ini belum memiliki divisi. Hubungi admin agar divisi mentor ditetapkan terlebih dahulu.
                        Setelah itu, logbook mahasiswa yang sesuai dengan divisi tersebut dapat ditampilkan di halaman ini.
                    </p>
                </div>
            @endif


            {{-- RINGKASAN --}}
            @php
                $totalLogbook = $logbooks->count();
                $totalMenunggu = $logbooks->where('status', 'Menunggu')->count();
                $totalDisetujui = $logbooks->where('status', 'Disetujui')->count();
                $totalDitolak = $logbooks->where('status', 'Ditolak')->count();
            @endphp

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <div class="card rounded-2xl bg-white p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Total Logbook</p>
                        <span class="rounded-lg bg-gray-100 px-3 py-2">▤</span>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-gray-900">
                        {{ $totalLogbook }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">Seluruh laporan yang ditampilkan</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Menunggu Review</p>
                        <span class="rounded-lg bg-amber-100 px-3 py-2 text-amber-700">◷</span>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-amber-600">
                        {{ $totalMenunggu }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">Perlu diperiksa mentor</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Disetujui</p>
                        <span class="rounded-lg bg-green-100 px-3 py-2 text-green-700">✓</span>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-green-600">
                        {{ $totalDisetujui }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">Laporan telah disetujui</p>
                </div>

                <div class="card rounded-2xl bg-white p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Perlu Revisi</p>
                        <span class="rounded-lg bg-red-100 px-3 py-2 text-red-700">!</span>
                    </div>

                    <p class="mt-4 text-3xl font-extrabold text-red-600">
                        {{ $totalDitolak }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">Laporan ditolak atau perlu diperbaiki</p>
                </div>

            </section>


            {{-- TABEL LOGBOOK --}}
            <section class="card overflow-hidden rounded-2xl bg-white">

                <div class="flex flex-col justify-between gap-3 border-b border-gray-100 px-5 py-5 sm:flex-row sm:items-center sm:px-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">
                            Daftar Laporan Harian
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Periksa isi laporan sebelum memberikan keputusan.
                        </p>
                    </div>

                    <span class="w-fit rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600">
                        {{ $totalLogbook }} laporan
                    </span>
                </div>


                @if(empty($assignmentMissing) && $logbooks->isNotEmpty())

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1050px] text-left text-sm">

                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-4 font-bold">Mahasiswa</th>
                                    <th class="px-5 py-4 font-bold">Tanggal</th>
                                    <th class="px-5 py-4 font-bold">Kegiatan</th>
                                    <th class="px-5 py-4 font-bold">Status</th>
                                    <th class="px-5 py-4 font-bold">Catatan Revisi</th>
                                    <th class="px-5 py-4 font-bold">Aksi Mentor</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @foreach($logbooks as $item)
                                    <tr class="align-top transition hover:bg-gray-50/80">

                                        {{-- MAHASISWA --}}
                                        <td class="px-5 py-5">
                                            <div class="font-bold text-gray-900">
                                                {{ $item->user->name ?? 'Mahasiswa' }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $item->user->email ?? '-' }}
                                            </div>
                                        </td>


                                        {{-- TANGGAL --}}
                                        <td class="whitespace-nowrap px-5 py-5 text-gray-700">
                                            @if($item->date)
                                                {{ $item->date->format('d M Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>


                                        {{-- ISI LOGBOOK --}}
                                        <td class="max-w-xs px-5 py-5">
                                            <p class="font-bold text-gray-900">
                                                {{ $item->topic }}
                                            </p>

                                            <p class="mt-2 whitespace-pre-line break-words text-xs leading-6 text-gray-600">{{ $item->description }}</p>
                                        </td>


                                        {{-- STATUS --}}
                                        <td class="whitespace-nowrap px-5 py-5">
                                            @if($item->status === 'Menunggu')
                                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800">
                                                    Menunggu
                                                </span>
                                            @elseif($item->status === 'Disetujui')
                                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-800">
                                                    Disetujui
                                                </span>
                                            @elseif($item->status === 'Ditolak')
                                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1.5 text-xs font-bold text-red-800">
                                                    Ditolak
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">
                                                    {{ $item->status }}
                                                </span>
                                            @endif

                                            @if($item->reviewed_at)
                                                <p class="mt-2 whitespace-nowrap text-xs text-gray-400">
                                                    Direview {{ $item->reviewed_at->format('d M Y, H:i') }}
                                                </p>
                                            @endif
                                        </td>


                                        {{-- CATATAN MENTOR --}}
                                        <td class="max-w-xs px-5 py-5">
                                            @if($item->mentor_feedback)
                                                <p class="whitespace-pre-line break-words text-xs leading-5 text-gray-600">{{ $item->mentor_feedback }}</p>
                                            @else
                                                <span class="text-xs text-gray-400">Belum ada catatan</span>
                                            @endif
                                        </td>


                                        {{-- AKSI REVIEW --}}
                                        <td class="min-w-[230px] px-5 py-5">

                                            @if($item->status === 'Menunggu')

                                                {{-- SETUJUI --}}
                                                <form action="{{ route('mentor.logbook.approve', $item->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Setujui logbook mahasiswa ini?')">
                                                    @csrf

                                                    <button type="submit"
                                                            class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-green-700">
                                                        ✓ Setujui Logbook
                                                    </button>
                                                </form>


                                                {{-- TOLAK / MINTA REVISI --}}
                                                <form action="{{ route('mentor.logbook.reject', $item->id) }}"
                                                      method="POST"
                                                      class="mt-3 space-y-2">
                                                    @csrf

                                                    <label for="feedback-{{ $item->id }}"
                                                           class="block text-xs font-bold text-gray-700">
                                                        Catatan revisi
                                                    </label>

                                                    <textarea id="feedback-{{ $item->id }}"
                                                              name="mentor_feedback"
                                                              rows="3"
                                                              maxlength="2000"
                                                              required
                                                              placeholder="Jelaskan bagian yang perlu diperbaiki..."
                                                              class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs outline-none transition placeholder:text-gray-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-100"></textarea>

                                                    <button type="submit"
                                                            onclick="return confirm('Tolak logbook dan kirim catatan revisi kepada mahasiswa?')"
                                                            class="w-full rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-bold text-red-700 transition hover:bg-red-100">
                                                        ↻ Tolak & Minta Revisi
                                                    </button>
                                                </form>

                                            @else

                                                <div class="rounded-lg bg-gray-50 p-3 text-xs leading-5 text-gray-500">
                                                    Logbook sudah diproses.

                                                    @if($item->reviewer)
                                                        <p class="mt-1 font-semibold text-gray-700">
                                                            Reviewer: {{ $item->reviewer->name }}
                                                        </p>
                                                    @endif
                                                </div>

                                            @endif

                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>
                    </div>

                @else

                    {{-- DATA KOSONG --}}
                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-3xl">
                            ▤
                        </div>

                        <h4 class="mt-5 text-base font-extrabold text-gray-900">
                            Belum Ada Logbook
                        </h4>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                            @if(!empty($assignmentMissing))
                                Tetapkan divisi mentor melalui admin terlebih dahulu.
                            @else
                                Belum ada logbook mahasiswa yang dapat ditampilkan untuk divisi ini.
                                Data akan muncul setelah mahasiswa mengirimkan laporan harian.
                            @endif
                        </p>

                    </div>

                @endif

                <div class="border-t border-gray-100 bg-gray-50 px-5 py-4 text-xs leading-5 text-gray-500 sm:px-6">
                    Catatan: penolakan logbook harus disertai alasan yang jelas agar mahasiswa dapat melakukan revisi.
                </div>

            </section>


            {{-- FOOTER --}}
            <footer class="pb-4 text-center text-xs text-gray-400">
                © {{ date('Y') }} DARIN — Sistem Presensi Geospasial Terintegrasi ESDM
            </footer>

        </div>
    </main>

</div>

</body>
</html>
