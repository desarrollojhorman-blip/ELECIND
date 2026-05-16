@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'icon' => null,
    'as' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-md font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:pointer-events-none';

    $sizes = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-2.5 py-1.5 text-sm',
        'md' => 'px-3 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-base',
    ];

    $variants = [
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800',
        'secondary' => 'bg-dark text-white hover:bg-dark-hover',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700',
        'info' => 'bg-blue-600 text-white hover:bg-blue-700',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600',
        'neutral' => 'bg-slate-600 text-white hover:bg-slate-700',
        'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100',
        'outline' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
        'link' => 'text-primary-600 hover:text-primary-700 hover:underline px-0 py-0',
    ];

    $classes = trim($base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']));
@endphp

@if ($as === 'a')
    <a {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-dynamic-component :component="$icon" class="size-4" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-dynamic-component :component="$icon" class="size-4" />
        @endif
        {{ $slot }}
    </button>
@endif
