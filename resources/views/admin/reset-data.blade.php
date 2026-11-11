@extends('layouts.admin-layout')

@section('title', 'Reset Data Sistem')
@section('page_title', 'Reset Data Sistem (Danger Zone)')
@section('page_subtitle', 'Area kritis untuk mengosongkan data lama pada pergantian periode magang.')

@section('content')
<div class="space-y-6 max-w-4xl">

    {{-- ALERT SUCCESS --}}
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- WARNING BANNER --}}
    <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-5 text-sm text-red-700 shadow-sm">
        <div class="flex gap-4">
            <span class="text-2xl mt-0.5">⚠️</span>
            <div>
                <strong class="block font-bold text-base">Peringatan Area Kritis (Danger Zone)</strong>
                <p class="mt-1 leading-relaxed">
                    Tindakan di halaman ini bersifat <strong>permanen dan tidak dapat dibatalkan</strong>. 
                    Pastikan Anda telah melakukan *backup* atau *Export* data (PDF/CSV) sebelum menghapus riwayat dari sistem aplikasi DARIN.
                </p>
            </div>
        </div>
    </div>

    {{-- 1. RESET ABSENSI --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col sm:flex-row gap-6 justify-between items-start sm:items-center">
        <div>
            <h3 class="text-base font-bold text-slate-800">Kosongkan Data Absensi & Foto</h3>
            <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                Menghapus seluruh riwayat presensi harian (Clock In / Clock Out) peserta beserta file <strong>foto selfie</strong> dari <i>storage server</i> untuk membebaskan kapasitas penyimpanan.
            </p>
        </div>
        <form action="{{ route('admin.reset-data.attendance') }}" method="POST" 
              onsubmit="return confirm('PERINGATAN KERAS: Seluruh riwayat kehadiran dan file foto selfie akan dihapus permanen dari server.\n\nApakah Anda benar-benar yakin ingin melanjutkan?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                <span>🗑️</span> Hapus Absensi
            </button>
        </form>
    </div>

    {{-- 2. RESET LOGBOOK --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col sm:flex-row gap-6 justify-between items-start sm:items-center">
        <div>
            <h3 class="text-base font-bold text-slate-800">Kosongkan Riwayat Logbook</h3>
            <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                Menghapus seluruh catatan aktivitas harian magang yang pernah ditulis oleh peserta, termasuk status <i>approval</i> dan umpan balik dari mentor.
            </p>
        </div>
        <form action="{{ route('admin.reset-data.logbooks') }}" method="POST" 
              onsubmit="return confirm('PERINGATAN KERAS: Seluruh catatan logbook dari semua divisi akan dihapus permanen.\n\nApakah Anda benar-benar yakin ingin melanjutkan?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                <span>🗑️</span> Hapus Logbook
            </button>
        </form>
    </div>

    {{-- 3. RESET AKUN PESERTA & MENTOR --}}
    <div class="bg-white rounded-2xl border border-red-200 shadow-sm p-6 flex flex-col sm:flex-row gap-6 justify-between items-start sm:items-center border-l-4 border-l-red-600">
        <div>
            <h3 class="text-base font-bold text-slate-800">Hapus Semua Akun (Peserta & Mentor)</h3>
            <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                Menghapus seluruh akun dengan <i>role</i> <strong>Magang</strong> dan <strong>Mentor</strong> secara serentak. 
                Gunakan fitur ini hanya saat tahun ajaran magang sudah selesai total. 
                <i>(Akun Admin/HR akan tetap aman).</i>
            </p>
        </div>
        <form action="{{ route('admin.reset-data.users') }}" method="POST" 
              onsubmit="return confirm('BAHAYA: Seluruh akun Magang dan Mentor beserta data relasinya akan musnah. Anda harus meregistrasi ulang peserta gelombang baru nanti.\n\nApakah Anda SANGAT YAKIN ingin mengeksekusi perintah ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-red-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-800 transition">
                <span>🚨</span> Hapus Massal Akun
            </button>
        </form>
    </div>

</div>
@endsection