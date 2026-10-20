<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DARIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#191712] text-white flex items-center justify-center min-h-screen p-4 overflow-y-auto">

    <!-- Container Utama Kartu Login -->
    <div class="w-full max-w-md bg-[#ffffff] text-gray-800 rounded-3xl shadow-2xl overflow-hidden relative border border-white/5 my-8">
        
        <!-- Aksen Garis Hazard Kuning-Hitam di Bagian Atas -->
        <div class="h-3 w-full" style="background: repeating-linear-gradient(45deg, #ffc107, #ffc107 15px, #191712 15px, #191712 30px);"></div>

        <div class="p-8">
            <!-- Logo & Brand -->
            <div class="flex flex-col items-center mb-6">
                <div class="bg-[#191712] text-[#ffc107] font-bold p-3 rounded-2xl flex items-center justify-center text-xl mb-3 shadow-lg">
                    ⚡
                </div>
                <h1 class="text-3xl font-extrabold tracking-wider text-black">DARIN</h1>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mt-1 text-center">DIGITAL ATTENDANCE & REPORTING FOR INTERNSHIP</p>
                <p class="text-xs text-gray-500 mt-1">Silakan login untuk melanjutkan</p>
            </div>

            <!-- Pesan Error / Validasi -->
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-600 text-xs rounded-xl text-center font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Form Login -->
            <form action="/login" method="POST" class="space-y-4">
                @csrf
                
                <!-- Input Email -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="Masukkan email" 
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>

                <!-- Input Password dengan Tombol Mata (Show/Hide) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required placeholder="Masukkan password" 
                            class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition pr-10">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <span id="eye-icon">👁️‍‍🗨️</span>
                        </button>
                    </div>
                </div>

                <!-- Validasi Captcha Kanvas (Samar) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Validasi Keamanan</label>
                    <div class="flex items-center gap-3">
                        <canvas id="captcha-canvas" width="180" height="46" class="bg-gray-100 border border-gray-200 rounded-xl shadow-inner cursor-pointer" onclick="generateCaptcha()" title="Klik untuk mengganti"></canvas>
                        <button type="button" onclick="generateCaptcha()" class="p-3 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl text-gray-600 transition" title="Ganti Captcha">
                            🔄
                        </button>
                    </div>
                    <input type="text" id="captcha-input" required placeholder="Masukkan kode di atas" 
                        class="w-full mt-2 px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>

                <!-- Tombol Login -->
                <button type="submit" class="w-full py-3.5 bg-[#ffc107] hover:bg-[#e0ac06] text-black font-extrabold rounded-xl shadow-lg shadow-[#ffc107]/20 transition mt-2">
                    Login
                </button>
            </form>

            <!-- Tautan Tambahan -->
            <div class="text-center mt-6 space-y-2">
                <div>
                    <a href="{{ route('password.request') }}" class="text-xs text-gray-500 hover:text-gray-800 transition">Lupa Password?</a>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Belum punya akun magang? <a href="{{ route('register') }}" class="text-amber-600 font-bold hover:underline">Daftar di sini</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Script JavaScript -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }

        let generatedCaptchaText = '';

        function generateCaptcha() {
            const canvas = document.getElementById('captcha-canvas');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = "#f3f4f6";
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Garis coretan samar
            for (let i = 0; i < 10; i++) {
                ctx.strokeStyle = `rgba(100, 100, 100, ${Math.random() * 0.25 + 0.1})`;
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
                ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
                ctx.stroke();
            }

            const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
            let captcha = "";
            for (let i = 0; i < 5; i++) {
                captcha += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            generatedCaptchaText = captcha;

            ctx.font = "bold 24px monospace";
            for (let i = 0; i < captcha.length; i++) {
                ctx.save();
                const x = 22 + (i * 28);
                const y = 30 + (Math.random() * 8 - 4);
                ctx.translate(x, y);
                ctx.rotate((Math.random() - 0.5) * 0.4);

                // Warna teks dibuat agak samar transparan
                ctx.fillStyle = `rgba(60, 60, 60, ${Math.random() * 0.2 + 0.5})`;
                ctx.fillText(captcha[i], 0, 0);
                ctx.restore();
            }
        }

        window.onload = function() {
            generateCaptcha();
        };

        document.querySelector('form').addEventListener('submit', function(e) {
            const userInput = document.getElementById('captcha-input').value.trim();
            if (userInput.toUpperCase() !== generatedCaptchaText) {
                e.preventDefault();
                alert("Kode validasi captcha salah! Silakan coba lagi.");
                generateCaptcha();
                document.getElementById('captcha-input').value = '';
            }
        });
    </script>
</body>
</html>