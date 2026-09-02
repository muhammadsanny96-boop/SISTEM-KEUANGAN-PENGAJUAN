@props(['priority'])

@php
    $priorityClasses = match($priority) {
        'Mendesak' => 'bg-red-50 text-red-700 border-red-300 ring-red-400/20 font-bold',
        'Tinggi' => 'bg-orange-50 text-orange-700 border-orange-300 ring-orange-400/20 font-semibold',
        'Sedang' => 'bg-blue-50 text-blue-700 border-blue-300 ring-blue-400/20',
        'Rendah' => 'bg-slate-50 text-slate-600 border-slate-200 ring-slate-400/10',
        default => 'bg-slate-50 text-slate-600 border-slate-200 ring-slate-400/10',
    };

    $icon = match($priority) {
        'Mendesak' => '🔥',
        'Tinggi' => '⚡',
        'Sedang' => '🔹',
        'Rendah' => '▫️',
        default => '▫️',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs border ring-1 {$priorityClasses}"]) }}>
    <span>{{ $icon }}</span>
    <span>{{ $priority }}</span>
</span>
