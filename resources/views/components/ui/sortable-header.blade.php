@props([
    'column' => null,
    'currentColumn' => null,
    'currentDirection' => 'asc',
    'action' => 'ordenarPor',
    'align' => 'left',
])

@php
    $active = $column === $currentColumn;
    $alignClass = match ($align) {
        'right' => 'justify-end text-right',
        'center' => 'justify-center text-center',
        default => 'justify-start text-left',
    };
@endphp

<th {{ $attributes->class('px-4 py-3') }}>
    @if ($column)
        <button type="button"
                wire:click="{{ $action }}('{{ $column }}')"
                class="inline-flex w-full items-center gap-1 text-white/90 transition-colors hover:text-white {{ $alignClass }}">
            <span>{{ $slot }}</span>
            @if ($active)
                @if ($currentDirection === 'asc')
                    <x-heroicon-m-chevron-up class="size-3.5 shrink-0" />
                @else
                    <x-heroicon-m-chevron-down class="size-3.5 shrink-0" />
                @endif
            @else
                <x-heroicon-m-chevron-up-down class="size-3.5 shrink-0 opacity-50" />
            @endif
        </button>
    @else
        <span class="block {{ $alignClass }}">{{ $slot }}</span>
    @endif
</th>
