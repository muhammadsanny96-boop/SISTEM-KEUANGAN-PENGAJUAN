<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Sistem Pengajuan Barang PT Jamkrida Kalsel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-jamkrida.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex flex-col justify-center items-center p-4 sm:p-6 font-sans text-slate-800 antialiased">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80 p-8">
        
        {{-- Header Logo & Title --}}
        <div class="text-center mb-6">
            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 flex items-center justify-center">
                <img src="{{ asset('images/logo-jamkrida.png') }}" alt="Logo PT Jamkrida Kalsel" class="max-w-full max-h-full object-contain">
            </div>
            <div class="text-xs font-bold text-blue-600 tracking-wider uppercase">
                PT JAMKRIDA KALSEL
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
                Sistem Pengajuan Barang
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Silakan masuk ke akun Anda
            </p>
        </div>

        {{-- Status Alert --}}
        @if (session('status'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        {{-- Form Login --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Alamat Email
                </label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       required 
                       autofocus 
                       placeholder="nama@jamkridakalsel.co.id"
                       class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm text-slate-900 placeholder-slate-400 py-2.5 px-3.5 transition duration-150">
                @error('email') 
                    <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> 
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="text-xs font-semibold text-slate-700">
                        Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            Lupa password?
                        </a>
                    @endif
                </div>
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       placeholder="••••••••"
                       class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm text-slate-900 placeholder-slate-400 py-2.5 px-3.5 transition duration-150">
                @error('password') 
                    <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> 
                @enderror
            </div>

            <div class="flex items-center">
                <input id="remember_me" 
                       type="checkbox" 
                       name="remember"
                       class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                <label for="remember_me" class="ml-2 text-xs sm:text-sm text-slate-600 cursor-pointer select-none">
                    Ingat saya
                </label>
            </div>

            <div>
                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 ease-in-out cursor-pointer flex justify-center items-center">
                    Masuk
                </button>
            </div>
        </form>

        {{-- Footer Register Link --}}
        <div class="text-center text-xs sm:text-sm text-slate-500 mt-6 pt-5 border-t border-slate-100">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline ml-1">
                Daftar Akun Baru
            </a>
        </div>
    </div>
</body>
</html>
