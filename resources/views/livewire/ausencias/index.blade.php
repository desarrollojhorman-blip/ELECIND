<div x-data
     x-on:descargar.window="
        const a = document.createElement('a');
        a.href = $event.detail.url;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
     ">
    <x-ui.page-header title="Ausencias" subtitle="Solicitudes de ausencia y permisos de los trabajadores." />

    {{-- ── Toolbar ─────────────────────────────────────────────────── --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por trabajador, motivo…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('ausencias.ver_todas')
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan

                <x-ui.actions-menu label="Acciones" icon="heroicon-o-bars-3">
                    @can('ausencias.exportar')
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-down-tray"
                                                wire:click="exportarExcel"
                                                wire:loading.attr="disabled"
                                                wire:target="exportarExcel">
                            <span wire:loading.remove wire:target="exportarExcel">Exportar a Excel</span>
                            <span wire:loading wire:target="exportarExcel" class="inline-flex items-center gap-2">
                                <x-heroicon-o-arrow-path class="size-3 animate-spin" />
                                Generando…
                            </span>
                        </x-ui.actions-menu-item>
                        <x-ui.actions-menu-item icon="heroicon-o-document-arrow-down"
                                                wire:click="exportarPdf('vertical')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportarPdf('vertical')">
                            <span wire:loading.remove wire:target="exportarPdf('vertical')">PDF Vertical</span>
                            <span wire:loading wire:target="exportarPdf('vertical')" class="inline-flex items-center gap-2">
                                <x-heroicon-o-arrow-path class="size-3 animate-spin" />
                                Generando…
                            </span>
                        </x-ui.actions-menu-item>
                        <x-ui.actions-menu-item icon="heroicon-o-document-arrow-down"
                                                wire:click="exportarPdf('horizontal')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportarPdf('horizontal')">
                            <span wire:loading.remove wire:target="exportarPdf('horizontal')">PDF Horizontal</span>
                            <span wire:loading wire:target="exportarPdf('horizontal')" class="inline-flex items-center gap-2">
                                <x-heroicon-o-arrow-path class="size-3 animate-spin" />
                                Generando…
                            </span>
                        </x-ui.actions-menu-item>
                    @else
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-down-tray" disabled badge="Sin permiso">
                            Exportar a Excel
                        </x-ui.actions-menu-item>
                        <x-ui.actions-menu-item icon="heroicon-o-document-arrow-down" disabled badge="Sin permiso">
                            PDF Vertical
                        </x-ui.actions-menu-item>
                        <x-ui.actions-menu-item icon="heroicon-o-document-arrow-down" disabled badge="Sin permiso">
                            PDF Horizontal
                        </x-ui.actions-menu-item>
                    @endcan
                </x-ui.actions-menu>

                {{-- Toggle papelera --}}
                @if ($this->puedeVerPapelera)
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <input type="checkbox"
                               wire:model.live="verPapelera"
                               class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <x-heroicon-o-archive-box class="size-4" />
                        <span>Papelera</span>
                        @if ($this->totalPapelera > 0)
                            <span class="text-xs font-semibold text-slate-500">({{ $this->totalPapelera }})</span>
                        @endif
                    </label>
                @endif
            </x-slot:leftActions>

            {{-- Panel de filtros --}}
            <div class="grid gap-3 md:grid-cols-5" wire:key="panel-filtros-{{ $filtrosVersion }}">
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
                                :value="\App\Enums\TipoAusencia::from($filtroTipo)->etiqueta()"
                                remove-action="quitarFiltroTipo" />
                        @endif
                        @if ($filtroEstado)
                            <x-ui.filter-chip label="Estado"
                                :value="\App\Enums\EstadoAusencia::from($filtroEstado)->etiqueta()"
                                remove-action="quitarFiltroEstado" />
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
        {{ $ausencias->links() }}
    </div>

    {{-- ── Tabla ────────────────────────────────────────────────────── --}}
    <x-ui.data-table :colspan="9" empty="No hay ausencias para los filtros seleccionados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="id" :current-column="$ordenColumna" :current-direction="$ordenDireccion">ID</x-ui.sortable-header>
                <x-ui.sortable-header>Trabajador</x-ui.sortable-header>
                <x-ui.sortable-header column="tipo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">Tipo</x-ui.sortable-header>
                <x-ui.sortable-header column="fecha_inicio" :current-column="$ordenColumna" :current-direction="$ordenDireccion">Fecha inicio</x-ui.sortable-header>
                <x-ui.sortable-header column="fecha_fin" :current-column="$ordenColumna" :current-direction="$ordenDireccion">Fecha fin</x-ui.sortable-header>
                <x-ui.sortable-header align="center">Días</x-ui.sortable-header>
                <x-ui.sortable-header column="estado" :current-column="$ordenColumna" :current-direction="$ordenDireccion">Estado</x-ui.sortable-header>
                <x-ui.sortable-header>Aprobado por</x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($ausencias as $ausencia)
                <tr wire:key="aus-{{ $ausencia->id }}" class="transition-colors hover:bg-slate-50">

                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $ausencia->id }}</td>

                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">
                            {{ trim($ausencia->trabajador?->apellidos . ' ' . $ausencia->trabajador?->nombre) ?: '—' }}
                        </div>
                        @if ($ausencia->trabajador?->numero_empleado)
                            <div class="text-xs text-slate-400">{{ $ausencia->trabajador->numero_empleado }}</div>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                        {{ $ausencia->tipo->etiqueta() }}
                    </td>

                    <td class="px-4 py-3 font-mono text-xs text-slate-600 whitespace-nowrap">
                        {{ $ausencia->fecha_inicio->format('d/m/Y') }}
                    </td>

                    <td class="px-4 py-3 font-mono text-xs text-slate-600 whitespace-nowrap">
                        {{ $ausencia->fecha_fin->format('d/m/Y') }}
                    </td>

                    <td class="px-4 py-3 text-center tabular-nums text-slate-700">
                        {{ $ausencia->diasNaturales() }}
                    </td>

                    <td class="px-4 py-3">
                        @if ($ausencia->trashed())
                            <x-ui.badge tone="danger" dot>Eliminada</x-ui.badge>
                        @else
                            <x-ui.badge :tone="$ausencia->estado->tono()" dot>
                                {{ $ausencia->estado->etiqueta() }}
                            </x-ui.badge>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                        @if ($ausencia->aprobador)
                            <div>{{ trim($ausencia->aprobador->apellidos . ' ' . $ausencia->aprobador->nombre) }}</div>
                            <div class="text-slate-400">{{ $ausencia->aprobado_at?->format('d/m/Y') }}</div>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($ausencia->trashed())
                                <x-ui.icon-button
                                    wire:click="abrirVer({{ $ausencia->id }})"
                                    icon="heroicon-o-eye"
                                    variant="ghost"
                                    tooltip="Ver" />
                                @can('ausencias.ver_todas')
                                    <x-ui.icon-button
                                        wire:click="restaurar({{ $ausencia->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="restaurar({{ $ausencia->id }})"
                                        variant="success"
                                        tooltip="Restaurar">
                                        <span wire:loading.remove wire:target="restaurar({{ $ausencia->id }})">
                                            <x-heroicon-o-arrow-uturn-left class="size-4" />
                                        </span>
                                        <svg wire:loading wire:target="restaurar({{ $ausencia->id }})" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                        </svg>
                                    </x-ui.icon-button>
                                @endcan
                            @else
                                <x-ui.icon-button
                                    wire:click="abrirVer({{ $ausencia->id }})"
                                    icon="heroicon-o-eye"
                                    variant="ghost"
                                    tooltip="Ver" />
                                @can('ausencias.ver_todas')
                                    <x-ui.icon-button
                                        wire:click="abrirEditar({{ $ausencia->id }})"
                                        icon="heroicon-o-pencil-square"
                                        variant="info"
                                        tooltip="Editar" />
                                    <x-ui.icon-button
                                        wire:click="confirmarEliminar({{ $ausencia->id }})"
                                        icon="heroicon-o-trash"
                                        variant="danger"
                                        tooltip="Eliminar" />
                                @endcan
                            @endif
                        </div>
                    </td>

                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    {{-- ── Modal crear / editar / ver ─────────────────────────────── --}}
    <x-ui.modal
        :show="$modalAbierto"
        :title="$soloLectura ? 'Ver ausencia' : ($editingId ? 'Editar ausencia' : 'Nueva ausencia')"
        close-action="cerrarModal"
        size="md">

        <form wire:submit="guardar" id="form-ausencia" class="space-y-4">

            <x-ui.field label="Trabajador" required :error="$errors->first('formTrabajador')">
                @if ($soloLectura)
                    @php $tw = $this->trabajadoresDisponibles->firstWhere('id', $formTrabajador); @endphp
                    <x-ui.input :value="$tw ? trim($tw->apellidos.' '.$tw->nombre) : '—'" readonly />
                @else
                    <x-ui.select wire:model="formTrabajador">
                        <option value="">— Selecciona trabajador —</option>
                        @foreach ($this->trabajadoresDisponibles as $u)
                            <option value="{{ $u->id }}">
                                {{ trim(($u->numero_empleado ? $u->numero_empleado.' · ' : '').trim($u->apellidos.' '.$u->nombre)) }}
                            </option>
                        @endforeach
                    </x-ui.select>
                @endif
            </x-ui.field>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field label="Tipo" required :error="$errors->first('formTipo')">
                    @if ($soloLectura)
                        <x-ui.input :value="$formTipo ? \App\Enums\TipoAusencia::from($formTipo)->etiqueta() : '—'" readonly />
                    @else
                        <x-ui.select wire:model="formTipo">
                            <option value="">— Tipo —</option>
                            @foreach ($tipos as $t)
                                <option value="{{ $t->value }}">{{ $t->etiqueta() }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif
                </x-ui.field>

                @if ($editingId)
                    <x-ui.field label="Estado" required :error="$errors->first('formEstadoForm')">
                        @if ($soloLectura)
                            <x-ui.input :value="$formEstadoForm ? \App\Enums\EstadoAusencia::from($formEstadoForm)->etiqueta() : '—'" readonly />
                        @else
                            <x-ui.select wire:model="formEstadoForm">
                                @foreach ($estados as $e)
                                    <option value="{{ $e->value }}">{{ $e->etiqueta() }}</option>
                                @endforeach
                            </x-ui.select>
                        @endif
                    </x-ui.field>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field label="Fecha inicio" required :error="$errors->first('formFechaInicio')">
                    @if ($soloLectura)
                        <x-ui.input :value="$formFechaInicio ? \Illuminate\Support\Carbon::parse($formFechaInicio)->format('d/m/Y') : '—'" readonly />
                    @else
                        <x-ui.date-input wireModel="formFechaInicio" :value="$formFechaInicio" :live="false" placeholder="dd/mm/aaaa" />
                    @endif
                </x-ui.field>

                <x-ui.field label="Fecha fin" required :error="$errors->first('formFechaFin')">
                    @if ($soloLectura)
                        <x-ui.input :value="$formFechaFin ? \Illuminate\Support\Carbon::parse($formFechaFin)->format('d/m/Y') : '—'" readonly />
                    @else
                        <x-ui.date-input wireModel="formFechaFin" :value="$formFechaFin" :live="false" placeholder="dd/mm/aaaa" />
                    @endif
                </x-ui.field>
            </div>

            <x-ui.field label="Motivo" :error="$errors->first('formMotivo')">
                @if ($soloLectura)
                    <x-ui.textarea :value="$formMotivo ?: '—'" rows="2" readonly />
                @else
                    <x-ui.textarea wire:model="formMotivo" rows="2" placeholder="Motivo de la ausencia…" />
                @endif
            </x-ui.field>

            <x-ui.field label="Observaciones internas">
                @if ($soloLectura)
                    <x-ui.textarea :value="$formObservaciones ?: '—'" rows="2" readonly />
                @else
                    <x-ui.textarea wire:model="formObservaciones" rows="2" placeholder="Notas internas, motivo de rechazo…" />
                @endif
            </x-ui.field>

        </form>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModal">
                {{ $soloLectura ? 'Cerrar' : 'Cancelar' }}
            </x-ui.button>
            @unless ($soloLectura)
                <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray"
                             type="submit" form="form-ausencia"
                             wire:loading.attr="disabled">
                    Guardar
                </x-ui.button>
            @endunless
        </x-slot:footer>
    </x-ui.modal>

    {{-- ── Modal confirmar eliminación ─────────────────────────────── --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar ausencia"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">¿Estás seguro de que quieres eliminar esta ausencia?</p>
                <p class="mt-1 text-sm text-slate-500">Se enviará a la papelera y podrás restaurarla después.</p>
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
