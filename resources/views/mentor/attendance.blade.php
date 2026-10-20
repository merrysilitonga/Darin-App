<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentoring & Kehadiran - DARIN Mentor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f8f9fa] text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar Mentor -->
        <aside class="w-64 bg-[#191712] text-white flex flex-col justify-between hidden md:flex">
            <div>
                <!-- Logo & Brand -->
                <div class="p-6 flex items-center gap-3 border-b border-white/10">
                    <div class="bg-[#ffc107] text-black font-bold p-2.5 rounded-xl text-lg shadow-md">⚡</div>
                    <div>
                        <h1 class="font-extrabold tracking-wider text-lg">DARIN</h1>
                        <p class="text-[9px] uppercase tracking-widest text-amber-400 font-bold">Portal Mentor</p>
                    </div>
                </div>

                <!-- Navigasi Menu -->
                <nav class="p-4 space-y-1.5 text-xs font-semibold">
                    <a href="/mentor/dashboard" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition">
                        📊 Dashboard
                    </a>
                    <a href="/mentor/interns" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition">
                        👨‍🎓 Daftar Anak Magang
                    </a>
                    <a href="/mentor/logbook" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition">
                        📝 Validasi Logbook
                    </a>
                    <a href="/mentor/attendance" class="flex items-center gap-3 px-4 py-3 bg-[#ffc107] text-black rounded-xl shadow-md">
                        📅 Mentoring & Kehadiran
                    </a>
                    <a href="/mentor/assessment" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition">
                        ⭐ Penilaian Magang
                    </a>
                </nav>
            </div>

            <!-- Tombol Logout -->
            <div class="p-4 border-t border-white/10">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white rounded-xl text-xs font-bold transition">
                        🚪 Keluar / Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Konten Utama -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            
            <!-- Topbar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between shadow-sm">
                <h2 class="text-lg font-bold text-gray-800">Mentoring & Rekap Kehadiran</h2>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs font-bold text-gray-800">Budi Santoso, M.Kom.</p>
                        <p class="text-[10px] text-gray-500">Mentor / Pembimbing</p>
                    </div>
                    <div class="w-10 h-10 bg-[#ffc107] rounded-xl flex items-center justify-center font-bold text-black shadow-inner">
                        BS
                    </div>
                </div>
            </header>

            <!-- Isi Halaman -->
            <main class="p-8 space-y-6">
                
                <!-- Bagian 1: Persetujuan Pengajuan Izin / Sakit -->
                <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-extrabold text-black">📬 Pengajuan Izin & Sakit (Menunggu Persetujuan)</h3>
                        <span class="px-3 py-1 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full">1 Pengajuan Baru</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase">
                                    <th class="py-3 px-4">Nama Mahasiswa</th>
                                    <th class="py-3 px-4">Jenis</th>
                                    <th class="py-3 px-4">Tanggal / Waktu</th>
                                    <th class="py-3 px-4">Alasan / Keterangan</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-gray-50">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-bold text-gray-800">Ahmad Fauzi</td>
                                    <td class="py-3 px-4"><span class="px-2 py-1 bg-blue-50 text-blue-700 font-bold rounded-lg text-[10px]">Izin Sakit</span></td>
                                    <td class="py-3 px-4 text-gray-600">30 September 2026</td>
                                    <td class="py-3 px-4 text-gray-600">Demam dan harus beristirahat (Surat dokter terlampir).</td>
                                    <td class="py-3 px-4 text-center space-x-1">
                                        <button class="px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-[11px] transition shadow-sm">Setujui</button>
                                        <button class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-600 font-bold rounded-xl text-[11px] transition">Tolak</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bagian 2: Rekap Absensi Seluruh Anak Magang -->
                <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="text-sm font-extrabold text-black">📊 Rekapitulasi Absensi Bulanan</h3>
                            <p class="text-xs text-gray-500">Akumulasi kehadiran anak magang bimbingan periode September 2026.</p>
                        </div>
                        <div>
                            <select class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:border-[#ffc107]">
                                <option>September 2026</option>
                                <option>Agustus 2026</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase">
                                    <th class="py-3 px-4">No</th>
                                    <th class="py-3 px-4">Nama Mahasiswa</th>
                                    <th class="py-3 px-4 text-center">Hadir (H)</th>
                                    <th class="py-3 px-4 text-center">Izin (I)</th>
                                    <th class="py-3 px-4 text-center">Sakit (S)</th>
                                    <th class="py-3 px-4 text-center">Alpa (A)</th>
                                    <th class="py-3 px-4 text-center">Total Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-gray-50">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 text-gray-500 font-semibold">1</td>
                                    <td class="py-3 px-4 font-bold text-gray-900">Merry Silitonga</td>
                                    <td class="py-3 px-4 text-center font-bold text-emerald-600">20</td>
                                    <td class="py-3 px-4 text-center font-bold text-amber-600">0</td>
                                    <td class="py-3 px-4 text-center font-bold text-blue-600">0</td>
                                    <td class="py-3 px-4 text-center font-bold text-red-600">0</td>
                                    <td class="py-3 px-4 text-center"><span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-lg text-[10px]">100%</span></td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 text-gray-500 font-semibold">2</td>
                                    <td class="py-3 px-4 font-bold text-gray-900">Ahmad Fauzi</td>
                                    <td class="py-3 px-4 text-center font-bold text-emerald-600">18</td>
                                    <td class="py-3 px-4 text-center font-bold text-amber-600">1</td>
                                    <td class="py-3 px-4 text-center font-bold text-blue-600">1</td>
                                    <td class="py-3 px-4 text-center font-bold text-red-600">0</td>
                                    <td class="py-3 px-4 text-center"><span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-lg text-[10px]">90%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>
</body>
</html>