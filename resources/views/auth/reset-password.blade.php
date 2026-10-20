<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - DARIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#191712] text-white flex items-center justify-center min-h-screen p-4 overflow-y-auto">

    <div class="w-full max-w-md bg-white text-gray-800 rounded-3xl shadow-2xl overflow-hidden relative border border-white/5 my-8">

        <div class="h-3 w-full" style="background: repeating-linear-gradient(45deg, #ffc107, #ffc107 15px, #191712 15px, #191712 30px);"></div>

        <div class="p-8">
            <div class="flex flex-col items-center mb-6">
                <div class="bg-[#191712] text-[#ffc107] font-bold p-3 rounded-2xl flex items-center justify-center text-xl mb-3 shadow-lg">⚡</div>
                <h1 class="text-3xl font-extrabold tracking-wider text-black">DARIN</h1>
                <p class="text-xs text-gray-500 mt-1">Buat password baru untuk {{ $email }}</p>
            </div>

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-600 text-xs rounded-xl text-center font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Password Baru</label>
                    <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password baru"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#ffc107] hover:bg-[#e0ac06] text-black font-extrabold rounded-xl shadow-lg shadow-[#ffc107]/20 transition mt-2">
                    Simpan Password Baru
                </button>
            </form>
        </div>
    </div>

</body>
</html>