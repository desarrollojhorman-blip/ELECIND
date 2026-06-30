<div>
    <x-ui.page-header title="Albaranes" subtitle="Consulta y gestión de todos los albaranes." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por número, cliente o proyecto…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Albaran::class)
                    <x-ui.button as="a" href="{{ route('albaranes.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
                <x-ui.actions-menu label="Acciones" icon="heroicon-o-bars-3">
                    <x-ui.actions-menu-item icon="heroicon-o-arrow-down-tray" disabled badge="Pronto">
                        Exportar a Excel
                    </x-ui.actions-menu-item>
                    <x-ui.actions-menu-item icon="heroicon-o-document-arrow-down" disabled badge="Pronto">
                        Exportar a PDF
                    </x-ui.actions-menu-item>
                    <x-ui.actions-menu-divider />
                    <x-ui.actions-menu-item icon="heroicon-o-printer" disabled badge="Pronto">
                        Imprimir lista
                    </x-ui.actions-menu-item>
                </x-ui.actions-menu>
            </x-slot:leftActions>

            {{-- Panel de filtros --}}
            <div class="grid gap-3 md:grid-cols-4">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="">Todos los estados</option>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->value }}">{{ $estado->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Cliente">
                    <x-ui.select wire:key="cliente-{{ $resetKey }}" wire:model.live="filtroCliente">
                        <option value="">Todos los clientes</option>
                        @foreach ($this->clientesDisponibles as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Tipo jornada">
                    <x-ui.select wire:key="tipo-{{ $resetKey }}" wire:model.live="filtroTipo">
                        <option value="">Todos los tipos</option>
                        @foreach ($tiposHora as $tipo)
                            <option value="{{ $tipo->value }}">{{ $tipo->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Fecha desde">
                    <x-ui.input wire:key="desde-{{ $resetKey }}" type="date" wire:model.live="filtroDesde" />
                </x-ui.field>

                <x-ui.field label="Fecha hasta">
                    <x-ui.input wire:key="hasta-{{ $resetKey }}" type="date" wire:model.live="filtroHasta" />
                </x-ui.field>
            </div>

            {{-- Chips filtros activos --}}
            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroEstado !== '')
                            <x-ui.filter-chip
                                label="Estado"
                                :value="\App\Enums\EstadoAlbaran::from($filtroEstado)->etiqueta()"
                                remove-action="quitarFiltroEstado" />
                        @endif
                        @if ($filtroCliente !== null)
                            <x-ui.filter-chip
                                label="Cliente"
                                :value="$this->clientesDisponibles->firstWhere('id', $filtroCliente)?->nombre ?? '?'"
                                remove-action="quitarFiltroCliente" />
                        @endif
                        @if ($filtroTipo !== '')
                            <x-ui.filter-chip
                                label="Tipo"
                                :value="\App\Enums\TipoHora::from($filtroTipo)->etiqueta()"
                                remove-action="quitarFiltroTipo" />
                        @endif
                        @if ($filtroDesde !== '')
                            <x-ui.filter-chip
                                label="Desde"
                                :value="\Carbon\Carbon::parse($filtroDesde)->format('d/m/Y')"
                                remove-action="quitarFiltroDesde" />
                        @endif
                        @if ($filtroHasta !== '')
                            <x-ui.filter-chip
                                label="Hasta"
                                :value="\Carbon\Carbon::parse($filtroHasta)->format('d/m/Y')"
                                remove-action="quitarFiltroHasta" />
                        @endif
                        <button type="button" wire:click="limpiarFiltros"
                                class="text-xs text-slate-500 underline hover:text-slate-700">
                            Limpiar todos
                        </button>
                    </div>
                </x-slot:chips>
            @endif
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla --}}
    <div class="mb-3 flex items-center justify-between">
        <div class="flex shrink-0 items-center gap-2">
            <span class="text-xs text-slate-500">Filas:</span>
            <select wire:model.live="porPagina"
                    class="rounded-md border-slate-300 py-1 pl-2 pr-7 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="250">250</option>
                <option value="500">500</option>
            </select>
        </div>
        {{ $albaranes->links() }}
    </div>
    <x-ui.data-table :colspan="8" empty="No hay albaranes que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="numero" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nº Albarán
                </x-ui.sortable-header>
                <x-ui.sortable-header column="fecha" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Fecha
                </x-ui.sortable-header>
                <x-ui.sortable-header>Cliente</x-ui.sortable-header>
                <x-ui.sortable-header>Proyecto</x-ui.sortable-header>
                <x-ui.sortable-header>Concepto</x-ui.sortable-header>
                <x-ui.sortable-header column="tipo_hora" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Jornada
                </x-ui.sortable-header>
                <x-ui.sortable-header column="estado" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($albaranes as $albaran)
                <tr wire:key="alb-{{ $albaran->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <span class="font-mono text-sm font-medium text-slate-900">{{ $albaran->numero }}</span>
                        @if ($albaran->creador)
                            <div class="text-xs text-slate-400">{{ trim($albaran->creador->nombre.' '.$albaran->creador->apellidos) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        {{ $albaran->fecha->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        {{ $albaran->cliente?->nombre ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        @if ($albaran->proyecto)
                            <div>{{ $albaran->proyecto->nombre }}</div>
                            @if ($albaran->proyecto->codigo)
                                <div class="font-mono text-xs text-slate-400">{{ $albaran->proyecto->codigo }}</div>
                            @endif
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $albaran->concepto?->nombre ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $tipoTone = match (true) {
                                $albaran->tipo_hora->esFestivo() && $albaran->tipo_hora->esNoche() => 'danger',
                                $albaran->tipo_hora->esFestivo() => 'warning',
                                $albaran->tipo_hora->esNoche() => 'info',
                                default => 'neutral',
                            };
                        @endphp
                        <x-ui.badge :tone="$tipoTone">{{ $albaran->tipo_hora->etiqueta() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :tone="$albaran->estado->tono()" dot>{{ $albaran->estado->etiqueta() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <x-ui.icon-button
                                as="a"
                                href="{{ route('albaranes.ver', $albaran) }}"
                                wire:navigate
                                icon="heroicon-o-eye"
                                variant="neutral"
                                tooltip="Ver detalle" />
                            @can('update', $albaran)
                                <x-ui.icon-button
                                    as="a"
                                    href="{{ route('albaranes.editar', $albaran) }}"
                                    wire:navigate.fresh
                                    icon="heroicon-o-pencil-square"
                                    variant="info"
                                    tooltip="Editar" />
                            @endcan
                            @can('delete', $albaran)
                                <x-ui.icon-button
                                    wire:click="confirmarEliminar({{ $albaran->id }})"
                                    icon="heroicon-o-trash"
                                    variant="danger"
                                    tooltip="Eliminar" />
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar albarán"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción <strong>eliminará definitivamente</strong> el albarán. No hay papelera.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Su parte de origen volverá a quedar <strong>abierto</strong> y editable.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})"
                         wire:loading.attr="disabled"
                         wire:target="eliminar">
                <x-heroicon-o-trash wire:loading.remove wire:target="eliminar" class="size-4" />
                <svg wire:loading wire:target="eliminar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="eliminar">Eliminar</span>
                <span wire:loading wire:target="eliminar">Eliminando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
