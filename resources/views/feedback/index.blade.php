<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Saran &amp; Masukan - DARIN</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fcfbf9;
        }
    </style>
</head>

<body class="text-gray-800">

<div class="flex min-h-screen">

    @include('layouts.sidebar')

    <main class="flex-1 min-w-0 overflow-y-auto p-5 md:p-10">

        <header class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight">
                Saran &amp; Masukan
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Sampaikan saran, kendala aplikasi, atau masukan lain kepada Admin/HR.
                Tanggapan akan muncul pada riwayat di bawah.
            </p>

            <div class="mt-3 h-1 w-16 rounded-full bg-amber-400"></div>
        </header>

        @if (session('success'))
            <div role="status" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="mb-2 font-semibold">Periksa kembali isian Anda:</p>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-5">

            {{-- FORM KIRIM SARAN --}}
            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="mb-5 text-lg font-bold">Kirim Saran Baru</h2>

                @if ($user->isInternshipEnded())
                    <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        Periode magang Anda telah berakhir, sehingga tidak dapat mengirim saran baru.
                        Riwayat saran tetap dapat dilihat.
                    </p>
                @else
                    <form action="{{ route('saran.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label for="category" class="mb-2 block text-sm font-semibold text-gray-700">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="category"
                                name="category"
                                required
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                            >
                                @foreach (\App\Models\Feedback::CATEGORIES as $kategori)
                                    <option value="{{ $kategori }}" @selected(old('category', 'Saran') === $kategori)>
                                        {{ $kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="subject" class="mb-2 block text-sm font-semibold text-gray-700">
                                Judul <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="subject"
                                name="subject"
                                value="{{ old('subject') }}"
                                maxlength="150"
                                required
                                placeholder="Contoh: Lokasi GPS sering tidak terbaca"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                            >
                        </div>

                        <div>
                            <label for="message" class="mb-2 block text-sm font-semibold text-gray-700">
                                Isi Saran <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="message"
                                name="message"
                                rows="6"
                                minlength="10"
                                maxlength="3000"
                                required
                                placeholder="Jelaskan saran atau kendala Anda secara singkat dan jelas..."
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                            >{{ old('message') }}</textarea>
                            <p class="mt-1 text-xs text-gray-400">
                                Minimal 10 karakter. Maksimal {{ \App\Models\Feedback::DAILY_LIMIT }} kiriman per hari.
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-[#191712] px-6 py-3 text-sm font-bold text-white transition hover:bg-gray-800"
                        >
                            Kirim Saran
                        </button>
                    </form>
                @endif
            </section>

            {{-- RIWAYAT SARAN --}}
            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-3">
                <h2 class="mb-5 text-lg font-bold">Riwayat Saran Saya</h2>

                <div class="space-y-4">
                    @forelse ($feedbacks as $feedback)
                        <article class="rounded-2xl border border-gray-100 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $feedback->subject }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400">
                                        {{ $feedback->category }} ·
                                        {{ $feedback->created_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                                    </p>
                                </div>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $feedback->statusBadgeClass() }}">
                                    {{ $feedback->status }}
                                </span>
                            </div>

                            <p class="mt-3 whitespace-pre-line text-sm text-gray-600">{{ $feedback->message }}</p>

                            @if ($feedback->admin_reply)
                                <div class="mt-3 rounded-xl border-l-4 border-amber-400 bg-amber-50/60 p-3 text-sm">
                                    <p class="mb-1 text-xs font-bold text-amber-700">
                                        Tanggapan Admin/HR
                                        @if ($feedback->replied_at)
                                            · {{ $feedback->replied_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                                        @endif
                                    </p>
                                    <p class="whitespace-pre-line text-gray-700">{{ $feedback->admin_reply }}</p>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="py-10 text-center">
                            <div class="mb-3 text-4xl">💬</div>
                            <p class="font-bold text-gray-800">Belum ada saran</p>
                            <p class="mt-1 text-sm text-gray-500">Saran yang Anda kirim akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

                @if ($feedbacks->hasPages())
                    <div class="mt-5">
                        {{ $feedbacks->links() }}
                    </div>
                @endif
            </section>

        </div>

    </main>

</div>

</body>
</html>
