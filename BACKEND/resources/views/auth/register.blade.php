<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi Akun Pegawai - PT Jamkrida Kalsel</title>
    
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
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80 p-8">
        
        {{-- Header Logo & Title --}}
        <div class="text-center mb-6">
            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 flex items-center justify-center">
                <img src="{{ asset('images/logo-jamkrida.png') }}" alt="Logo PT Jamkrida Kalsel" class="max-w-full max-h-full object-contain">
            </div>
            <div class="text-xs font-bold text-blue-600 tracking-wider uppercase">
                PT JAMKRIDA KALSEL
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
                Registrasi Kepala Divisi
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Pendaftaran akun resmi penanggung jawab divisi (1 akun per divisi)
            </p>
        </div>

        @php
            $divisionsList = $allDivisions ?? \App\Models\Division::with('headUser')->orderBy('nama_divisi')->get();
            $hasAvailableDivisions = $divisionsList->contains(fn($d) => $d->headUser === null);
        @endphp

        @if(! $hasAvailableDivisions)
            <div class="p-4 mb-6 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm leading-relaxed">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-amber-900">
                    <span>⚠️</span>
                    <span>Seluruh Divisi Telah Terisi</span>
                </div>
                Semua divisi telah memiliki akun Kepala Divisi terdaftar. Jika Anda membutuhkan akun atau pergantian penanggung jawab divisi, silakan hubungi Administrator.
            </div>
        @endif

        {{-- Form Register --}}
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Lengkap Kepala Divisi <span class="text-red-500">*</span>
                </label>
                <input id="name" 
                       type="text" 
                       name="name" 
                       value="{{ old('name') }}"
                       required 
                       autofocus 
                       placeholder="Nama lengkap penanggung jawab divisi"
                       class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm text-slate-900 placeholder-slate-400 py-2.5 px-3.5 transition duration-150">
                @error('name') 
                    <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> 
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Alamat Email Perusahaan <span class="text-red-500">*</span>
                </label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       required 
                       placeholder="email@jamkridakalsel.co.id"
                       class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm text-slate-900 placeholder-slate-400 py-2.5 px-3.5 transition duration-150">
                @error('email') 
                    <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> 
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="division_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Divisi yang Dipimpin <span class="text-red-500">*</span>
                    </label>
                    <select id="division_id" 
                            name="division_id" 
                            required
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm text-slate-900 py-2.5 px-3.5 transition duration-150">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisionsList as $div)
                            @php
                                $isTaken = $div->headUser !== null;
                            @endphp
                            <option value="{{ $div->id }}" 
                                    {{ old('division_id') == $div->id ? 'selected' : '' }}
                                    {{ $isTaken ? 'disabled' : '' }}
                                    class="{{ $isTaken ? 'text-slate-400 bg-slate-100' : 'text-slate-900 font-medium' }}">
                                {{ $div->nama_divisi }} {{ $isTaken ? ' (Sudah Terisi)' : ' (Tersedia)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('division_id') 
                        <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> 
                    @enderror
                    <p class="text-[11px] text-slate-400 mt-1">1 divisi hanya dapat memiliki 1 akun Kepala Divisi.</p>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        No. WhatsApp / HP
                    </label>
                    <input id="phone" 
                           type="text" 
                           name="phone" 
                           value="{{ old('phone') }}"
                           placeholder="08xxxxxxxxxx"
                           class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm text-slate-900 placeholder-slate-400 py-2.5 px-3.5 transition duration-150">
                    @error('phone') 
                        <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="new-password" 
                           placeholder="Min. 8 karakter"
                           class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm text-slate-900 placeholder-slate-400 py-2.5 px-3.5 transition duration-150">
                    @error('password') 
                        <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input id="password_confirmation" 
                           type="password" 
                           name="password_confirmation" 
                           required 
                           placeholder="Ulangi password"
                           class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm text-slate-900 placeholder-slate-400 py-2.5 px-3.5 transition duration-150">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 ease-in-out cursor-pointer flex justify-center items-center">
                    Daftar Sebagai Kepala Divisi
                </button>
            </div>
        </form>

        {{-- Footer Login Link --}}
        <div class="text-center text-xs sm:text-sm text-slate-500 mt-6 pt-5 border-t border-slate-100">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline ml-1">
                Masuk
            </a>
        </div>
    </div>
</body>
</html>
