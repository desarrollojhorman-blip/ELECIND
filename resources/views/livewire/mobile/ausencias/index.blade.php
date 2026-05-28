<div>
    {{-- Buscador --}}
    <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-3 py-2">
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="buscar"
                placeholder="Tipo, motivo o fecha…"
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
                    'todas'     => 'Todas',
                    'pendiente' => 'Pendientes',
                    'aprobada'  => 'Aprobadas',
                    'rechazada' => 'Rechazadas',
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

        @forelse ($this->ausencias as $ausencia)
            @php
                $estadoTono = $ausencia->estado->tono();
                $estadoClases = match($estadoTono) {
                    'success' => 'bg-emerald-100 text-emerald-800',
                    'warning' => 'bg-amber-100 text-amber-800',
                    'danger'  => 'bg-red-100 text-red-800',
                    default   => 'bg-slate-100 text-slate-700',
                };
                $tipoTono = $ausencia->tipo->tono();
                $borderColor = match($tipoTono) {
                    'success' => 'border-l-emerald-400',
                    'info'    => 'border-l-blue-400',
                    'warning' => 'border-l-amber-400',
                    'danger'  => 'border-l-red-400',
                    default   => 'border-l-slate-300',
                };
                $esPendiente = $ausencia->estado === \App\Enums\EstadoAusencia::PENDIENTE;
                $urlEditar  = route('mobile.ausencias.editar', $ausencia);
            @endphp

            <div wire:key="aus-{{ $ausencia->id }}"
                 onclick="window.location='{{ $urlEditar }}'"
                 @class([
                     'mb-2 flex overflow-hidden rounded-lg border border-slate-200 border-l-4 bg-white shadow-sm',
                     'cursor-pointer transition-colors hover:border-primary-300 hover:bg-primary-50/30 active:scale-[0.99] active:transition-transform',
                     $borderColor,
                 ])>

                {{-- Contenido izquierdo --}}
                <div class="flex min-w-0 flex-1 flex-col p-3">
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $ausencia->tipo->etiqueta() }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ $ausencia->fecha_inicio->format('d/m/Y') }}
                        @if ($ausencia->fecha_inicio->ne($ausencia->fecha_fin))
                            → {{ $ausencia->fecha_fin->format('d/m/Y') }}
                            <span class="ml-1 text-slate-400">({{ $ausencia->diasNaturales() }} días)</span>
                        @endif
                    </p>
                    @if ($ausencia->motivo)
                        <p class="mt-1 truncate text-xs text-slate-400">{{ $ausencia->motivo }}</p>
                    @endif
                    @if ($ausencia->observaciones && $ausencia->estado === \App\Enums\EstadoAusencia::RECHAZADA)
                        <div class="mt-2 rounded-md bg-red-50 px-2.5 py-1.5 text-xs text-red-700">
                            <span class="font-medium">Motivo de rechazo:</span> {{ $ausencia->observaciones }}
                        </div>
                    @endif
                    {{-- Estado al fondo --}}
                    <div class="mt-2 border-t border-slate-100 pt-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $estadoClases }}">
                            {{ $ausencia->estado->etiqueta() }}
                        </span>
                    </div>
                </div>

                {{-- Columna derecha: papelera (solo PENDIENTE) --}}
                @if ($esPendiente)
                    <button type="button"
                            onclick="event.stopPropagation()"
                            wire:click="eliminar({{ $ausencia->id }})"
                            wire:confirm="¿Eliminar esta solicitud de ausencia?"
                            class="flex w-11 shrink-0 items-center justify-center border-l border-red-200 bg-red-50 text-red-400 transition-colors hover:bg-red-100 hover:text-red-600">
                        <x-heroicon-m-trash class="size-4" />
                    </button>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="mb-3 inline-flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-heroicon-o-calendar-days class="size-7" />
                </div>
                <p class="text-sm text-slate-600">No hay ausencias en este filtro.</p>
            </div>
        @endforelse

        <div class="mt-3">
            {{ $this->ausencias->links() }}
        </div>
    </div>

    {{-- Botón flotante nueva solicitud --}}
    @can('ausencias.solicitar')
        <div class="fixed bottom-6 right-4">
            <a href="{{ route('mobile.ausencias.nueva') }}"
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
