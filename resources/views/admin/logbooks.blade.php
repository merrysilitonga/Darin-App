@extends('layouts.admin-layout')

@section('title', 'Monitoring Logbook Lintas Unit')
@section('page_title', 'Monitoring Logbook Peserta')
@section('page_subtitle', 'Pantau seluruh aktivitas harian peserta magang dari berbagai divisi.')

@section('content')
<div class="space-y-6">

    {{-- FILTER BULAN --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Filter Berdasarkan Bulan</h3>
            <p class="text-xs text-slate-500 mt-1">Tampilkan logbook berdasarkan periode aktivitas.</p>
        </div>
        
        <form method="GET" action="{{ route('admin.logbooks') }}" id="filterLogbookForm">
            <select name="bulan" onchange="document.getElementById('filterLogbookForm').submit();"
                class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-slate-700 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition">
                @for($i = 0; $i < 6; $i++)
                    @php $m = \Carbon\Carbon::now('Asia/Jakarta')->subMonths($i)->format('Y-m'); @endphp
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::now('Asia/Jakarta')->subMonths($i)->translatedFormat('F Y') }}
                    </option>
                @endfor
            </select>
        </form>
    </div>

    {{-- TABEL LOGBOOK --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-5 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">Daftar Aktivitas Logbook</h3>
            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700">
                Total: {{ $logbooks->total() }} Data
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Peserta & Divisi</th>
                        <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Mentor</th>
                        <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 min-w-[250px]">Topik & Deskripsi</th>
                        <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-center whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logbooks as $log)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-700">{{ \Carbon\Carbon::parse($log->date)->translatedFormat('d M Y') }}</span>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <p class="text-xs font-bold text-slate-800">{{ $log->user->name ?? 'User Dihapus' }}</p>
                                <p class="text-[11px] text-slate-500 mt-1">{{ $log->user->division->name ?? 'Tanpa Divisi' }}</p>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <p class="text-xs font-medium text-slate-700">{{ $log->user->mentor->name ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <p class="text-xs font-bold text-slate-800 mb-1">{{ $log->topic }}</p>
                                <p class="text-[11px] text-slate-600 leading-relaxed line-clamp-3">{{ $log->description }}</p>
                            </td>
                            <td class="px-6 py-4 align-top text-center">
                                @php
                                    $statusColor = match($log->status) {
                                        'Disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                        'Ditolak' => 'bg-red-50 text-red-600 border-red-200',
                                        'Menunggu' => 'bg-blue-50 text-blue-600 border-blue-200',
                                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-[11px] font-semibold border {{ $statusColor }}">
                                    {{ $log->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <div class="text-3xl mb-3">📝</div>
                                <p class="text-sm font-medium text-slate-500">Belum ada logbook yang diisi pada bulan ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logbooks->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $logbooks->links() }}
            </div>
        @endif
    </div>

</div>
@endsection