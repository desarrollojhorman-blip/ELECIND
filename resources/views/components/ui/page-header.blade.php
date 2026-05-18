@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->class('mb-4') }}>
    {{-- Fila 1: título (+ botones clásicos a la derecha para páginas de índice) --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            @if ($title)
                <h2 class="text-xl font-semibold text-slate-900">{{ $title }}</h2>
            @endif
            @if ($subtitle)
                <p class="text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>

    {{-- Fila 2: dos grupos de botones (editar / ver / crear) --}}
    @if (isset($actionsLeft) || isset($actionsRight))
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
            <div class="flex flex-wrap items-center gap-2">
                {{ $actionsLeft ?? '' }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{ $actionsRight ?? '' }}
            </div>
        </div>
    @endif
</div>
