<div>
    <x-ui.page-header title="Partes" :subtitle="$totalPartes.' '.($totalPartes === 1 ? 'parte registrado' : 'partes registrados')" />

    <x-ui.flash />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por código, operario, proyecto o cliente…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Parte::class)
                    <x-ui.button as="a" href="{{ route('partes.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
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
                <x-ui.field label="Operario">
                    <x-ui.select wire:key="operario-{{ $resetKey }}" wire:model.live="filtroOperario">
                        <option value="">Todos los operarios</option>
                        @foreach ($this->operariosDisponibles as $u)
                            <option value="{{ $u->id }}">{{ trim($u->apellidos.' '.$u->nombre) }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Proyecto">
                    <x-ui.select wire:key="proyecto-{{ $resetKey }}" wire:model.live="filtroProyecto">
                        <option value="">Todos los proyectos</option>
                        @foreach ($this->proyectosDisponibles as $p)
                            <option value="{{ $p->id }}">{{ $p->codigo }} · {{ $p->nombre }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Cliente">
                    <x-ui.select wire:key="cliente-{{ $resetKey }}" wire:model.live="filtroCliente">
                        <option value="">Todos los clientes</option>
                        @foreach ($this->clientesDisponibles as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="abierto">Abierto</option>
                        <option value="cerrado">Cerrado</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="¿Tiene albarán?">
                    <x-ui.select wire:key="con-albaran-{{ $resetKey }}" wire:model.live="filtroConAlbaran">
                        <option value="">Todos</option>
                        <option value="si">Sí (con albarán)</option>
                        <option value="no">No (solo parte)</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Fecha desde">
                    <x-ui.input wire:key="desde-{{ $resetKey }}" type="date" wire:model.live="fechaDesde" />
                </x-ui.field>

                <x-ui.field label="Fecha hasta">
                    <x-ui.input wire:key="hasta-{{ $resetKey }}" type="date" wire:model.live="fechaHasta" />
                </x-ui.field>
            </div>

            {{-- Chips filtros activos --}}
            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroOperario !== null)
                            @php $operario = $this->operariosDisponibles->firstWhere('id', $filtroOperario); @endphp
                            <x-ui.filter-chip
                                label="Operario"
                                :value="$operario ? trim($operario->apellidos.' '.$operario->nombre) : '?'"
                                remove-action="quitarFiltroOperario" />
                        @endif
                        @if ($filtroProyecto !== null)
                            @php $proyecto = $this->proyectosDisponibles->firstWhere('id', $filtroProyecto); @endphp
                            <x-ui.filter-chip
                                label="Proyecto"
                                :value="$proyecto ? $proyecto->codigo : '?'"
                                remove-action="quitarFiltroProyecto" />
                        @endif
                        @if ($filtroCliente !== null)
                            @php $cliente = $this->clientesDisponibles->firstWhere('id', $filtroCliente); @endphp
                            <x-ui.filter-chip
                                label="Cliente"
                                :value="$cliente ? $cliente->nombre : '?'"
                                remove-action="quitarFiltroCliente" />
                        @endif
                        @if ($filtroEstado !== '')
                            <x-ui.filter-chip
                                label="Estado"
                                :value="ucfirst($filtroEstado)"
                                remove-action="quitarFiltroEstado" />
                        @endif
                        @if ($filtroConAlbaran !== '')
                            <x-ui.filter-chip
                                label="Albarán"
                                :value="$filtroConAlbaran === 'si' ? 'Con albarán' : 'Solo parte'"
                                remove-action="quitarFiltroConAlbaran" />
                        @endif
                        @if ($fechaDesde !== '' || $fechaHasta !== '')
                            <x-ui.filter-chip
                                label="Fechas"
                                :value="($fechaDesde ?: '...').' → '.($fechaHasta ?: '...')"
                                remove-action="quitarFiltroFechas" />
                        @endif
                    </div>
                </x-slot:chips>
            @endif
        </x-ui.search-and-filter>
    </div>

    {{-- ── Filas + paginación ───────────────────────────────────── --}}
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
            </select>
        </div>
        {{ $partes->links() }}
    </div>

    {{-- ── Tabla ───────────────────────────────────────────────── --}}
    <x-ui.data-table colspan="8" empty="No hay partes que coincidan con los filtros.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="numero" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nº Parte
                </x-ui.sortable-header>
                <x-ui.sortable-header column="fecha" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Fecha
                </x-ui.sortable-header>
                <x-ui.sortable-header column="creador_apellidos_snapshot" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Creador
                </x-ui.sortable-header>
                <x-ui.sortable-header column="proyecto_nombre_snapshot" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Proyecto
                </x-ui.sortable-header>
                <x-ui.sortable-header column="cliente_nombre_snapshot" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Cliente
                </x-ui.sortable-header>
                <x-ui.sortable-header align="center">Tipo</x-ui.sortable-header>
                <x-ui.sortable-header column="estado" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($partes as $parte)
                <tr wire:key="parte-{{ $parte->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $parte->codigo }}</td>
                    <td class="px-4 py-3 text-xs text-slate-700">{{ $parte->fecha?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $parte->operario_nombre_snapshot ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="font-mono text-xs text-slate-500">{{ $parte->proyecto_codigo_snapshot ?? '—' }}</div>
                        <div class="text-xs text-slate-700">{{ $parte->proyecto_nombre_snapshot ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $parte->cliente_nombre_snapshot ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($parte->tieneAlbaran())
                            <span class="inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">Albarán</span>
                        @else
                            <span class="inline-flex items-center rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Solo parte</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($parte->estado === 'abierto')
                            <span class="inline-flex items-center rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Abierto</span>
                        @else
                            <span class="inline-flex items-center rounded bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Cerrado</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('view', $parte)
                                <x-ui.icon-button as="a" href="{{ route('partes.ver', $parte) }}" wire:navigate
                                    icon="heroicon-o-eye" variant="info" tooltip="Ver" />
                            @endcan
                            @can('update', $parte)
                                <x-ui.icon-button as="a" href="{{ route('partes.editar', $parte) }}" wire:navigate
                                    icon="heroicon-o-pencil-square" variant="neutral" tooltip="Editar" />
                            @endcan
                            @can('delete', $parte)
                                <x-ui.icon-button
                                    icon="heroicon-o-trash"
                                    variant="danger"
                                    tooltip="Eliminar"
                                    wire:click="confirmarEliminar({{ $parte->id }})"
                                    wire:confirm="¿Eliminar el parte {{ $parte->codigo }}? Esta acción se puede revertir desde papelera." />
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>
</div>
