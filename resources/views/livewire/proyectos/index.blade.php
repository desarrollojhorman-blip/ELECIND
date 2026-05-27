<div>
    <x-ui.page-header title="Proyectos" subtitle="Gestión de proyectos: grupos, clientes, responsables y planificación." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por nombre, código o descripción…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Proyecto::class)
                    <x-ui.button variant="success" as="a" href="{{ route('proyectos.crear') }}" wire:navigate icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan

                <x-ui.actions-menu label="Acciones" icon="heroicon-o-bars-3">
                    <x-ui.actions-menu-item icon="heroicon-o-arrow-up-tray" disabled badge="Pronto">
                        Importar desde Excel/CSV
                    </x-ui.actions-menu-item>
                    <x-ui.actions-menu-divider />
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

            <div class="grid gap-3 md:grid-cols-4">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="todos">Todos</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="cerrado">Cerrado</option>
                        <option value="papelera">En papelera</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Grupo">
                    <x-ui.select wire:key="tipo-{{ $resetKey }}" wire:model.live="filtroTipo">
                        <option value="">Todos los grupos</option>
                        @foreach ($this->tiposDisponibles as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
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

                <x-ui.field label="Responsable">
                    <x-ui.select wire:key="resp-{{ $resetKey }}" wire:model.live="filtroResponsable">
                        <option value="">Todos los responsables</option>
                        @foreach ($this->responsablesDisponibles as $resp)
                            <option value="{{ $resp->id }}">{{ trim($resp->nombre.' '.$resp->apellidos) }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroEstado !== 'todos')
                            <x-ui.filter-chip label="Estado" :value="ucfirst($filtroEstado)" remove-action="quitarFiltroEstado" />
                        @endif
                        @if ($filtroTipo !== null)
                            <x-ui.filter-chip label="Grupo"
                                :value="$this->tiposDisponibles->firstWhere('id', $filtroTipo)?->nombre ?? '?'"
                                remove-action="quitarFiltroTipo" />
                        @endif
                        @if ($filtroCliente !== null)
                            <x-ui.filter-chip label="Cliente"
                                :value="$this->clientesDisponibles->firstWhere('id', $filtroCliente)?->nombre ?? '?'"
                                remove-action="quitarFiltroCliente" />
                        @endif
                        @if ($filtroResponsable !== null)
                            @php $r = $this->responsablesDisponibles->firstWhere('id', $filtroResponsable); @endphp
                            <x-ui.filter-chip label="Responsable"
                                :value="$r ? trim($r->nombre.' '.$r->apellidos) : '?'"
                                remove-action="quitarFiltroResponsable" />
                        @endif
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
        {{ $proyectos->links() }}
    </div>
    <x-ui.data-table :colspan="8" empty="No hay proyectos que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="codigo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Código
                </x-ui.sortable-header>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre proyecto
                </x-ui.sortable-header>
                <x-ui.sortable-header>Grupo</x-ui.sortable-header>
                <x-ui.sortable-header>Cliente</x-ui.sortable-header>
                <x-ui.sortable-header column="fecha_inicio" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Fechas
                </x-ui.sortable-header>
                <x-ui.sortable-header column="albaranes_count" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Albaranes
                </x-ui.sortable-header>
                <x-ui.sortable-header column="estado" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($proyectos as $proyecto)
                @php
                    $estadoTone = match ($proyecto->estado) {
                        'activo'   => 'success',
                        'inactivo' => 'neutral',
                        'cerrado'  => 'info',
                        default    => 'neutral',
                    };
                @endphp
                <tr wire:key="proy-{{ $proyecto->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">
                        {{ $proyecto->codigo ?? '—' }}
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-900">
                        {{ $proyecto->nombre }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($proyecto->tipoProyecto)
                            <x-ui.badge tone="primary">{{ $proyecto->tipoProyecto->nombre }}</x-ui.badge>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $proyecto->cliente?->nombre ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        @if ($proyecto->fecha_inicio)
                            {{ $proyecto->fecha_inicio->format('d/m/Y') }}
                            @if ($proyecto->fecha_fin)
                                → {{ $proyecto->fecha_fin->format('d/m/Y') }}
                            @endif
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-slate-700">
                        @if ($proyecto->albaranes_count)
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                {{ $proyecto->albaranes_count }}
                            </span>
                        @else
                            <span class="text-slate-300">0</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($proyecto->trashed())
                            <x-ui.badge tone="danger" dot>Eliminado</x-ui.badge>
                        @else
                            <x-ui.badge :tone="$estadoTone" dot>{{ ucfirst($proyecto->estado) }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($proyecto->trashed())
                                @can('restore', $proyecto)
                                    <x-ui.icon-button
                                        wire:click="restaurar({{ $proyecto->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="restaurar({{ $proyecto->id }})"
                                        variant="success"
                                        tooltip="Restaurar">
                                        <span wire:loading.remove wire:target="restaurar({{ $proyecto->id }})">
                                            <x-heroicon-o-arrow-uturn-left class="size-4" />
                                        </span>
                                        <svg wire:loading wire:target="restaurar({{ $proyecto->id }})" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                    </x-ui.icon-button>
                                @endcan
                            @else
                                @can('view', $proyecto)
                                    <x-ui.icon-button as="a" href="{{ route('proyectos.ver', $proyecto) }}" wire:navigate
                                        icon="heroicon-o-eye" variant="neutral" tooltip="Ver detalle" />
                                @endcan
                                @can('update', $proyecto)
                                    <x-ui.icon-button as="a" href="{{ route('proyectos.editar', $proyecto) }}" wire:navigate.fresh
                                        icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                @endcan
                                @can('delete', $proyecto)
                                    <x-ui.icon-button wire:click="confirmarEliminar({{ $proyecto->id }})"
                                        icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal :show="$confirmarEliminarId !== null"
        title="Eliminar proyecto"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el proyecto a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Albaranes y horas asociadas mantendrán la referencia hasta que el proyecto sea restaurado.
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
