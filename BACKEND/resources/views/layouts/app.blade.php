<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistem Pengajuan Barang') - PT Jamkrida Kalsel</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-jamkrida.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

<div class="flex min-h-screen">

    {{-- Backdrop for Mobile Sidebar --}}
    <div x-show="sidebarOpen" 
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        
        {{-- Sidebar Brand --}}
        <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-800/80 bg-slate-950/40">
            <div class="w-9 h-9 rounded-lg bg-white p-1 flex items-center justify-center shrink-0 shadow-sm">
                <img src="{{ asset('images/logo-jamkrida.png') }}" alt="Logo Jamkrida" class="max-w-full max-h-full object-contain">
            </div>
            <div class="min-w-0 flex-1">
                <div class="font-bold text-sm text-white tracking-wide truncate">
                    JAMKRIDA KALSEL
                </div>
                <div class="text-[10.5px] text-slate-400 truncate">
                    Sistem Pengajuan Barang
                </div>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Nav Links --}}
        <nav class="flex-1 px-3.5 py-4 space-y-1.5 overflow-y-auto">
            @if(auth()->user()?->isAdmin())
                <div class="px-3 pb-1.5 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Menu Admin
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Admin</span>
                </a>

                <a href="{{ route('admin.submissions.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.submissions.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="flex-1">Daftar Pengajuan</span>
                    @php $pendingCount = \App\Models\Submission::where('status', 'Menunggu')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="px-2 py-0.5 text-[11px] font-bold bg-amber-500 text-slate-950 rounded-full">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.expenses.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.expenses.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Rekap Pengeluaran & Log</span>
                </a>

                <div class="px-3 pb-1.5 pt-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Master Data
                </div>

                <a href="{{ route('admin.divisions.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.divisions.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Data Divisi</span>
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.categories.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Data Kategori</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Data Karyawan</span>
                </a>
            @else
                <div class="px-3 pb-1.5 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Menu Pegawai
                </div>

                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Pegawai</span>
                </a>

                <a href="{{ route('user.submissions.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('user.submissions.index') || request()->routeIs('user.submissions.show') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Pengajuan Saya</span>
                </a>

                <a href="{{ route('user.submissions.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('user.submissions.create') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Buat Pengajuan Baru</span>
                </a>
            @endif

            <div class="px-3 pb-1.5 pt-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Pemberitahuan
            </div>

            <a href="{{ route('notifications.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-150 {{ request()->routeIs('notifications.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="flex-1">Notifikasi</span>
                @php $unreadCount = auth()->user()?->unreadNotifications->count() ?? 0; @endphp
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 text-[11px] font-bold bg-blue-500 text-white rounded-full">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
        </nav>

        {{-- Sidebar User Box --}}
        <div class="p-3.5 border-t border-slate-800/80 bg-slate-950/40">
            <div class="flex items-center gap-3 px-2 py-1.5 rounded-lg">
                <div class="w-8 h-8 rounded-md bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-white truncate">{{ auth()->user()?->name }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ auth()->user()?->division->nama_divisi ?? 'Umum' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-md transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content Container --}}
    <div class="flex-1 lg:ml-64 flex flex-col min-w-0 min-h-screen bg-slate-50">

        {{-- Topbar Header --}}
        <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
            <div class="flex items-center gap-3 min-w-0">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-base font-extrabold text-slate-900 truncate">
                        @yield('page_title', 'Sistem Pengajuan Barang')
                    </h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 truncate hidden sm:block">
                        @yield('page_subtitle', 'PT Jamkrida Kalsel')
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-1.5 px-3 py-1 bg-slate-50 border border-slate-200/80 rounded-lg text-xs font-medium text-slate-600">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>

                {{-- Notification Dropdown Bell --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="relative p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if($unreadCount > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                        @endif
                    </button>

                    <div x-show="open" 
                         x-cloak
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-200/80 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">Pemberitahuan</span>
                            <a href="{{ route('notifications.index') }}" class="text-[11px] font-semibold text-blue-600 hover:underline">Lihat Semua</a>
                        </div>
                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                            @forelse(auth()->user()?->unreadNotifications->take(4) ?? [] as $notification)
                                <div class="p-3 bg-blue-50/50 hover:bg-blue-50 transition-colors">
                                    <div class="text-xs font-bold text-slate-800">{{ $notification->data['title'] ?? 'Pemberitahuan' }}</div>
                                    <div class="text-[11px] text-slate-600 mt-0.5 line-clamp-2">{{ $notification->data['message'] ?? '' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-slate-400">Tidak ada notifikasi baru</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- User Badge --}}
                <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()?->name }}</div>
                        <div class="text-[10.5px] text-slate-500">{{ auth()->user()?->isAdmin() ? 'Administrator' : (auth()->user()?->division->nama_divisi ?? 'Pegawai') }}</div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success') || session('error') || session('status'))
            <div class="px-4 sm:px-6 pt-4">
                @if(session('success'))
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm rounded-xl flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm rounded-xl flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Main Page Content --}}
        <main class="flex-1 p-4 sm:p-6 w-full max-w-7xl mx-auto">
            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>

        {{-- Footer --}}
        <footer class="bg-white border-t border-slate-200/80 px-4 sm:px-6 py-4 text-xs text-slate-500 flex flex-wrap justify-between items-center gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>PT JAMKRIDA KALSEL</strong> &bull; PT Penjaminan Kredit Daerah Kalimantan Selatan
            </div>
            <div class="text-[11px] text-slate-400">
                Sistem Informasi Pengajuan Pengadaan Barang
            </div>
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>
