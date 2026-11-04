@extends('layouts.admin-layout')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-600">
                Pengelolaan Kalender
            </p>
            <h1 class="mt-1 text-2xl font-extrabold text-gray-900">
                Kalender Hari Libur
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola tanggal libur nasional dan hari khusus ESDM untuk sistem DARIN.
            </p>
        </div>

        <a href="#form-tambah"
           class="inline-flex items-center justify-center rounded-xl bg-[#ffc107] px-5 py-3 text-sm font-bold text-[#191712] shadow-sm transition hover:bg-amber-400">
            + Tambah Hari Libur
        </a>
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="mb-2 font-bold">Periksa kembali data yang dimasukkan:</p>
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- RINGKASAN --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Total Hari Libur</p>
                <span class="rounded-lg bg-amber-100 px-3 py-2 text-lg">📅</span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900">
                {{ $totalHolidays }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Data pada tahun {{ $tahun }}</p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Hari Libur Aktif</p>
                <span class="rounded-lg bg-green-100 px-3 py-2 text-lg">✓</span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-green-600">
                {{ $activeHolidays }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Diberlakukan oleh sistem</p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Hari Libur Nonaktif</p>
                <span class="rounded-lg bg-gray-100 px-3 py-2 text-lg">○</span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-500">
                {{ $inactiveHolidays }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Tidak diberlakukan oleh sistem</p>
        </div>

    </div>

    {{-- FORM TAMBAH --}}
    <div id="form-tambah" class="scroll-mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-extrabold text-gray-900">Tambah Hari Libur</h2>
            <p class="mt-1 text-sm text-gray-500">
                Masukkan tanggal dan informasi hari libur yang akan dicatat.
            </p>
        </div>

        <form action="{{ route('admin.holidays.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>
                    <label for="date" class="mb-2 block text-sm font-semibold text-gray-700">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="{{ old('date') }}"
                        required
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                    >
                </div>

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                        Nama Hari Libur <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        maxlength="150"
                        required
                        placeholder="Contoh: Hari Kemerdekaan Republik Indonesia"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                    >
                </div>

                <div>
                    <label for="type" class="mb-2 block text-sm font-semibold text-gray-700">
                        Jenis Hari Libur <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="type"
                        name="type"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                    >
                        <option value="Nasional" {{ old('type', 'Nasional') === 'Nasional' ? 'selected' : '' }}>
                            Libur Nasional
                        </option>
                        <option value="Khusus ESDM" {{ old('type') === 'Khusus ESDM' ? 'selected' : '' }}>
                            Libur Khusus ESDM
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Status Pemberlakuan
                    </label>

                    <div class="flex h-[46px] items-center gap-3 rounded-xl border border-gray-200 px-4">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            value="1"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-amber-500 focus:ring-amber-400"
                        >
                        <label for="is_active" class="text-sm font-medium text-gray-700">
                            Aktifkan hari libur
                        </label>
                    </div>
                </div>

            </div>

            <div>
                <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">
                    Keterangan
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    maxlength="2000"
                    placeholder="Tambahkan keterangan apabila diperlukan..."
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                >{{ old('description') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-xl bg-[#191712] px-6 py-3 text-sm font-bold text-white transition hover:bg-gray-800"
                >
                    Simpan Hari Libur
                </button>
            </div>
        </form>
    </div>

    {{-- FILTER DAN DAFTAR --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

        <div class="border-b border-gray-100 p-5">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-900">Daftar Hari Libur</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Daftar hari libur yang tercatat pada kalender DARIN.
                    </p>
                </div>

                <form action="{{ route('admin.holidays') }}" method="GET"
                      class="flex flex-col gap-2 sm:flex-row">

                    <input
                        type="number"
                        name="tahun"
                        value="{{ $tahun }}"
                        min="2000"
                        max="2100"
                        placeholder="Tahun"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-amber-400 sm:w-28"
                    >

                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari hari libur..."
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-amber-400 sm:w-52"
                    >

                    <button
                        type="submit"
                        class="rounded-xl bg-[#ffc107] px-4 py-2.5 text-sm font-bold text-[#191712] transition hover:bg-amber-400"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('admin.holidays') }}"
                        class="rounded-xl border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-600 transition hover:bg-gray-50"
                    >
                        Reset
                    </a>
                </form>
            </div>
        </div>

        {{-- TABEL --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">

                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-4 font-bold">No.</th>
                        <th class="px-5 py-4 font-bold">Tanggal</th>
                        <th class="px-5 py-4 font-bold">Nama Hari Libur</th>
                        <th class="px-5 py-4 font-bold">Jenis</th>
                        <th class="px-5 py-4 font-bold">Status</th>
                        <th class="px-5 py-4 font-bold">Keterangan</th>
                        <th class="px-5 py-4 text-center font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($holidays as $index => $holiday)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-5 py-4 text-gray-500">
                                {{ $holidays->firstItem() + $index }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-700">
                                {{ $holiday->date->format('d-m-Y') }}
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-bold text-gray-900">
                                    {{ $holiday->name }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                @if ($holiday->type === 'Nasional')
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                        Nasional
                                    </span>
                                @else
                                    <span class="rounded-full bg-purple-50 px-3 py-1 text-xs font-bold text-purple-700">
                                        {{ $holiday->type }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if ($holiday->is_active)
                                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="max-w-xs px-5 py-4 text-gray-500">
                                {{ $holiday->description ?: '-' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">

                                    <details class="relative">
                                        <summary class="cursor-pointer list-none rounded-lg border border-amber-200 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-50">
                                            Edit
                                        </summary>

                                        <div class="absolute right-0 z-20 mt-2 w-80 rounded-xl border border-gray-200 bg-white p-4 text-left shadow-xl sm:w-96">
                                            <h3 class="mb-4 font-extrabold text-gray-900">
                                                Edit Hari Libur
                                            </h3>

                                            <form
                                                action="{{ route('admin.holidays.update', $holiday->id) }}"
                                                method="POST"
                                                class="space-y-3"
                                            >
                                                @csrf
                                                @method('PUT')

                                                <div>
                                                    <label class="mb-1 block text-xs font-bold text-gray-600">
                                                        Tanggal
                                                    </label>
                                                    <input
                                                        type="date"
                                                        name="date"
                                                        value="{{ $holiday->date->format('Y-m-d') }}"
                                                        required
                                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                                    >
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-xs font-bold text-gray-600">
                                                        Nama Hari Libur
                                                    </label>
                                                    <input
                                                        type="text"
                                                        name="name"
                                                        value="{{ $holiday->name }}"
                                                        maxlength="150"
                                                        required
                                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                                    >
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-xs font-bold text-gray-600">
                                                        Jenis
                                                    </label>
                                                    <select
                                                        name="type"
                                                        required
                                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm"
                                                    >
                                                        <option value="Nasional" {{ $holiday->type === 'Nasional' ? 'selected' : '' }}>
                                                            Libur Nasional
                                                        </option>
                                                        <option value="Khusus ESDM" {{ $holiday->type === 'Khusus ESDM' ? 'selected' : '' }}>
                                                            Libur Khusus ESDM
                                                        </option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-xs font-bold text-gray-600">
                                                        Keterangan
                                                    </label>
                                                    <textarea
                                                        name="description"
                                                        rows="3"
                                                        maxlength="2000"
                                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                                    >{{ $holiday->description }}</textarea>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input
                                                        type="checkbox"
                                                        id="holiday-active-{{ $holiday->id }}"
                                                        name="is_active"
                                                        value="1"
                                                        {{ $holiday->is_active ? 'checked' : '' }}
                                                        class="h-4 w-4 rounded border-gray-300 text-amber-500"
                                                    >
                                                    <label
                                                        for="holiday-active-{{ $holiday->id }}"
                                                        class="text-xs font-medium text-gray-700"
                                                    >
                                                        Aktifkan hari libur
                                                    </label>
                                                </div>

                                                <button
                                                    type="submit"
                                                    class="w-full rounded-lg bg-[#191712] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-gray-800"
                                                >
                                                    Simpan Perubahan
                                                </button>
                                            </form>
                                        </div>
                                    </details>

                                    <form
                                        action="{{ route('admin.holidays.destroy', $holiday->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data hari libur ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50"
                                        >
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="mx-auto max-w-sm">
                                    <div class="mb-3 text-4xl">📅</div>
                                    <p class="font-bold text-gray-800">
                                        Belum ada data hari libur
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Belum ada data yang sesuai dengan tahun atau pencarian yang dipilih.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if ($holidays->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $holidays->links() }}
            </div>
        @endif

    </div>

    <p class="text-xs leading-relaxed text-gray-400">
        Catatan: Data kalender hari libur dikelola oleh Admin/HR. Pastikan tanggal dan jenis hari libur
        sudah sesuai sebelum mengaktifkannya.
    </p>

</div>
@endsection