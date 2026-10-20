<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - DARIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#191712] text-white flex items-center justify-center min-h-screen p-4 overflow-y-auto">

    <div class="w-full max-w-md bg-white text-gray-800 rounded-3xl shadow-2xl overflow-hidden relative border border-white/5 my-8">

        <div class="h-3 w-full" style="background: repeating-linear-gradient(45deg, #ffc107, #ffc107 15px, #191712 15px, #191712 30px);"></div>

        <div class="p-8">
            <div class="flex flex-col items-center mb-6">
                <div class="bg-[#191712] text-[#ffc107] font-bold p-3 rounded-2xl flex items-center justify-center text-xl mb-3 shadow-lg">⚡</div>
                <h1 class="text-3xl font-extrabold tracking-wider text-black">DARIN</h1>
                <p class="text-xs text-gray-500 mt-1">Masukkan email akun Anda untuk mengatur ulang password</p>
            </div>

            @if(session('status'))
                <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl text-center font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-600 text-xs rounded-xl text-center font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="Masukkan email akun Anda"
                        class="w-full px-4 py-3 bg-[#fdfdfd] border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:border-[#ffc107] focus:ring-1 focus:ring-[#ffc107] transition">
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#ffc107] hover:bg-[#e0ac06] text-black font-extrabold rounded-xl shadow-lg shadow-[#ffc107]/20 transition mt-2">
                    Kirim Tautan Reset
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-xs text-gray-500 hover:text-gray-800 transition">← Kembali ke halaman login</a>
            </div>
        </div>
    </div>

</body>
</html>