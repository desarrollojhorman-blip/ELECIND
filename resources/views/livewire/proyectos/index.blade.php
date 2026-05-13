<div>
    <x-ui.page-header title="Proyectos" subtitle="Gestión de proyectos: tipos, clientes, responsables, fechas y estado." />

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
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
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
                        <option value="borrador">Borrador</option>
                        <option value="activo">Activo</option>
                        <option value="cerrado">Cerrado</option>
                        <option value="archivado">Archivado</option>
                        <option value="papelera">En papelera</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Tipo">
                    <x-ui.select wire:key="tipo-{{ $resetKey }}" wire:model.live="filtroTipo">
                        <option value="">Todos los tipos</option>
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
                            <x-ui.filter-chip label="Tipo"
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
    <x-ui.data-table :colspan="7" empty="No hay proyectos que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header column="codigo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Código
                </x-ui.sortable-header>
                <x-ui.sortable-header>Tipo</x-ui.sortable-header>
                <x-ui.sortable-header>Cliente</x-ui.sortable-header>
                <x-ui.sortable-header>Responsable</x-ui.sortable-header>
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
                        'borrador' => 'neutral',
                        'activo' => 'success',
                        'cerrado' => 'info',
                        'archivado' => 'warning',
                        default => 'neutral',
                    };
                @endphp
                <tr wire:key="proy-{{ $proyecto->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ $proyecto->nombre }}</div>
                        @if ($proyecto->fecha_inicio)
                            <div class="text-xs text-slate-500">
                                {{ $proyecto->fecha_inicio->format('d/m/Y') }}
                                @if ($proyecto->fecha_fin) → {{ $proyecto->fecha_fin->format('d/m/Y') }} @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">
                        {{ $proyecto->codigo ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($proyecto->tipoProyecto)
                            <x-ui.badge tone="primary">{{ $proyecto->tipoProyecto->nombre }}</x-ui.badge>
                        @else
                            <span class="text-xs text-slate-400">Sin tipo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $proyecto->empresaCliente?->nombre ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        @if ($proyecto->responsablePrincipal)
                            {{ trim($proyecto->responsablePrincipal->nombre.' '.$proyecto->responsablePrincipal->apellidos) }}
                        @else
                            <span class="text-xs text-slate-400">Sin asignar</span>
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
                                    <x-ui.icon-button wire:click="restaurar({{ $proyecto->id }})"
                                        icon="heroicon-o-arrow-uturn-left" variant="success" tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('update', $proyecto)
                                    <x-ui.icon-button wire:click="abrirEditar({{ $proyecto->id }})"
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

    <div class="mt-3">{{ $proyectos->links() }}</div>

    {{-- Modal principal: crear/editar proyecto --}}
    <x-ui.modal :show="$modalAbierto"
        :title="$form->id ? 'Editar proyecto' : 'Nuevo proyecto'"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-proyecto" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')" class="md:col-span-2">
                    <x-ui.input wire:model="form.nombre" autofocus />
                </x-ui.field>

                <x-ui.field label="Código" :error="$errors->first('form.codigo')"
                            hint="Único por cliente. Se usará en albaranes y reportes.">
                    <x-ui.input wire:model="form.codigo" placeholder="Ej. MAR-A-2026" class="font-mono" />
                </x-ui.field>

                <x-ui.field label="Estado" required :error="$errors->first('form.estado')">
                    <x-ui.select wire:model="form.estado">
                        <option value="borrador">Borrador</option>
                        <option value="activo">Activo</option>
                        <option value="cerrado">Cerrado</option>
                        <option value="archivado">Archivado</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Cliente" required :error="$errors->first('form.empresa_cliente_id')">
                    <x-ui.select wire:model="form.empresa_cliente_id">
                        <option value="">— Selecciona cliente —</option>
                        @foreach ($this->clientesDisponibles as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Tipo de proyecto (grupo)" :error="$errors->first('form.tipo_proyecto_id')">
                    <div class="flex items-center gap-2">
                        <x-ui.select wire:model="form.tipo_proyecto_id" class="flex-1">
                            <option value="">— Sin tipo —</option>
                            @foreach ($this->tiposDisponibles as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </x-ui.select>
                        @can('create', App\Models\TiposProyecto::class)
                            <button type="button"
                                    wire:click="abrirModalTipo"
                                    class="inline-flex shrink-0 items-center justify-center rounded-md border border-emerald-300 bg-emerald-50 px-2.5 py-2 text-emerald-700 transition-colors hover:bg-emerald-100"
                                    title="Crear nuevo tipo">
                                <x-heroicon-o-plus class="size-4" />
                            </button>
                        @endcan
                    </div>
                </x-ui.field>

                <x-ui.field label="Responsable principal" :error="$errors->first('form.responsable_principal_id')" class="md:col-span-2">
                    <x-ui.select wire:model="form.responsable_principal_id">
                        <option value="">— Sin asignar —</option>
                        @foreach ($this->responsablesDisponibles as $resp)
                            <option value="{{ $resp->id }}">{{ trim($resp->nombre.' '.$resp->apellidos) }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Fecha inicio" :error="$errors->first('form.fecha_inicio')">
                    <x-ui.input type="date" wire:model="form.fecha_inicio" />
                </x-ui.field>

                <x-ui.field label="Fecha fin" :error="$errors->first('form.fecha_fin')">
                    <x-ui.input type="date" wire:model="form.fecha_fin" />
                </x-ui.field>

                <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')" class="md:col-span-2">
                    <x-ui.textarea wire:model="form.descripcion" rows="3" />
                </x-ui.field>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cerrarModal">Cancelar</x-ui.button>
            <x-ui.button variant="success" type="submit" form="form-proyecto"
                         wire:loading.attr="disabled" icon="heroicon-o-check">
                Guardar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Sub-modal: crear tipo de proyecto al vuelo --}}
    <x-ui.modal :show="$modalTipoAbierto"
        title="Crear nuevo tipo de proyecto"
        close-action="cerrarModalTipo"
        size="sm">

        <form wire:submit="guardarTipo" id="form-tipo-rapido" class="space-y-4">
            <x-ui.field label="Nombre" required :error="$errors->first('tipoForm.nombre')">
                <x-ui.input wire:model="tipoForm.nombre" autofocus
                            placeholder="Ej. Marzo, Mantenimiento, Aluan-2026…" />
            </x-ui.field>

            <x-ui.field label="Descripción (opcional)" :error="$errors->first('tipoForm.descripcion')">
                <x-ui.textarea wire:model="tipoForm.descripcion" rows="2" />
            </x-ui.field>

            <p class="text-xs text-slate-500">
                Al guardar, el tipo se selecciona automáticamente en el proyecto.
            </p>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cerrarModalTipo">Cancelar</x-ui.button>
            <x-ui.button variant="success" type="submit" form="form-tipo-rapido"
                         wire:loading.attr="disabled" icon="heroicon-o-check">
                Crear tipo
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

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
            <x-ui.button variant="ghost" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
