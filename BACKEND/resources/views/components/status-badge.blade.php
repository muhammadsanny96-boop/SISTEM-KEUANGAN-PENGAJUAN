@props(['status'])

@php
    $statusClasses = match($status) {
        'Menunggu' => 'bg-slate-100 text-slate-700 border-slate-300 ring-slate-400/20',
        'Diproses' => 'bg-amber-50 text-amber-800 border-amber-300 ring-amber-400/20',
        'Disetujui' => 'bg-emerald-50 text-emerald-800 border-emerald-300 ring-emerald-400/20',
        'Ditolak' => 'bg-rose-50 text-rose-800 border-rose-300 ring-rose-400/20',
        'Selesai' => 'bg-blue-50 text-blue-800 border-blue-300 ring-blue-400/20',
        default => 'bg-slate-100 text-slate-700 border-slate-300 ring-slate-400/20',
    };

    $dotClasses = match($status) {
        'Menunggu' => 'bg-slate-500',
        'Diproses' => 'bg-amber-500 animate-pulse',
        'Disetujui' => 'bg-emerald-500',
        'Ditolak' => 'bg-rose-500',
        'Selesai' => 'bg-blue-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border ring-1 {$statusClasses}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $dotClasses }}"></span>
    {{ $status }}
</span>
