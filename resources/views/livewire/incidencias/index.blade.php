<div>
    <x-ui.page-header title="Incidencias" subtitle="Incidencias reportadas por los trabajadores." />

    {{-- ── Toolbar ─────────────────────────────────────────────────── --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por trabajador, título, descripción…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            {{-- Panel de filtros --}}
            <div class="grid gap-3 md:grid-cols-6" wire:key="panel-filtros-{{ $filtrosVersion }}">

                <x-ui.field label="Trabajador">
                    <x-ui.searchable-select
                        wire-model="filtroTrabajador"
                        :value="$filtroTrabajador"
                        :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim(($u->numero_empleado ? $u->numero_empleado.' · ' : '').trim($u->apellidos.' '.$u->nombre))])"
                        placeholder="Todos"
                    />
                </x-ui.field>

                <x-ui.field label="Tipo">
                    <x-ui.select wire:key="tipo-{{ $resetKey }}" wire:model.live="filtroTipo">
                        <option value="">Todos</option>
                        @foreach ($tipos as $t)
                            <option value="{{ $t->value }}">{{ $t->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="">Todos</option>
                        @foreach ($estados as $e)
                            <option value="{{ $e->value }}">{{ $e->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Prioridad">
                    <x-ui.select wire:key="prioridad-{{ $resetKey }}" wire:model.live="filtroPrioridad">
                        <option value="">Todas</option>
                        @foreach ($prioridades as $p)
                            <option value="{{ $p->value }}">{{ $p->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Desde">
                    <x-ui.date-input wireModel="fechaDesde" :value="$fechaDesde" :live="true" placeholder="dd/mm/aaaa" />
                </x-ui.field>

                <x-ui.field label="Hasta">
                    <x-ui.date-input wireModel="fechaHasta" :value="$fechaHasta" :live="true" placeholder="dd/mm/aaaa" />
                </x-ui.field>

            </div>

            {{-- Chips de filtros activos --}}
            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroTrabajador)
                            @php $tw = $this->trabajadoresDisponibles->firstWhere('id', $filtroTrabajador); @endphp
                            <x-ui.filter-chip label="Trabajador"
                                :value="$tw ? trim($tw->apellidos.' '.$tw->nombre) : '#'.$filtroTrabajador"
                                remove-action="quitarFiltroTrabajador" />
                        @endif
                        @if ($filtroTipo)
                            <x-ui.filter-chip label="Tipo"
                                :value="\App\Enums\TipoIncidencia::from($filtroTipo)->etiqueta()"
                                remove-action="quitarFiltroTipo" />
                        @endif
                        @if ($filtroEstado)
                            <x-ui.filter-chip label="Estado"
                                :value="\App\Enums\EstadoIncidencia::from($filtroEstado)->etiqueta()"
                                remove-action="quitarFiltroEstado" />
                        @endif
                        @if ($filtroPrioridad)
                            <x-ui.filter-chip label="Prioridad"
                                :value="\App\Enums\PrioridadIncidencia::from($filtroPrioridad)->etiqueta()"
                                remove-action="quitarFiltroPrioridad" />
                        @endif
                        @if ($fechaDesde)
                            <x-ui.filter-chip label="Desde"
                                :value="\Illuminate\Support\Carbon::parse($fechaDesde)->format('d/m/Y')"
                                remove-action="quitarFechaDesde" />
                        @endif
                        @if ($fechaHasta)
                            <x-ui.filter-chip label="Hasta"
                                :value="\Illuminate\Support\Carbon::parse($fechaHasta)->format('d/m/Y')"
                                remove-action="quitarFechaHasta" />
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

    {{-- ── Filas + paginación ──────────────────────────────────────── --}}
    <div class="mb-3 flex items-center justify-between">
        <div class="flex shrink-0 items-center gap-2">
            <span class="text-xs text-slate-500">Filas:</span>
            <select wire:model.live="porPagina"
                    class="rounded-md border-slate-300 py-1 pl-2 pr-7 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        {{ $incidencias->links() }}
    </div>

    {{-- ── Tabla ────────────────────────────────────────────────────── --}}
    <x-ui.data-table :colspan="9" empty="No hay incidencias para los filtros seleccionados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="id" :current-column="$ordenColumna" :current-direction="$ordenDireccion">ID</x-ui.sortable-header>
                <x-ui.sortable-header>Trabajador</x-ui.sortable-header>
                <x-ui.sortable-header column="tipo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">Tipo</x-ui.sortable-header>
                <x-ui.sortable-header column="titulo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">Título</x-ui.sortable-header>
                <x-ui.sortable-header column="prioridad" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">Prioridad</x-ui.sortable-header>
                <x-ui.sortable-header column="estado" :current-column="$ordenColumna" :current-direction="$ordenDireccion">Estado</x-ui.sortable-header>
                <x-ui.sortable-header column="created_at" :current-column="$ordenColumna" :current-direction="$ordenDireccion">Fecha</x-ui.sortable-header>
                <x-ui.sortable-header>Resolución</x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($incidencias as $inc)
                @php
                    $leftBorder = match($inc->prioridad->value) {
                        'urgente' => 'border-l-4 border-l-red-500',
                        'alta'    => 'border-l-4 border-l-amber-500',
                        default   => '',
                    };
                @endphp
                <tr wire:key="inc-{{ $inc->id }}" class="transition-colors hover:bg-slate-50 {{ $leftBorder }}">

                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $inc->id }}</td>

                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">
                            {{ trim($inc->trabajador?->apellidos . ' ' . $inc->trabajador?->nombre) ?: '—' }}
                        </div>
                        @if ($inc->trabajador?->numero_empleado)
                            <div class="text-xs text-slate-400">{{ $inc->trabajador->numero_empleado }}</div>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                        {{ $inc->tipo->etiqueta() }}
                    </td>

                    <td class="max-w-xs px-4 py-3">
                        <div class="font-medium text-slate-800">{{ $inc->titulo }}</div>
                        @if ($inc->descripcion)
                            <div class="mt-0.5 truncate text-xs text-slate-400">{{ $inc->descripcion }}</div>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-center">
                        <x-ui.badge :tone="$inc->prioridad->tono()" dot>
                            {{ $inc->prioridad->etiqueta() }}
                        </x-ui.badge>
                    </td>

                    <td class="px-4 py-3">
                        <x-ui.badge :tone="$inc->estado->tono()" dot>
                            {{ $inc->estado->etiqueta() }}
                        </x-ui.badge>
                    </td>

                    <td class="px-4 py-3 font-mono text-xs text-slate-600 whitespace-nowrap">
                        {{ $inc->created_at->format('d/m/Y') }}
                    </td>

                    <td class="max-w-xs px-4 py-3 text-xs text-slate-500">
                        @if ($inc->resolucion)
                            <div class="truncate">{{ $inc->resolucion }}</div>
                            @if ($inc->resolutor)
                                <div class="text-slate-400">
                                    {{ trim($inc->resolutor->apellidos . ' ' . $inc->resolutor->nombre) }}
                                    · {{ $inc->resuelto_at?->format('d/m/Y') }}
                                </div>
                            @endif
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <x-ui.icon-button
                                wire:click="abrirVer({{ $inc->id }})"
                                icon="heroicon-o-eye"
                                variant="ghost"
                                tooltip="Ver" />
                            @can('incidencias.modificar')
                                <x-ui.icon-button
                                    wire:click="abrirEditar({{ $inc->id }})"
                                    icon="heroicon-o-pencil-square"
                                    variant="info"
                                    tooltip="Editar" />
                                <x-ui.icon-button
                                    wire:click="confirmarEliminar({{ $inc->id }})"
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

    {{-- ── Modal ver / gestionar ──────────────────────────────────── --}}
    <x-ui.modal
        :show="$modalAbierto"
        :title="$soloLectura ? 'Ver incidencia' : 'Gestionar incidencia'"
        close-action="cerrarModal"
        size="md">

        @if ($modalAbierto)
            @php
                $tw = $this->trabajadoresDisponibles->firstWhere('id', $formTrabajador);
            @endphp

            {{-- Datos de la incidencia (siempre solo lectura) --}}
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.field label="Trabajador">
                        <x-ui.input :value="$tw ? trim($tw->apellidos.' '.$tw->nombre) : '—'" readonly />
                    </x-ui.field>
                    <x-ui.field label="Tipo">
                        <x-ui.input :value="$formTipo ? \App\Enums\TipoIncidencia::from($formTipo)->etiqueta() : '—'" readonly />
                    </x-ui.field>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.field label="Prioridad">
                        <x-ui.input :value="$formPrioridad ? \App\Enums\PrioridadIncidencia::from($formPrioridad)->etiqueta() : '—'" readonly />
                    </x-ui.field>
                    <x-ui.field label="Título">
                        <x-ui.input :value="$formTitulo ?: '—'" readonly />
                    </x-ui.field>
                </div>
                @if ($formDescripcion)
                    <x-ui.field label="Descripción">
                        <x-ui.textarea :value="$formDescripcion" rows="2" readonly />
                    </x-ui.field>
                @endif
            </div>

            {{-- Sección de gestión --}}
            <form wire:submit="guardar" id="form-incidencia" class="mt-4 space-y-4">

                <x-ui.field label="Estado" required :error="$errors->first('formEstado')">
                    @if ($soloLectura)
                        <x-ui.input :value="$formEstado ? \App\Enums\EstadoIncidencia::from($formEstado)->etiqueta() : '—'" readonly />
                    @else
                        <x-ui.select wire:model="formEstado">
                            @foreach ($estados as $e)
                                <option value="{{ $e->value }}">{{ $e->etiqueta() }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif
                </x-ui.field>

                <x-ui.field label="Resolución / Respuesta" :error="$errors->first('formResolucion')">
                    @if ($soloLectura)
                        <x-ui.textarea :value="$formResolucion ?: '—'" rows="3" readonly />
                    @else
                        <x-ui.textarea wire:model="formResolucion" rows="3"
                            placeholder="Indica cómo se ha resuelto o qué pasos se están dando…" />
                    @endif
                </x-ui.field>

            </form>
        @endif

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModal">
                {{ $soloLectura ? 'Cerrar' : 'Cancelar' }}
            </x-ui.button>
            @unless ($soloLectura)
                <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray"
                             type="submit" form="form-incidencia"
                             wire:loading.attr="disabled">
                    Guardar
                </x-ui.button>
            @endunless
        </x-slot:footer>
    </x-ui.modal>

    {{-- ── Modal confirmar eliminación ─────────────────────────────── --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar incidencia"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">¿Estás seguro de que quieres eliminar esta incidencia?</p>
                <p class="mt-1 text-sm text-slate-500">Esta acción no se puede deshacer.</p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})"
                         wire:loading.attr="disabled"
                         wire:target="eliminar">
                <span wire:loading.remove wire:target="eliminar">Eliminar</span>
                <span wire:loading wire:target="eliminar">Eliminando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

</div>
