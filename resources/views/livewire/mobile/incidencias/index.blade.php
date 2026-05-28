<div>
    {{-- Buscador --}}
    <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-3 py-2">
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="buscar"
                placeholder="Título, tipo, prioridad, estado…"
                class="w-full rounded-lg border-slate-300 py-2 pl-9 pr-9 text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-primary-500"
            />
            @if ($buscar !== '')
                <button type="button" wire:click="limpiarBuscar"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-slate-400 hover:text-slate-600">
                    <x-heroicon-m-x-mark class="size-4" />
                </button>
            @endif
        </div>
    </div>

    {{-- Filtros pills --}}
    <div class="sticky top-[52px] z-10 border-b border-slate-200 bg-white px-3 py-2">
        <div class="flex gap-1.5 overflow-x-auto">
            @php
                $filtros = [
                    'todas'      => 'Todas',
                    'pendiente'  => 'Pendientes',
                    'en_proceso' => 'En proceso',
                    'resuelta'   => 'Resueltas',
                    'cerrada'    => 'Cerradas',
                ];
            @endphp

            @foreach ($filtros as $valor => $label)
                <button type="button"
                        wire:click="setFiltro('{{ $valor }}')"
                        @class([
                            'shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                            'bg-primary-700 text-white' => $filtroEstado === $valor,
                            'bg-slate-100 text-slate-700 hover:bg-slate-200' => $filtroEstado !== $valor,
                        ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="px-4 py-3">

        @forelse ($this->incidencias as $inc)
            @php
                $prioridadClases = match($inc->prioridad->tono()) {
                    'danger'  => 'bg-red-100 text-red-800',
                    'warning' => 'bg-amber-100 text-amber-800',
                    'info'    => 'bg-blue-100 text-blue-800',
                    default   => 'bg-slate-100 text-slate-700',
                };
                $estadoClases = match($inc->estado->tono()) {
                    'success' => 'bg-emerald-100 text-emerald-800',
                    'warning' => 'bg-amber-100 text-amber-800',
                    'info'    => 'bg-blue-100 text-blue-800',
                    default   => 'bg-slate-100 text-slate-600',
                };
                $borderColor = match($inc->prioridad->value) {
                    'urgente' => 'border-l-red-500',
                    'alta'    => 'border-l-amber-500',
                    'media'   => 'border-l-blue-400',
                    default   => 'border-l-slate-300',
                };
            @endphp

            <div wire:key="inc-{{ $inc->id }}"
                 class="mb-2 rounded-lg border border-slate-200 border-l-4 {{ $borderColor }} bg-white p-3 shadow-sm">

                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900 leading-snug">{{ $inc->titulo }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $inc->tipo->etiqueta() }}</p>
                        @if ($inc->descripcion)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-400">{{ $inc->descripcion }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 space-y-1 text-right">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $prioridadClases }}">
                            {{ $inc->prioridad->etiqueta() }}
                        </span>
                        <br>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $estadoClases }}">
                            {{ $inc->estado->etiqueta() }}
                        </span>
                        <p class="mt-1 text-xs text-slate-400">{{ $inc->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>

                @if ($inc->resolucion && ! $inc->estado->esActiva())
                    <div class="mt-2 rounded-md bg-emerald-50 px-2.5 py-1.5 text-xs text-emerald-800">
                        <span class="font-medium">Resolución:</span> {{ $inc->resolucion }}
                    </div>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="mb-3 inline-flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-heroicon-o-exclamation-circle class="size-7" />
                </div>
                <p class="text-sm text-slate-600">No hay incidencias en este filtro.</p>
            </div>
        @endforelse

        <div class="mt-3">
            {{ $this->incidencias->links() }}
        </div>
    </div>

    {{-- Botón flotante nueva incidencia --}}
    @can('incidencias.crear')
        <div class="fixed bottom-6 right-4">
            <a href="{{ route('mobile.incidencias.nueva') }}"
               wire:navigate
               class="flex size-14 items-center justify-center rounded-full bg-primary-700 text-white shadow-lg hover:bg-primary-800 active:scale-95 active:transition-transform">
                <x-heroicon-m-plus class="size-6" />
            </a>
        </div>
    @endcan

    {{-- Loading --}}
    <div wire:loading.flex class="fixed inset-0 z-50 items-center justify-center bg-white/70">
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
            <svg class="size-4 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-slate-600">Cargando…</span>
        </div>
    </div>
</div>
