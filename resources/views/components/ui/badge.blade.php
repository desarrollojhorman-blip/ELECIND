@props([
    'tone' => 'neutral',
    'dot' => false,
])

@php
    $tones = [
        'neutral' => 'bg-slate-100 text-slate-700',
        'success' => 'bg-emerald-100 text-emerald-700',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-700',
        'info' => 'bg-blue-100 text-blue-700',
        'primary' => 'bg-primary-100 text-primary-700',
        'pending' => 'bg-yellow-200 text-yellow-900',
    ];

    $dotColors = [
        'neutral' => 'bg-slate-400',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-blue-500',
        'primary' => 'bg-primary-500',
        'pending' => 'bg-yellow-500',
    ];

    $classes = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium '.($tones[$tone] ?? $tones['neutral']);
@endphp

<span {{ $attributes->class($classes) }}>
    @if ($dot)
        <span class="inline-block size-1.5 rounded-full {{ $dotColors[$tone] ?? $dotColors['neutral'] }}"></span>
    @endif
    {{ $slot }}
</span>
