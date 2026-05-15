@props([
    'icon' => null,
    'variant' => 'ghost',
    'tooltip' => null,
    'type' => 'button',
    'size' => 'md',
    'as' => 'button',
])

@php
    $sizes = [
        'sm' => 'size-7 [&_svg]:size-3.5',
        'md' => 'size-8 [&_svg]:size-4',
        'lg' => 'size-9 [&_svg]:size-5',
    ];

    $variants = [
        'ghost' => 'text-slate-500 hover:bg-slate-100 hover:text-slate-900',
        'primary' => 'text-primary-600 hover:bg-primary-50',
        'danger' => 'text-red-600 hover:bg-red-50',
        'success' => 'text-emerald-600 hover:bg-emerald-50',
        'info' => 'text-blue-600 hover:bg-blue-50',
        'warning' => 'text-amber-600 hover:bg-amber-50',
        'soft-danger' => 'bg-red-50 text-red-700 hover:bg-red-100',
        'soft-primary' => 'bg-primary-50 text-primary-700 hover:bg-primary-100',
        'soft-info' => 'bg-blue-50 text-blue-700 hover:bg-blue-100',
        'soft-success' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
    ];

    $classes = trim('inline-flex items-center justify-center rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-primary-500 disabled:opacity-50 disabled:pointer-events-none '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['ghost']));
@endphp

@if ($as === 'a')
    <a @if ($tooltip) title="{{ $tooltip }}" aria-label="{{ $tooltip }}" @endif
       {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-dynamic-component :component="$icon" />
        @else
            {{ $slot }}
        @endif
    </a>
@else
    <button type="{{ $type }}"
            @if ($tooltip) title="{{ $tooltip }}" aria-label="{{ $tooltip }}" @endif
            {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-dynamic-component :component="$icon" />
        @else
            {{ $slot }}
        @endif
    </button>
@endif
