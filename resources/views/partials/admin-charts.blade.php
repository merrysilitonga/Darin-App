{{--
    Grafik dashboard admin.
    Variabel: $adminCharts (array dari App\Services\AdminChartService, diisi View Composer).
    Memakai inline style untuk ukuran grafik agar tetap rapi tanpa build Tailwind.
--}}
@php
    $grafik = $adminCharts ?? null;

    $warna = [
        'hadir' => '#10b981',
        'terlambat' => '#f59e0b',
        'izin' => '#3b82f6',
        'alfa' => '#ef4444',
        'belum' => '#d1d5db',
    ];

    $label = [
        'hadir' => 'Tepat waktu',
        'terlambat' => 'Terlambat',
        'izin' => 'Izin/Sakit',
        'alfa' => 'Alfa',
        'belum' => 'Belum absen',
    ];
@endphp

@if ($grafik)
<section id="grafik-kehadiran" style="margin: 24px 0;">

    {{-- RINGKASAN HARI INI --}}
    @php $hariIni = $grafik['today']; @endphp

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;">
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Peserta aktif hari ini</p>
            <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ $hariIni['off'] ? '-' : $hariIni['active'] }}</p>
        </div>

        @foreach (['hadir', 'terlambat', 'izin', 'belum'] as $kunci)
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">
                    <span style="display:inline-block;width:8px;height:8px;border-radius:9999px;background:{{ $warna[$kunci] }};margin-right:4px;"></span>
                    {{ $label[$kunci] }}
                </p>
                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ $hariIni['off'] ? '-' : $hariIni[$kunci] }}</p>
            </div>
        @endforeach
    </div>

    @if ($hariIni['off'])
        <p class="mb-4 text-sm text-gray-500">Hari ini bukan hari kerja ({{ $hariIni['off'] }}).</p>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;">

        {{-- TREN KEHADIRAN --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" style="min-width:0;">
            <div class="mb-4">
                <h3 class="text-base font-bold text-gray-900">Tren Kehadiran {{ $grafik['days'] }} Hari Terakhir</h3>
                <p class="mt-1 text-xs text-gray-500">Jumlah peserta per status setiap hari kerja. Arahkan kursor ke batang untuk detail.</p>
            </div>

            <div style="display:flex;align-items:flex-end;gap:3px;height:180px;border-bottom:1px solid #e5e7eb;padding-bottom:2px;">
                @foreach ($grafik['trend'] as $hari)
                    @php
                        $judul = $hari['date']->locale('id')->translatedFormat('D, d M Y');

                        if ($hari['off']) {
                            $judul .= ' — ' . $hari['off'];
                        } else {
                            $judul .= " — Aktif {$hari['active']}: tepat waktu {$hari['hadir']}, terlambat {$hari['terlambat']}, izin/sakit {$hari['izin']}, "
                                . ($hari['is_today'] ? "belum absen {$hari['belum']}" : "alfa {$hari['alfa']}")
                                . ($hari['rate'] !== null ? " ({$hari['rate']}% hadir)" : '');
                        }
                    @endphp

                    <div title="{{ $judul }}"
                         style="flex:1;min-width:4px;height:100%;display:flex;flex-direction:column-reverse;{{ $hari['is_today'] ? 'outline:2px solid #fbbf24;outline-offset:1px;border-radius:2px;' : '' }}">
                        @if ($hari['off'])
                            <div style="height:6px;background:#f3f4f6;border-radius:2px;"></div>
                        @else
                            @foreach (['hadir', 'terlambat', 'izin', 'alfa', 'belum'] as $kunci)
                                @if ($hari[$kunci] > 0)
                                    <div style="height:{{ round($hari[$kunci] / $grafik['trendMax'] * 100, 2) }}%;background:{{ $warna[$kunci] }};"></div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>

            <div style="display:flex;justify-content:space-between;margin-top:6px;" class="text-[11px] text-gray-400">
                <span>{{ $grafik['trend'][0]['date']->locale('id')->translatedFormat('d M') }}</span>
                <span>Hari ini</span>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:12px;" class="text-xs text-gray-600">
                @foreach (['hadir', 'terlambat', 'izin', 'alfa', 'belum'] as $kunci)
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span style="width:10px;height:10px;border-radius:2px;background:{{ $warna[$kunci] }};"></span>
                        {{ $label[$kunci] }}
                    </span>
                @endforeach
                <span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="width:10px;height:10px;border-radius:2px;background:#f3f4f6;"></span>
                    Libur/akhir pekan
                </span>
            </div>
        </div>

        {{-- KEHADIRAN PER DIVISI --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" style="min-width:0;">
            <div class="mb-4">
                <h3 class="text-base font-bold text-gray-900">Kehadiran per Divisi — {{ $grafik['monthLabel'] }}</h3>
                <p class="mt-1 text-xs text-gray-500">Persentase hari kerja peserta yang diisi absen masuk (bulan berjalan).</p>
            </div>

            @forelse ($grafik['divisions'] as $divisi)
                @php
                    $warnaBatang = $divisi['rate'] >= 90 ? '#10b981' : ($divisi['rate'] >= 75 ? '#f59e0b' : '#ef4444');
                @endphp

                <div style="margin-bottom:12px;" title="{{ $divisi['present'] }} dari {{ $divisi['workdays'] }} hari kerja, {{ $divisi['interns'] }} peserta">
                    <div style="display:flex;justify-content:space-between;gap:8px;" class="text-sm">
                        <span class="font-semibold text-gray-700">{{ $divisi['name'] }}</span>
                        <span class="font-bold text-gray-900">{{ $divisi['rate'] }}%</span>
                    </div>
                    <div style="height:10px;background:#f3f4f6;border-radius:9999px;overflow:hidden;margin-top:4px;">
                        <div style="height:100%;width:{{ $divisi['rate'] }}%;background:{{ $warnaBatang }};border-radius:9999px;"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $divisi['interns'] }} peserta · {{ $divisi['present'] }}/{{ $divisi['workdays'] }} hari</p>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-gray-400">Belum ada data kehadiran bulan ini.</p>
            @endforelse

            <p class="mt-2 text-[11px] text-gray-400">Hijau ≥ 90% · Kuning ≥ 75% · Merah &lt; 75%</p>
        </div>
    </div>
</section>
@endif
