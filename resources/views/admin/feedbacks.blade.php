@extends('layouts.admin-layout')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    {{-- HEADER --}}
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-amber-600">
            Komunikasi Peserta
        </p>
        <h1 class="mt-1 text-2xl font-extrabold text-gray-900">
            Saran &amp; Masukan
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Tinjau saran dan kendala yang dikirim peserta magang, ubah status, dan berikan tanggapan.
        </p>
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- RINGKASAN --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px">
        @php
            $kartu = [
                ['label' => 'Total Saran', 'nilai' => $stats['total'], 'warna' => 'text-gray-900', 'filter' => null],
                ['label' => 'Baru', 'nilai' => $stats['Baru'], 'warna' => 'text-amber-600', 'filter' => 'Baru'],
                ['label' => 'Diproses', 'nilai' => $stats['Diproses'], 'warna' => 'text-blue-600', 'filter' => 'Diproses'],
                ['label' => 'Selesai', 'nilai' => $stats['Selesai'], 'warna' => 'text-green-600', 'filter' => 'Selesai'],
            ];
        @endphp

        @foreach ($kartu as $item)
            <a href="{{ route('admin.feedbacks', $item['filter'] ? ['status' => $item['filter']] : []) }}"
               class="rounded-2xl border bg-white p-5 shadow-sm transition hover:border-amber-300 {{ $status === ($item['filter'] ?? '') ? 'border-amber-400' : 'border-gray-100' }}">
                <p class="text-sm font-medium text-gray-500">{{ $item['label'] }}</p>
                <p class="mt-2 text-3xl font-extrabold {{ $item['warna'] }}">{{ $item['nilai'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- FILTER --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <form action="{{ route('admin.feedbacks') }}" method="GET" class="flex flex-col gap-2 md:flex-row">
            <select name="status" class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-amber-400">
                <option value="">Semua status</option>
                @foreach (\App\Models\Feedback::STATUSES as $opsi)
                    <option value="{{ $opsi }}" @selected($status === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>

            <select name="category" class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-amber-400">
                <option value="">Semua kategori</option>
                @foreach (\App\Models\Feedback::CATEGORIES as $opsi)
                    <option value="{{ $opsi }}" @selected($category === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>

            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari judul, isi, atau nama peserta..."
                class="flex-1 rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-amber-400"
            >

            <button type="submit" class="rounded-xl bg-[#ffc107] px-5 py-2.5 text-sm font-bold text-[#191712] transition hover:bg-amber-400">
                Filter
            </button>

            <a href="{{ route('admin.feedbacks') }}" class="rounded-xl border border-gray-200 px-5 py-2.5 text-center text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                Reset
            </a>
        </form>
    </div>

    {{-- DAFTAR SARAN --}}
    <div class="space-y-4">
        @forelse ($feedbacks as $feedback)
            <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $feedback->statusBadgeClass() }}">
                                {{ $feedback->status }}
                            </span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">
                                {{ $feedback->category }}
                            </span>
                        </div>

                        <h2 class="mt-2 text-base font-extrabold text-gray-900">{{ $feedback->subject }}</h2>

                        <p class="mt-0.5 text-xs text-gray-400">
                            {{ $feedback->user?->name ?? 'Pengguna dihapus' }}
                            @if ($feedback->user?->email)
                                ({{ $feedback->user->email }})
                            @endif
                            · {{ $feedback->created_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                        </p>

                        <p class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $feedback->message }}</p>
                    </div>
                </div>

                {{-- FORM TANGGAPAN --}}
                <form action="{{ route('admin.feedbacks.update', $feedback) }}" method="POST"
                      class="mt-4 space-y-3 border-t border-gray-100 pt-4">
                    @csrf
                    @method('PUT')

                    <label class="block text-xs font-bold text-gray-600" for="reply-{{ $feedback->id }}">
                        Tanggapan Admin/HR
                        @if ($feedback->replied_at)
                            <span class="font-normal text-gray-400">
                                — terakhir oleh {{ $feedback->repliedBy?->name ?? 'Admin' }},
                                {{ $feedback->replied_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        @endif
                    </label>

                    <textarea
                        id="reply-{{ $feedback->id }}"
                        name="admin_reply"
                        rows="2"
                        maxlength="3000"
                        placeholder="Tulis tanggapan untuk peserta (opsional)..."
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-amber-400"
                    >{{ $feedback->admin_reply }}</textarea>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                        <select name="status" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-amber-400">
                            @foreach (\App\Models\Feedback::STATUSES as $opsi)
                                <option value="{{ $opsi }}" @selected($feedback->status === $opsi)>{{ $opsi }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="rounded-xl bg-[#191712] px-5 py-2 text-sm font-bold text-white transition hover:bg-gray-800">
                            Simpan
                        </button>
                    </div>
                </form>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center shadow-sm">
                <div class="mb-3 text-4xl">💬</div>
                <p class="font-bold text-gray-800">Belum ada saran</p>
                <p class="mt-1 text-sm text-gray-500">Belum ada saran yang sesuai dengan filter yang dipilih.</p>
            </div>
        @endforelse
    </div>

    @if ($feedbacks->hasPages())
        <div>
            {{ $feedbacks->links() }}
        </div>
    @endif

</div>
@endsection
