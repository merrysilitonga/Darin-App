{{-- Label status persetujuan Dinas Luar. Variabel: $absen (Attendance). --}}
@if ($absen && $absen->work_location === 'Dinas Luar' && $absen->dinas_status)
    @php
        $kelasDinas = match ($absen->dinas_status) {
            'Disetujui' => 'bg-green-100 text-green-700',
            'Ditolak' => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };
        $teksDinas = match ($absen->dinas_status) {
            'Disetujui' => 'Dinas disetujui Mentor',
            'Ditolak' => 'Dinas ditolak Mentor (tidak dihitung hadir)',
            default => 'Menunggu persetujuan Mentor',
        };
    @endphp
    <span class="mt-1 inline-flex w-fit rounded-full px-2 py-0.5 text-[10px] font-bold {{ $kelasDinas }}">{{ $teksDinas }}</span>
    @if ($absen->dinas_status === 'Ditolak' && $absen->dinas_review_notes)
        <span class="mt-1 block max-w-[14rem] whitespace-normal text-[11px] text-red-600">Alasan: {{ $absen->dinas_review_notes }}</span>
    @endif
@endif
