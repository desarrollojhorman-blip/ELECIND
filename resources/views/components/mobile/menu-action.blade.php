@props([
    'href' => '#',
    'icon' => null,
    'variant' => 'primary',
])

@php
    $base = 'flex w-full items-center gap-3 rounded-lg px-4 py-4 text-base font-medium transition-colors active:scale-[0.98] active:transition-transform';

    $variants = [
        'primary' => 'bg-primary-700 text-white hover:bg-primary-800',
        'dark' => 'bg-slate-900 text-white hover:bg-slate-800',
        'outline' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
    ];

    $classes = trim($base.' '.($variants[$variant] ?? $variants['primary']));
@endphp

<a href="{{ $href }}" {{ $attributes->class($classes) }}>
    @if ($icon)
        <x-dynamic-component :component="$icon"
                             class="size-5 shrink-0 {{ $variant === 'outline' ? 'text-slate-500' : '' }}" />
    @endif
    <span class="flex-1 text-left">{{ $slot }}</span>
    <x-heroicon-m-chevron-right class="size-4 shrink-0 opacity-60" />
</a>
