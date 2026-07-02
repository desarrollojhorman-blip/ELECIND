<div>
    {{-- Buscador + filtros pills --}}
    <div class="sticky top-0 z-10 space-y-2 border-b border-slate-200 bg-white px-3 py-2">
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input type="text"
                   wire:model.live.debounce.400ms="buscar"
                   placeholder="Buscar por nº, cliente o proyecto…"
                   class="w-full rounded-md border border-slate-300 py-1.5 pl-8 pr-8 text-sm focus:border-primary-500 focus:ring-primary-500" />
            @if ($buscar !== '')
                <button type="button" wire:click="limpiarBuscar"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <x-heroicon-o-x-mark class="size-4" />
                </button>
            @endif
        </div>

        <select wire:model.live="filtroEstado"
                class="w-full rounded-md border border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="todos">Todos</option>
            <option value="parte">Partes</option>
            <option value="albaran">Albaranes</option>
            <option value="borrador">Borradores</option>
        </select>
    </div>

    {{-- Listado --}}
    <div class="px-4 py-3">
        @forelse ($this->items as $item)
            @php
                $esNavegable = ! is_null($item['url']);
                $tag = $esNavegable ? 'a' : 'div';
            @endphp

            <{{ $tag }}
               @if ($esNavegable) href="{{ $item['url'] }}" @endif
               wire:key="item-{{ $item['tipo'] }}-{{ $item['numero'] }}"
               @class([
                   'mb-2 block rounded-lg border bg-white p-3 shadow-sm transition-colors',
                   'border-slate-200 hover:border-primary-300 hover:bg-primary-50/30 active:scale-[0.99] active:transition-transform' => $esNavegable,
                   'border-dashed border-slate-300' => ! $esNavegable,
               ])>
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <p class="truncate font-mono text-sm font-semibold text-slate-900">
                                {{ $item['numero'] }}
                            </p>
                        </div>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ $item['cliente'] ?? '—' }}
                            @if ($item['proyecto'])
                                · {{ $item['proyecto'] }}
                            @endif
                        </p>
                        @if (! empty($item['origen']))
                            <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $item['origen'] }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 text-right">
                        <x-ui.badge :tone="$item['estadoTone']" dot>{{ $item['estadoLabel'] }}</x-ui.badge>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ \Illuminate\Support\Carbon::parse($item['fecha'])->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </{{ $tag }}>
        @empty
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="mb-3 inline-flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-heroicon-o-folder-open class="size-7" />
                </div>
                <p class="text-sm text-slate-600">No hay nada en este filtro.</p>
                <a href="{{ route('mobile.albaranes.nuevo') }}"
                   class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    <x-heroicon-m-plus class="size-4" />
                    Crear nuevo parte
                </a>
            </div>
        @endforelse

        <div class="mt-3">
            {{ $this->items->links() }}
        </div>
    </div>
</div>
