@props([
    'label' => 'Acciones',
    'icon' => 'heroicon-o-ellipsis-horizontal',
    'align' => 'left',
])

@php
    $alignClass = match ($align) {
        'right' => 'right-0',
        default => 'left-0',
    };
@endphp

<div x-data="{ open: false }"
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     class="relative inline-block">
    <button type="button"
            @click="open = ! open"
            :class="open ? 'border-primary-200 bg-primary-50 text-primary-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-md border px-3 py-2 text-sm font-medium transition-colors">
        <x-dynamic-component :component="$icon" class="size-4" />
        <span>{{ $label }}</span>
        <x-heroicon-m-chevron-down x-bind:class="open ? 'rotate-180' : ''" class="size-4 transition-transform" />
    </button>

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="{{ $alignClass }} absolute top-full z-50 mt-1 w-60 overflow-hidden rounded-md border border-slate-200 bg-white py-1 shadow-lg ring-1 ring-slate-900/5">
        {{ $slot }}
    </div>
</div>
