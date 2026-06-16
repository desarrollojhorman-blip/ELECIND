<div>
    <x-ui.page-header title="Partes" :subtitle="$totalPartes.' '.($totalPartes === 1 ? 'parte registrado' : 'partes registrados')">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('web.dashboard') }}" wire:navigate variant="neutral" icon="heroicon-o-home">
                Inicio
            </x-ui.button>
            @can('create', App\Models\Parte::class)
                <x-ui.button as="a" href="{{ route('partes.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                    Nuevo
                </x-ui.button>
            @endcan
        </x-slot:actionsLeft>
    </x-ui.page-header>

    <x-ui.flash />

    {{-- ── Filtros ──────────────────────────────────────────────── --}}
    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <x-ui.field label="Buscar">
                <x-ui.input wire:model.live.debounce.400ms="buscar"
                    placeholder="Código, observaciones, operario…" />
            </x-ui.field>

            <x-ui.field label="Operario">
                <x-ui.select wire:model.live="filtroOperario">
                    <option value="">Todos</option>
                    @foreach ($this->operariosDisponibles as $u)
                        <option value="{{ $u->id }}">{{ trim($u->apellidos.' '.$u->nombre) }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Proyecto">
                <x-ui.select wire:model.live="filtroProyecto">
                    <option value="">Todos</option>
                    @foreach ($this->proyectosDisponibles as $p)
                        <option value="{{ $p->id }}">{{ $p->codigo }} · {{ $p->nombre }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Cliente">
                <x-ui.select wire:model.live="filtroCliente">
                    <option value="">Todos</option>
                    @foreach ($this->clientesDisponibles as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        </div>

        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4">
            <x-ui.field label="Estado">
                <x-ui.select wire:model.live="filtroEstado">
                    <option value="">Todos</option>
                    <option value="abierto">Abierto</option>
                    <option value="cerrado">Cerrado</option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="¿Es albarán?">
                <x-ui.select wire:model.live="filtroEsAlbaran">
                    <option value="">Todos</option>
                    <option value="si">Sí (con albarán)</option>
                    <option value="no">No (solo parte)</option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Desde">
                <x-ui.date-input wireModel="fechaDesde" :value="$fechaDesde" :live="true" placeholder="dd/mm/aaaa" />
            </x-ui.field>

            <x-ui.field label="Hasta">
                <x-ui.date-input wireModel="fechaHasta" :value="$fechaHasta" :live="true" placeholder="dd/mm/aaaa" />
            </x-ui.field>
        </div>

        @if ($buscar || $filtroOperario || $filtroProyecto || $filtroCliente || $filtroEstado || $filtroEsAlbaran || $fechaDesde || $fechaHasta)
            <div class="mt-3 flex justify-end">
                <button wire:click="limpiarFiltros" class="text-xs text-primary-600 underline hover:text-primary-800">
                    Limpiar filtros
                </button>
            </div>
        @endif
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
                <x-ui.sortable-header column="codigo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Código
                </x-ui.sortable-header>
                <x-ui.sortable-header column="fecha" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Fecha
                </x-ui.sortable-header>
                <x-ui.sortable-header column="operario_nombre_snapshot" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Operario
                </x-ui.sortable-header>
                <x-ui.sortable-header column="proyecto_nombre_snapshot" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Proyecto
                </x-ui.sortable-header>
                <x-ui.sortable-header column="cliente_nombre_snapshot" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Cliente
                </x-ui.sortable-header>
                <x-ui.sortable-header column="es_albaran" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Tipo
                </x-ui.sortable-header>
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
                        @if ($parte->es_albaran)
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
