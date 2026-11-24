@extends('layouts.admin-layout')

@section('title', 'Impor Pengguna')
@section('page_title', 'Impor Pengguna dari CSV')
@section('page_subtitle', 'Daftarkan banyak akun Peserta Magang dan Mentor sekaligus.')

@section('content')
@php
    $kredensial = session('import_credentials', []);
    $adaPasswordBaru = collect($kredensial)->whereNotNull('password')->isNotEmpty();
@endphp

<div class="space-y-6">

    <a href="{{ route('admin.users') }}" class="inline-block text-sm font-semibold text-amber-700 hover:underline">
        ← Kembali ke Manajemen Pengguna
    </a>

    {{-- HASIL IMPOR --}}
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($kredensial !== [])
        <div class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-gray-900">Akun yang Baru Dibuat</h2>

            @if ($adaPasswordBaru)
                <p class="mt-1 rounded-xl bg-amber-50 p-3 text-sm text-amber-800">
                    Password yang dibuat otomatis <strong>hanya ditampilkan sekali</strong> di halaman ini.
                    Salin atau unduh sekarang, lalu bagikan ke masing-masing pengguna secara pribadi.
                    Minta mereka mengganti password setelah login pertama.
                </p>
            @endif

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm" id="tabel-kredensial">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Password</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($kredensial as $akun)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $akun['name'] }}</td>
                                <td class="px-4 py-3">{{ $akun['email'] }}</td>
                                <td class="px-4 py-3 capitalize">{{ $akun['role'] }}</td>
                                <td class="px-4 py-3 font-mono">
                                    {{ $akun['password'] ?? '(sesuai file CSV)' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($adaPasswordBaru)
                <button type="button" id="unduh-kredensial"
                        class="mt-4 rounded-xl bg-[#191712] px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800">
                    Unduh Daftar Akun (CSV)
                </button>

                <script>
                    document.getElementById('unduh-kredensial').addEventListener('click', function () {
                        const data = @json($kredensial);
                        const escape = (nilai) => '"' + String(nilai ?? '').replace(/"/g, '""') + '"';
                        const baris = [['nama', 'email', 'role', 'password']]
                            .concat(data.map((akun) => [akun.name, akun.email, akun.role, akun.password ?? '(sesuai file CSV)']))
                            .map((kolom) => kolom.map(escape).join(';'))
                            .join('\r\n');

                        const blob = new Blob(['﻿' + baris], { type: 'text/csv;charset=utf-8' });
                        const tautan = document.createElement('a');
                        tautan.href = URL.createObjectURL(blob);
                        tautan.download = 'akun-darin-hasil-impor.csv';
                        tautan.click();
                        URL.revokeObjectURL(tautan.href);
                    });
                </script>
            @endif
        </div>
    @endif

    {{-- ERROR IMPOR --}}
    @if ($errors->import->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
            <p class="mb-2 font-bold">Impor dibatalkan, tidak ada akun yang dibuat. Perbaiki baris berikut lalu unggah ulang:</p>
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->import->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UNGGAH --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div>
                <h2 class="text-lg font-extrabold text-gray-900">Unggah File CSV</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Maksimal {{ $maxRows }} baris per file. Pemisah kolom <b>;</b> (format Excel Indonesia) atau <b>,</b>.
                </p>
            </div>

            <a href="{{ route('admin.users.import.template') }}"
               class="inline-flex shrink-0 items-center justify-center rounded-xl border border-amber-200 px-4 py-2.5 text-sm font-bold text-amber-700 hover:bg-amber-50">
                Unduh Template
            </a>
        </div>

        <form action="{{ route('admin.users.import.store') }}" method="POST" enctype="multipart/form-data"
              class="flex flex-col gap-3 sm:flex-row sm:items-center">
            @csrf

            <input type="file" name="file" accept=".csv,text/csv" required
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">

            <button type="submit"
                    class="shrink-0 rounded-xl bg-[#ffc107] px-6 py-3 text-sm font-bold text-[#191712] hover:bg-amber-400">
                Impor Pengguna
            </button>
        </form>
    </div>

    {{-- PANDUAN KOLOM --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-extrabold text-gray-900">Panduan Kolom</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Kolom</th>
                        <th class="px-4 py-3">Wajib</th>
                        <th class="px-4 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <tr><td class="px-4 py-2 font-mono">nama</td><td class="px-4 py-2">Ya</td><td class="px-4 py-2">Nama lengkap.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">email</td><td class="px-4 py-2">Ya</td><td class="px-4 py-2">Harus unik, belum terdaftar.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">role</td><td class="px-4 py-2">Ya</td><td class="px-4 py-2"><b>magang</b> atau <b>mentor</b>. Akun admin tidak bisa diimpor.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">divisi</td><td class="px-4 py-2">Tidak</td><td class="px-4 py-2">Nama divisi yang sudah ada{{ $divisions->isNotEmpty() ? ': ' . $divisions->join(', ') : '' }}.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">lokasi</td><td class="px-4 py-2">Tidak</td><td class="px-4 py-2">Khusus magang. Nama lokasi absensi yang aktif{{ $locations->isNotEmpty() ? ': ' . $locations->join(', ') : '' }}. Jika diisi, peserta langsung bisa absen.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">email_mentor</td><td class="px-4 py-2">Tidak</td><td class="px-4 py-2">Khusus magang. Email mentor yang sudah ada atau ikut diimpor di file yang sama.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">tanggal_mulai, tanggal_selesai</td><td class="px-4 py-2">Tidak</td><td class="px-4 py-2">Khusus magang. YYYY-MM-DD atau DD/MM/YYYY, diisi keduanya.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">nim, universitas, prodi</td><td class="px-4 py-2">Tidak</td><td class="px-4 py-2">Khusus magang.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">nip, jabatan</td><td class="px-4 py-2">Tidak</td><td class="px-4 py-2">Khusus mentor.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">telepon</td><td class="px-4 py-2">Tidak</td><td class="px-4 py-2">Nomor HP/WhatsApp.</td></tr>
                    <tr><td class="px-4 py-2 font-mono">password</td><td class="px-4 py-2">Tidak</td><td class="px-4 py-2">Minimal 8 karakter. Kosongkan agar dibuat otomatis.</td></tr>
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-xs text-gray-400">
            Jika kolom lokasi dikosongkan, lokasi kerja peserta dapat diatur nanti per pengguna di halaman Manajemen Pengguna.
        </p>
    </div>
</div>
@endsection
