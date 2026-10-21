@extends('layouts.admin-layout')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <p class="text-sm text-gray-500">Administrator / Pengaturan</p>
        <h1 class="mt-1 text-2xl font-extrabold text-gray-900">Pengaturan Sistem</h1>
        <p class="mt-2 text-sm text-gray-500">Atur batas waktu absensi yang dipakai oleh sistem DARIN.</p>
    </div>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <p class="font-bold">Pengaturan belum berhasil disimpan.</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-extrabold text-gray-900">Jam Absensi Masuk</h2>
                <p class="mt-1 text-sm text-gray-500">Batas waktu ini akan digunakan saat mahasiswa mengirim absensi masuk.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div>
                    <label for="clock_in_start" class="mb-2 block text-sm font-bold text-gray-700">Mulai absensi masuk</label>
                    <input type="time" id="clock_in_start" name="clock_in_start" required
                        value="{{ old('clock_in_start', substr((string) $settings->clock_in_start, 0, 5)) }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    @error('clock_in_start')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="clock_in_end" class="mb-2 block text-sm font-bold text-gray-700">Batas masuk Senin–Kamis</label>
                    <input type="time" id="clock_in_end" name="clock_in_end" required
                        value="{{ old('clock_in_end', substr((string) $settings->clock_in_end, 0, 5)) }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    @error('clock_in_end')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="friday_clock_in_end" class="mb-2 block text-sm font-bold text-gray-700">Batas masuk Jumat</label>
                    <input type="time" id="friday_clock_in_end" name="friday_clock_in_end" required
                        value="{{ old('friday_clock_in_end', substr((string) $settings->friday_clock_in_end, 0, 5)) }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    @error('friday_clock_in_end')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-extrabold text-gray-900">Jam Absensi Pulang</h2>
                <p class="mt-1 text-sm text-gray-500">Mahasiswa baru dapat melakukan absensi pulang pada jam yang ditentukan di bawah.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="clock_out_time" class="mb-2 block text-sm font-bold text-gray-700">Jam pulang Senin–Kamis</label>
                    <input type="time" id="clock_out_time" name="clock_out_time" required
                        value="{{ old('clock_out_time', substr((string) $settings->clock_out_time, 0, 5)) }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    @error('clock_out_time')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="friday_clock_out_time" class="mb-2 block text-sm font-bold text-gray-700">Jam pulang Jumat</label>
                    <input type="time" id="friday_clock_out_time" name="friday_clock_out_time" required
                        value="{{ old('friday_clock_out_time', substr((string) $settings->friday_clock_out_time, 0, 5)) }}"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    @error('friday_clock_out_time')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-bold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            <button type="submit" class="rounded-xl bg-[#ffc107] px-6 py-3 text-sm font-extrabold text-black shadow-sm transition hover:bg-amber-400">Simpan Pengaturan</button>
        </div>
    </form>

    <p class="text-xs leading-5 text-gray-500">Zona waktu yang digunakan oleh sistem absensi adalah Asia/Jakarta (WIB). Perubahan jam berlaku untuk absensi berikutnya dan tidak mengubah catatan absensi yang sudah tersimpan.</p>
</div>
@endsection
