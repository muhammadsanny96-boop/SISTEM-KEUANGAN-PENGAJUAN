@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page_title', 'Notifikasi')
@section('page_subtitle', 'Pemberitahuan pembaruan status pengajuan barang')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Pemberitahuan</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Terdapat <strong class="text-slate-800">{{ auth()->user()->unreadNotifications->count() }}</strong> notifikasi belum dibaca
            </p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="card overflow-hidden divide-y divide-slate-100">
        @forelse($notifications as $notification)
            <div class="p-4 sm:p-5 flex items-start gap-3.5 transition-colors {{ $notification->read_at ? 'bg-white hover:bg-slate-50/60' : 'bg-blue-50/50 hover:bg-blue-50/80' }}">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs {{ $notification->read_at ? 'bg-slate-400' : 'bg-blue-600' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                            {{ $notification->data['title'] ?? 'Notifikasi' }}
                        </h4>
                        <span class="text-[11px] text-slate-400 shrink-0">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        {{ $notification->data['message'] ?? '-' }}
                    </p>

                    @if(!empty($notification->data['url']))
                        <div class="mt-2.5 flex items-center gap-2">
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm text-[11px] py-1 px-3">
                                    Lihat Pengajuan &rarr;
                                </button>
                            </form>
                            @if(!$notification->read_at)
                                <span class="w-2 h-2 rounded-full bg-blue-600 inline-block"></span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-12 text-center text-slate-400 text-xs sm:text-sm">
                Tidak ada notifikasi saat ini.
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
