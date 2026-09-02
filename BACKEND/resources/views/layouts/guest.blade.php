<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistem Pengajuan Barang') }} - PT Jamkrida Kalsel</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-jamkrida.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex flex-col justify-center items-center p-4 sm:p-6 font-sans text-slate-800 antialiased">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80 p-8">
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
        </div>

        {{ $slot }}
    </div>
</body>
</html>
