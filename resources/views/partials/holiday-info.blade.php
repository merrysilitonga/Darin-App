{{--
    Informasi hari libur untuk peserta magang.
    Variabel: $todayHoliday (?Holiday), $upcomingHolidays (Collection<Holiday>)
--}}
@php
    $todayHoliday = $todayHoliday ?? null;
    $upcomingHolidays = $upcomingHolidays ?? collect();
@endphp

@if ($todayHoliday)
    <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
        <span class="text-xl leading-none">🏖️</span>
        <div>
            <p class="font-bold">
                Hari ini libur: {{ $todayHoliday->name }}
            </p>
            <p class="mt-0.5">
                {{ $todayHoliday->type === 'Nasional' ? 'Libur Nasional' : 'Libur ' . $todayHoliday->type }}.
                Absensi masuk tidak tersedia hari ini.
                @if ($todayHoliday->description)
                    <span class="text-amber-700">{{ $todayHoliday->description }}</span>
                @endif
            </p>
        </div>
    </div>
@endif

@if ($upcomingHolidays->isNotEmpty())
    <div class="mb-6 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm">
        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">
            📅 Hari libur 30 hari ke depan
        </p>

        <ul class="divide-y divide-gray-100 text-sm">
            @foreach ($upcomingHolidays as $libur)
                <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <span class="font-semibold text-gray-800">{{ $libur->name }}</span>
                    <span class="text-gray-500">
                        {{ $libur->date->locale('id')->translatedFormat('l, d F Y') }}
                        <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-bold {{ $libur->type === 'Nasional' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                            {{ $libur->type }}
                        </span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
