@extends('layouts.admin-layout')

@section('title', 'Reset Data Sistem')
@section('page_title', 'Reset Data Sistem (Danger Zone)')
@section('page_subtitle', 'Area kritis untuk mengosongkan data peserta yang periode magangnya sudah selesai.')

@section('content')
@php
    /*
     * Satu kartu = satu aksi reset. Form dicek di server:
     * password akun admin + kata konfirmasi wajib benar.
     */
    $kartu = [
        [
            'target' => 'attendance',
            'route' => 'admin.reset-data.attendance',
            'judul' => 'Kosongkan Data Absensi & Foto',
            'deskripsi' => 'Menghapus riwayat presensi (Clock In / Clock Out) beserta file foto selfie dari storage server.',
            'jumlah' => $counts['attendances'] . ' data absensi',
            'tombol' => 'Hapus Absensi',
            'ikon' => '🗑️',
            'bahaya' => false,
        ],
        [
            'target' => 'logbooks',
            'route' => 'admin.reset-data.logbooks',
            'judul' => 'Kosongkan Riwayat Logbook',
            'deskripsi' => 'Menghapus catatan aktivitas harian, termasuk status approval dan umpan balik mentor.',
            'jumlah' => $counts['logbooks'] . ' logbook',
            'tombol' => 'Hapus Logbook',
            'ikon' => '🗑️',
            'bahaya' => false,
        ],
        [
            'target' => 'users',
            'route' => 'admin.reset-data.users',
            'judul' => 'Hapus Akun Peserta Selesai',
            'deskripsi' => 'Menghapus akun peserta beserta seluruh absensi, logbook, pengajuan izin, saran, foto profil, foto selfie, dan dokumen izinnya. Akun Mentor, Admin, dan HR tetap aman.',
            'jumlah' => $counts['interns'] . ' akun peserta',
            'tombol' => 'Hapus Akun',
            'ikon' => '🚨',
            'bahaya' => true,
        ],
    ];
@endphp

<div class="mx-auto max-w-4xl space-y-6">

    {{-- ALERT SUCCESS --}}
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- WARNING BANNER --}}
    <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-5 text-sm text-red-700 shadow-sm">
        <div class="flex gap-4">
            <span class="mt-0.5 text-2xl">⚠️</span>
            <div>
                <strong class="block text-base font-bold">Peringatan Area Kritis (Danger Zone)</strong>
                <p class="mt-1 leading-relaxed">
                    Tindakan di halaman ini bersifat <strong>permanen dan tidak dapat dibatalkan</strong>.
                    Lakukan <i>backup</i> database atau <i>Export</i> rekap (PDF/CSV) terlebih dahulu.
                </p>
                <p class="mt-2 leading-relaxed">
                    Reset <strong>hanya</strong> berlaku untuk peserta yang periode magangnya berakhir sebelum
                    {{ $today->locale('id')->translatedFormat('d F Y') }}
                    (saat ini <strong>{{ $counts['interns'] }} peserta</strong>).
                    Peserta yang masih aktif, mentor, admin, divisi, lokasi, kalender libur,
                    dan pengaturan sistem tidak akan terhapus.
                </p>
            </div>
        </div>
    </div>

    @foreach ($kartu as $item)
        @php
            $aktif = old('target') === $item['target'];
            $kosong = str_starts_with($item['jumlah'], '0 ');
        @endphp

        <div id="reset-{{ $item['target'] }}"
             class="rounded-2xl border bg-white p-6 shadow-sm {{ $item['bahaya'] ? 'border-l-4 border-red-200 border-l-red-600' : 'border-gray-200' }}">

            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                <div>
                    <h3 class="text-base font-bold text-slate-800">{{ $item['judul'] }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $item['deskripsi'] }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                    {{ $item['jumlah'] }}
                </span>
            </div>

            @if ($aktif && $errors->any())
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route($item['route']) }}" method="POST"
                  class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:items-end"
                  onsubmit="return confirm('PERINGATAN: {{ $item['judul'] }} untuk peserta yang magangnya sudah selesai akan dihapus permanen.\n\nLanjutkan?');">
                @csrf
                @method('DELETE')
                <input type="hidden" name="target" value="{{ $item['target'] }}">

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="password-{{ $item['target'] }}">
                        Password akun Anda
                    </label>
                    <input type="password" id="password-{{ $item['target'] }}" name="password" required
                           autocomplete="current-password"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-red-400">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="confirmation-{{ $item['target'] }}">
                        Ketik <span class="font-mono font-bold text-red-600">{{ $confirmWord }}</span>
                    </label>
                    <input type="text" id="confirmation-{{ $item['target'] }}" name="confirmation" required
                           autocomplete="off" placeholder="{{ $confirmWord }}"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2.5 font-mono text-sm outline-none focus:border-red-400">
                </div>

                <button type="submit" @disabled($kosong)
                        class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition disabled:cursor-not-allowed disabled:opacity-40
                        {{ $item['bahaya'] ? 'bg-red-700 hover:bg-red-800' : 'bg-red-600 hover:bg-red-700' }}">
                    <span>{{ $item['ikon'] }}</span> {{ $item['tombol'] }}
                </button>
            </form>
        </div>
    @endforeach

</div>
@endsection
