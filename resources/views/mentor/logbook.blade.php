@extends('layouts.mentor-layout')

@section('title', 'Validasi Logbook')
@section('page_title', 'Validasi Logbook & Aktivitas Harian')
@section('page_subtitle', 'Tinjau laporan harian anak magang lalu berikan persetujuan atau revisi.')

@section('content')
<div class="space-y-6">

    <!-- Header Info -->
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-gray-700">Daftar Logbook Masuk</h3>
            <p class="text-sm text-gray-500 mt-0.5">Tinjau laporan kegiatan harian yang dikirimkan oleh anak magang, lalu berikan verifikasi atau catatan revisi.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-xl border border-amber-200">Menunggu: 2 Laporan</span>
        </div>
    </div>

    <!-- Daftar Logbook -->
    <div class="space-y-4">

        <!-- Item Logbook 1 -->
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 text-amber-800 rounded-xl flex items-center justify-center font-bold text-sm shadow-inner">
                        MS
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-gray-700">Merry Silitonga <span class="text-xs font-normal text-gray-500">(NIM: 12230456)</span></h4>
                        <p class="text-[11px] text-gray-400">Tanggal Kegiatan: Rabu, 30 September 2026</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full w-fit">Pending / Menunggu Review</span>
            </div>

            <!-- Isi Detail Aktivitas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-2xl">
                <div>
                    <p class="text-gray-400 font-semibold mb-1">Aktivitas / Pekerjaan Harian:</p>
                    <p class="font-medium text-gray-800 leading-relaxed">Melakukan konfigurasi database MySQL untuk sistem rekomendasi kos dan menyusun tampilan antarmuka (UI) form pendaftaran menggunakan Tailwind CSS.</p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold mb-1">Kendala / Catatan Anak Magang:</p>
                    <p class="font-medium text-gray-800 leading-relaxed">Sempat mengalami kendala pada port koneksi XAMPP, namun berhasil diselesaikan dengan mengubah port config.</p>
                </div>
            </div>

            <!-- Form Aksi / Validasi Mentor -->
            <form action="#" method="POST" class="space-y-3 pt-2">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Catatan / Komentar Mentor (Opsional jika disetujui, Wajib jika revisi):</label>
                    <textarea name="mentor_notes" rows="2" placeholder="Tuliskan masukan, saran, atau instruksi revisi di sini..."
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="submit" name="status" value="revisi" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-bold rounded-xl text-xs transition border border-red-200">
                        🔄 Minta Revisi
                    </button>
                    <button type="submit" name="status" value="setuju" class="px-5 py-2 bg-[#ffc107] hover:bg-[#e0ac06] text-black font-bold rounded-xl text-xs transition shadow-sm">
                        ✅ Setujui Logbook
                    </button>
                </div>
            </form>
        </div>

        <!-- Item Logbook 2 -->
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 text-blue-800 rounded-xl flex items-center justify-center font-bold text-sm shadow-inner">
                        AF
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-gray-700">Ahmad Fauzi <span class="text-xs font-normal text-gray-500">(NIM: 12230789)</span></h4>
                        <p class="text-[11px] text-gray-400">Tanggal Kegiatan: Selasa, 29 September 2026</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full w-fit">Telah Disetujui</span>
            </div>

            <!-- Isi Detail Aktivitas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-2xl">
                <div>
                    <p class="text-gray-400 font-semibold mb-1">Aktivitas / Pekerjaan Harian:</p>
                    <p class="font-medium text-gray-800 leading-relaxed">Mengikuti briefing mingguan bersama supervisor divisi teknologi informasi.</p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold mb-1">Catatan / Komentar Mentor:</p>
                    <p class="font-medium text-emerald-700 italic">"Bagus, pertahankan keaktifan saat diskusi."</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection