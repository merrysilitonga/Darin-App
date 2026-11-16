<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Portal Mentor - DARIN')</title>

    <meta name="theme-color" content="#fcfbf9">
    <meta name="description" content="DARIN - Sistem Presensi Geospasial Terintegrasi ESDM">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fcfbf9;
            color: #334155;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen text-sm antialiased">

    <div class="flex min-h-screen">

        {{-- Sidebar Mentor --}}
        @include('layouts.sidebar')

        {{-- Konten utama --}}
        <main class="min-w-0 flex-1 px-5 py-6 md:px-8 md:py-8 lg:px-10">

            @php
                $mentorPending = $mentorPending ?? ['logbook' => 0, 'leave' => 0, 'total' => 0, 'overdue' => 0];
            @endphp

            {{-- Header halaman --}}
            <header class="mb-8">

                <div class="flex flex-col-reverse justify-between gap-3 sm:flex-row sm:items-start">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-800 md:text-3xl">
                        @yield('page_title', 'Portal Mentor')
                    </h1>

                    {{-- Lonceng notifikasi tindakan menunggu --}}
                    <a
                        href="{{ route('mentor.dashboard') }}#tindakan-menunggu"
                        class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl border px-3 py-2 text-xs font-medium transition
                        {{ $mentorPending['total'] > 0 ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}"
                        title="Logbook & pengajuan izin yang menunggu tindakan Anda"
                    >
                        <span aria-hidden="true">🔔</span>
                        @if ($mentorPending['total'] > 0)
                            {{ $mentorPending['total'] }} menunggu tindakan
                        @else
                            Tidak ada tindakan
                        @endif
                    </a>
                </div>

                @hasSection('page_subtitle')
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        @yield('page_subtitle')
                    </p>
                @endif

                <div class="mt-4 h-1 w-14 rounded-full bg-amber-400"></div>

            </header>

            {{-- Peringatan item yang tertunda terlalu lama --}}
            @if ($mentorPending['overdue'] > 0)
                <div
                    role="status"
                    class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    <span class="font-semibold">{{ $mentorPending['overdue'] }} logbook/pengajuan izin</span>
                    sudah menunggu {{ \App\Services\MentorPendingService::OVERDUE_DAYS }} hari atau lebih.
                    <a href="{{ route('mentor.dashboard') }}#tindakan-menunggu" class="font-semibold underline">Tinjau sekarang</a>
                </div>
            @endif

            {{-- Notifikasi berhasil --}}
            @if (session('success'))
                <div
                    role="status"
                    aria-live="polite"
                    class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Notifikasi gagal --}}
            @if (session('error'))
                <div
                    role="alert"
                    class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    {{ session('error') }}
                </div>
            @endif

            {{-- Pesan validasi --}}
            @if ($errors->any())
                <div
                    role="alert"
                    class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700"
                >
                    <p class="mb-2 font-semibold">
                        Periksa kembali data yang Anda masukkan:
                    </p>

                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Konten setiap halaman Mentor --}}
            @yield('content')

            {{-- Footer --}}
            <footer class="mt-10 border-t border-slate-200 py-5">
                <p class="text-center text-xs leading-5 text-slate-400">
                    DARIN &middot; Sistem Presensi Geospasial Terintegrasi ESDM
                </p>
            </footer>

        </main>

    </div>

    {{-- Script tambahan dari halaman --}}
    @stack('scripts')

</body>
</html>
