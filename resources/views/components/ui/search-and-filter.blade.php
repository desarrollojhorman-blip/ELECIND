@props([
    'searchModel' => 'buscar',
    'placeholder' => 'Buscar…',
    'filtrosAplicados' => 0,
    'panelToggle' => null,
    'panelOpen' => false,
    'resetKey' => 0,
    'clearAllAction' => null,
    'clearSearchAction' => null,
    'hasContentToClear' => false,
])

<div {{ $attributes->class('rounded-md border border-slate-200 bg-white shadow-sm') }}>
    <div class="flex flex-wrap items-center gap-2 px-3 py-2.5">
        {{-- Acciones izquierdas (Nuevo, Acciones▾...) --}}
        @isset ($leftActions)
            <div class="flex shrink-0 items-center gap-1.5">
                {{ $leftActions }}
            </div>
        @endisset

        {{-- Buscador con icono lupa a la izquierda y X a la derecha cuando hay texto --}}
        <div class="relative flex-1 min-w-[220px]">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input type="text"
                   wire:key="search-{{ $resetKey }}"
                   wire:model.live.debounce.300ms="{{ $searchModel }}"
                   placeholder="{{ $placeholder }}"
                   class="w-full rounded-md border-slate-300 pl-9 pr-9 py-2 text-sm shadow-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-primary-500">
            <button type="button"
                    x-show="$wire.{{ $searchModel }} !== '' && $wire.{{ $searchModel }} !== null"
                    x-cloak
                    @if ($clearSearchAction)
                        wire:click="{{ $clearSearchAction }}"
                    @else
                        x-on:click="$wire.set('{{ $searchModel }}', '')"
                    @endif
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                    title="Limpiar búsqueda">
                <x-heroicon-m-x-mark class="size-4" />
            </button>
        </div>

        {{-- Botón Limpiar todo (a la izquierda de Filtros, visible si hay algo aplicado) --}}
        @if ($clearAllAction && $hasContentToClear)
            <button type="button"
                    wire:click="{{ $clearAllAction }}"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:border-red-300 hover:bg-red-50 hover:text-red-700"
                    title="Borrar la búsqueda y todos los filtros aplicados">
                <x-heroicon-o-x-circle class="size-4" />
                <span>Limpiar todo</span>
            </button>
        @endif

        {{-- Botón Filtros --}}
        @if ($panelToggle)
            <button type="button"
                    wire:click="{{ $panelToggle }}"
                    @class([
                        'inline-flex shrink-0 items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium transition-colors',
                        'border-primary-200 bg-primary-50 text-primary-700 hover:bg-primary-100' => $filtrosAplicados > 0 || $panelOpen,
                        'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' => $filtrosAplicados === 0 && ! $panelOpen,
                    ])>
                <x-heroicon-o-funnel class="size-4" />
                <span>Filtros</span>
                @if ($filtrosAplicados > 0)
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-primary-600 px-1.5 py-0.5 text-xs font-semibold text-white">
                        {{ $filtrosAplicados }}
                    </span>
                @endif
                <x-heroicon-o-chevron-down @class(['size-4 transition-transform', 'rotate-180' => $panelOpen]) />
            </button>
        @endif

        @isset ($extra)
            {{ $extra }}
        @endisset
    </div>

    @if ($panelOpen)
        <div class="border-t border-slate-200 bg-slate-50 px-3 py-3">
            {{ $slot }}
        </div>
    @endif

    @isset ($chips)
        <div class="border-t border-slate-200 px-3 py-2">
            {{ $chips }}
        </div>
    @endisset
</div>
