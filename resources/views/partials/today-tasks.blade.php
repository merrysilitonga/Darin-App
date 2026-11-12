{{--
    Kartu "Tugas Hari Ini" untuk peserta magang.
    Variabel: $todayTasks (array dari App\Services\InternTodayService)
--}}
@php
    $tugas = collect($todayTasks ?? []);

    // Banner libur sudah tampil dari partial holiday-info.
    if (!empty($todayHoliday)) {
        $tugas = $tugas->reject(fn ($item) => $item['key'] === 'day-off');
    }

    $belumSelesai = $tugas->whereIn('state', ['todo', 'missed'])->count();

    $gaya = [
        'done' => ['ikon' => '✓', 'bulat' => 'bg-green-100 text-green-700', 'judul' => 'text-gray-500'],
        'todo' => ['ikon' => '!', 'bulat' => 'bg-amber-100 text-amber-700', 'judul' => 'text-gray-900'],
        'missed' => ['ikon' => '✕', 'bulat' => 'bg-red-100 text-red-700', 'judul' => 'text-red-700'],
        'info' => ['ikon' => 'i', 'bulat' => 'bg-blue-100 text-blue-700', 'judul' => 'text-gray-800'],
    ];
@endphp

@if ($tugas->isNotEmpty())
    <section class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-bold text-gray-900">📌 Tugas Hari Ini</h2>

            @if ($belumSelesai > 0)
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                    {{ $belumSelesai }} perlu ditindaklanjuti
                </span>
            @else
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                    Semua beres
                </span>
            @endif
        </div>

        <ul class="divide-y divide-gray-100">
            @foreach ($tugas as $item)
                @php $g = $gaya[$item['state']] ?? $gaya['info']; @endphp

                <li class="flex flex-wrap items-center gap-3 py-3" data-task="{{ $item['key'] }}" data-state="{{ $item['state'] }}">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $g['bulat'] }}">
                        {{ $g['ikon'] }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold {{ $g['judul'] }}">{{ $item['title'] }}</p>
                        <p class="text-xs text-gray-500">{{ $item['description'] }}</p>
                    </div>

                    @if ($item['url'] && $item['action'])
                        <a href="{{ $item['url'] }}"
                           class="shrink-0 rounded-lg px-3 py-2 text-xs font-bold transition
                           {{ $item['state'] === 'info' ? 'border border-gray-200 text-gray-600 hover:bg-gray-50' : 'bg-[#ffc107] text-[#191712] hover:bg-amber-400' }}">
                            {{ $item['action'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
@endif
