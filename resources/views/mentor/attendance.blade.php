@extends('layouts.mentor-layout')

@section('title', 'Mentoring & Kehadiran')
@section('page_title', 'Mentoring & Rekap Kehadiran')
@section('page_subtitle', 'Setujui pengajuan izin atau sakit dan pantau rekap absensi bulanan.')

@section('content')
<div class="space-y-5">

    {{-- BAGIAN 1: PERSETUJUAN IZIN DAN SAKIT --}}
    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-sm font-semibold leading-6 text-slate-700">
                    Pengajuan Izin &amp; Sakit
                </h3>
                <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                    Daftar pengajuan mahasiswa yang menunggu persetujuan.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700">
                1 Pengajuan Baru
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="whitespace-nowrap px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Nama Mahasiswa
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Jenis
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Tanggal / Waktu
                        </th>
                        <th class="min-w-64 px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Alasan / Keterangan
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-center text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50">
                    <tr class="transition hover:bg-slate-50/70">
                        <td class="whitespace-nowrap px-3 py-3">
                            <span class="text-xs font-semibold text-slate-700">
                                Ahmad Fauzi
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-3 py-3">
                            <span class="inline-flex rounded-lg bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700">
                                Izin Sakit
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-xs font-normal text-slate-500">
                            30 September 2026
                        </td>

                        <td class="min-w-64 px-3 py-3 text-xs font-normal leading-5 text-slate-500">
                            Demam dan harus beristirahat (surat dokter terlampir).
                        </td>

                        <td class="whitespace-nowrap px-3 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    class="rounded-lg bg-emerald-500 px-3 py-1.5 text-[11px] font-medium text-white transition hover:bg-emerald-600"
                                >
                                    Setujui
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-[11px] font-medium text-red-600 transition hover:bg-red-100"
                                >
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- BAGIAN 2: REKAPITULASI ABSENSI --}}
    <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-4 border-b border-gray-100 pb-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-sm font-semibold leading-6 text-slate-700">
                    Rekapitulasi Absensi Bulanan
                </h3>

                <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                    Akumulasi kehadiran mahasiswa magang bimbingan periode September 2026.
                </p>
            </div>

            <div>
                <label for="attendance_month" class="sr-only">
                    Pilih bulan rekap absensi
                </label>

                <select
                    id="attendance_month"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-normal text-slate-600 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                >
                    <option value="2026-09">September 2026</option>
                    <option value="2026-08">Agustus 2026</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="whitespace-nowrap px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            No.
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Nama Mahasiswa
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-center text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Hadir (H)
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-center text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Izin (I)
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-center text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Sakit (S)
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-center text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Alpa (A)
                        </th>
                        <th class="whitespace-nowrap px-3 py-3 text-center text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Persentase Kehadiran
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50">
                    <tr class="transition hover:bg-slate-50/70">
                        <td class="px-3 py-3 text-xs font-normal text-slate-500">
                            1
                        </td>

                        <td class="whitespace-nowrap px-3 py-3">
                            <span class="text-xs font-semibold text-slate-700">
                                Merry Silitonga
                            </span>
                        </td>

                        <td class="px-3 py-3 text-center text-xs font-medium text-emerald-600">
                            20
                        </td>

                        <td class="px-3 py-3 text-center text-xs font-medium text-amber-600">
                            0
                        </td>

                        <td class="px-3 py-3 text-center text-xs font-medium text-blue-600">
                            0
                        </td>

                        <td class="px-3 py-3 text-center text-xs font-medium text-red-500">
                            0
                        </td>

                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                                100%
                            </span>
                        </td>
                    </tr>

                    <tr class="transition hover:bg-slate-50/70">
                        <td class="px-3 py-3 text-xs font-normal text-slate-500">
                            2
                        </td>

                        <td class="whitespace-nowrap px-3 py-3">
                            <span class="text-xs font-semibold text-slate-700">
                                Ahmad Fauzi
                            </span>
                        </td>

                        <td class="px-3 py-3 text-center text-xs font-medium text-emerald-600">
                            18
                        </td>

                        <td class="px-3 py-3 text-center text-xs font-medium text-amber-600">
                            1
                        </td>

                        <td class="px-3 py-3 text-center text-xs font-medium text-blue-600">
                            1
                        </td>

                        <td class="px-3 py-3 text-center text-xs font-medium text-red-500">
                            0
                        </td>

                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                                90%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-xs font-normal leading-5 text-slate-400">
            Catatan: data pada tabel saat ini masih berupa contoh tampilan.
        </p>
    </section>

</div>
@endsection