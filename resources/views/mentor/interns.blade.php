<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anak Magang - DARIN Mentor</title>
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
                    <a href="/mentor/interns" class="flex items-center gap-3 px-4 py-3 bg-[#ffc107] text-black rounded-xl shadow-md">
                        👨‍🎓 Daftar Anak Magang
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition">
                        📝 Validasi Logbook
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition">
                        📅 Mentoring & Kehadiran
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition">
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
                <h2 class="text-lg font-bold text-gray-800">Daftar Anak Magang Bimbingan</h2>
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

            <!-- Isi Halaman Daftar Anak Magang -->
            <main class="p-8 space-y-6">
                
                <!-- Info Header & Search Bar -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
                    <div>
                        <h3 class="text-base font-extrabold text-black">Mahasiswa / Siswa Magang Aktif</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola data diri, profil, dan kontak anak magang yang berada di bawah bimbingan Anda.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <input type="text" placeholder="Cari nama atau universitas..." 
                            class="w-full md:w-64 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                    </div>
                </div>

                <!-- Grid Kartu Daftar Anak Magang -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Card 1 -->
                    <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 bg-amber-100 text-amber-800 rounded-2xl flex items-center justify-center font-bold text-lg shadow-inner">
                                    MS
                                </div>
                                <div>
                                    <h4 class="text-sm font-extrabold text-gray-900">Merry Silitonga</h4>
                                    <p class="text-[11px] font-semibold text-gray-500">NIM: 12230456</p>
                                    <span class="inline-block mt-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-md">Aktif</span>
                                </div>
                            </div>

                            <div class="space-y-2 text-xs border-t border-gray-100 pt-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Universitas:</span>
                                    <span class="font-bold text-gray-800">UBSI</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Program Studi:</span>
                                    <span class="font-bold text-gray-800">Informatika</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">No. WhatsApp:</span>
                                    <span class="font-bold text-gray-800">081234567890</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Email:</span>
                                    <span class="font-bold text-gray-800 truncate max-w-[150px]">merry@student.ubsi.ac.id</span>
                                </div>
                            </div>
                        </div>

                        <!-- Aksi Detail / Kontak -->
                        <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                            <a href="https://wa.me/6281234567890" target="_blank" class="flex-1 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-center font-bold rounded-xl text-xs transition">
                                💬 WhatsApp
                            </a>
                            <button onclick="openDetailModal('Merry Silitonga', '12230456', 'Universitas Bina Sarana Informatika (UBSI)', 'Informatika', '081234567890', 'merry@student.ubsi.ac.id')" class="flex-1 py-2 bg-[#ffc107] hover:bg-[#e0ac06] text-black text-center font-bold rounded-xl text-xs transition shadow-sm">
                                👁️ Profil Lengkap
                            </button>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 bg-blue-100 text-blue-800 rounded-2xl flex items-center justify-center font-bold text-lg shadow-inner">
                                    AF
                                </div>
                                <div>
                                    <h4 class="text-sm font-extrabold text-gray-900">Ahmad Fauzi</h4>
                                    <p class="text-[11px] font-semibold text-gray-500">NIM: 12230789</p>
                                    <span class="inline-block mt-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-md">Aktif</span>
                                </div>
                            </div>

                            <div class="space-y-2 text-xs border-t border-gray-100 pt-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Universitas:</span>
                                    <span class="font-bold text-gray-800">Universitas Indonesia</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Program Studi:</span>
                                    <span class="font-bold text-gray-800">Ilmu Komputer</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">No. WhatsApp:</span>
                                    <span class="font-bold text-gray-800">089876543210</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Email:</span>
                                    <span class="font-bold text-gray-800 truncate max-w-[150px]">ahmad.fauzi@ui.ac.id</span>
                                </div>
                            </div>
                        </div>

                        <!-- Aksi Detail / Kontak -->
                        <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                            <a href="https://wa.me/6289876543210" target="_blank" class="flex-1 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-center font-bold rounded-xl text-xs transition">
                                💬 WhatsApp
                            </a>
                            <button onclick="openDetailModal('Ahmad Fauzi', '12230789', 'Universitas Indonesia', 'Ilmu Komputer', '089876543210', 'ahmad.fauzi@ui.ac.id')" class="flex-1 py-2 bg-[#ffc107] hover:bg-[#e0ac06] text-black text-center font-bold rounded-xl text-xs transition shadow-sm">
                                👁️ Profil Lengkap
                            </button>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- Modal Detail Profil -->
    <div id="detailModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-md rounded-3xl p-6 space-y-4 shadow-2xl relative">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h4 class="text-sm font-extrabold text-gray-900">Detail Profil Anak Magang</h4>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>
            
            <div class="space-y-3 text-xs">
                <div>
                    <p class="text-gray-400 font-medium">Nama Lengkap</p>
                    <p id="modal-name" class="font-bold text-gray-800 text-sm">-</p>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <p class="text-gray-400 font-medium">NIM</p>
                        <p id="modal-nim" class="font-bold text-gray-800">-</p>
                    </div>
                    <div>
                        <p class="text-gray-400 font-medium">No. WhatsApp</p>
                        <p id="modal-phone" class="font-bold text-gray-800">-</p>
                    </div>
                </div>
                <div>
                    <p class="text-gray-400 font-medium">Asal Universitas</p>
                    <p id="modal-university" class="font-bold text-gray-800">-</p>
                </div>
                <div>
                    <p class="text-gray-400 font-medium">Program Studi</p>
                    <p id="modal-study" class="font-bold text-gray-800">-</p>
                </div>
                <div>
                    <p class="text-gray-400 font-medium">Alamat Email</p>
                    <p id="modal-email" class="font-bold text-gray-800">-</p>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex justify-end">
                <button onclick="closeDetailModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Script Modal -->
    <script>
        function openDetailModal(name, nim, university, study, phone, email) {
            document.getElementById('modal-name').innerText = name;
            document.getElementById('modal-nim').innerText = nim;
            document.getElementById('modal-university').innerText = university;
            document.getElementById('modal-study').innerText = study;
            document.getElementById('modal-phone').innerText = phone;
            document.getElementById('modal-email').innerText = email;
            document.getElementById('detailModal').classList.remove('hidden');
            document.getElementById('detailModal').classList.add('flex');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('flex');
            document.getElementById('detailModal').classList.add('hidden');
        }
    </script>
</body>
</html>