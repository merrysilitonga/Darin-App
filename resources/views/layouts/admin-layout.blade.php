<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Admin') - DARIN</title>
    {{-- Tailwind CDN, sama seperti halaman lain (tidak perlu npm run build). --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfbf9; }
    </style>
</head>

{{--
    Kerangka halaman admin memakai sidebar bersama (layouts.sidebar),
    sama persis dengan halaman Pengguna, Divisi, Lokasi, serta portal mahasiswa & mentor.
--}}
<body class="min-h-screen text-gray-800 antialiased">

<div class="flex min-h-screen items-stretch">

    @include('layouts.sidebar')

    <main class="min-w-0 flex-1 p-5 md:p-8">
        {{-- Judul halaman (opsional) untuk view yang mengisi section page_title --}}
        @hasSection('page_title')
            <header class="mb-6">
                <h1 class="text-2xl font-extrabold text-gray-900">@yield('page_title')</h1>
                @hasSection('page_subtitle')
                    <p class="mt-1 text-sm text-gray-500">@yield('page_subtitle')</p>
                @endif
            </header>
        @endif

        @yield('content')
    </main>

</div>

</body>
</html>
