<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Portal Mentor - DARIN')</title>

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
            background: #fcfbf9;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen text-sm antialiased">

    <div class="flex min-h-screen">
        @include('layouts.sidebar')

        <main class="min-w-0 flex-1 px-5 py-6 md:px-8 md:py-8 lg:px-10">
            <header class="mb-8">
                <h1 class="text-2xl font-bold tracking-tight text-slate-800 md:text-3xl">
                    @yield('page_title', 'Portal Mentor')
                </h1>

                @hasSection('page_subtitle')
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        @yield('page_subtitle')
                    </p>
                @endif

                <div class="mt-4 h-1 w-14 rounded-full bg-amber-400"></div>
            </header>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
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

            @yield('content')

            <footer class="mt-10 border-t border-slate-200 py-5">
                <p class="text-center text-xs leading-5 text-slate-400">
                    DARIN · Sistem Presensi Geospasial Terintegrasi ESDM
                </p>
            </footer>
        </main>
    </div>

    @stack('scripts')
</body>
</html>