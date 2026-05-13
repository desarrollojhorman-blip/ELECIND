@props([
    'icon' => null,
    'disabled' => false,
    'badge' => null,
    'tone' => 'default',
])

@php
    $base = 'flex w-full items-center gap-2.5 px-3 py-2 text-sm transition-colors';

    $tones = [
        'default' => 'text-slate-700 hover:bg-slate-50 hover:text-slate-900',
        'danger' => 'text-red-700 hover:bg-red-50',
        'primary' => 'text-primary-700 hover:bg-primary-50',
    ];

    $classes = $disabled
        ? "{$base} cursor-not-allowed text-slate-400"
        : "{$base} ".($tones[$tone] ?? $tones['default']);
@endphp

<button type="button"
        @if ($disabled) disabled @endif
        {{ $attributes->class($classes) }}>
    @if ($icon)
        <x-dynamic-component :component="$icon" class="size-4 shrink-0" />
    @endif
    <span class="flex-1 text-left">{{ $slot }}</span>
    @if ($badge)
        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $badge }}</span>
    @endif
</button>
