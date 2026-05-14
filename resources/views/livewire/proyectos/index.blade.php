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
                        <option value="activo">Activo</option>
                        <option value="cerrado">Cerrado</option>
                        <option value="archivado">Archivado</option>
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
    <x-ui.data-table :colspan="7" empty="No hay proyectos que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre proyecto
                </x-ui.sortable-header>
                <x-ui.sortable-header column="codigo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Código proyecto
                </x-ui.sortable-header>
                <x-ui.sortable-header>Grupo</x-ui.sortable-header>
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
                        {{ $proyecto->cliente?->nombre ?? '—' }}
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
                                @can('view', $proyecto)
                                    <x-ui.icon-button wire:click="abrirVer({{ $proyecto->id }})"
                                        icon="heroicon-o-eye" variant="neutral" tooltip="Ver detalle" />
                                @endcan
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
        :title="$modoSoloLectura ? 'Ver proyecto' : ($form->id ? 'Editar proyecto' : 'Nuevo proyecto')"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-proyecto" class="space-y-4">
            <div @class([
                'grid gap-4 md:grid-cols-2',
                'max-h-[62vh] overflow-y-auto pr-1' => $form->id,
            ])>
                <x-ui.field label="Código proyecto" :error="$errors->first('form.codigo')"
                            hint="Único por cliente. Se usará en albaranes y reportes.">
                    <x-ui.input wire:model="form.codigo" placeholder="Ej. MAR-A-2026" class="font-mono" :disabled="$modoSoloLectura" autofocus />
                </x-ui.field>

                <x-ui.field label="Nombre proyecto" required :error="$errors->first('form.nombre')">
                    <x-ui.input wire:model="form.nombre" :disabled="$modoSoloLectura" />
                </x-ui.field>

                <x-ui.field label="Grupo" :error="$errors->first('form.tipo_proyecto_id')">
                    <x-ui.select wire:model.live="selectorGrupo" class="flex-1" :disabled="$modoSoloLectura">
                        <option value="">— Sin grupo —</option>
                        @foreach ($this->tiposDisponibles as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                        <option value="__otro__">Otro…</option>
                    </x-ui.select>
                </x-ui.field>

                @if ($selectorGrupo === '__otro__')
                    <x-ui.field label="Grupo nuevo" :error="$errors->first('nuevoGrupoNombre')">
                        <x-ui.input wire:model="nuevoGrupoNombre"
                                    :disabled="$modoSoloLectura"
                                    placeholder="Escribe el nombre del nuevo grupo" />
                    </x-ui.field>
                @endif

                <x-ui.field label="Fecha inicio" :error="$errors->first('form.fecha_inicio')">
                    <x-ui.input type="date" wire:model="form.fecha_inicio" :disabled="$modoSoloLectura" />
                </x-ui.field>

                <x-ui.field label="Fecha fin" :error="$errors->first('form.fecha_fin')">
                    <x-ui.input type="date" wire:model="form.fecha_fin" :disabled="$modoSoloLectura" />
                </x-ui.field>

                <x-ui.field label="Cliente" required :error="$errors->first('form.cliente_id')" class="md:col-span-2">
                    <x-ui.select wire:model="form.cliente_id" :disabled="$modoSoloLectura">
                        <option value="">— Selecciona cliente —</option>
                        @foreach ($this->clientesDisponibles as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Observaciones" :error="$errors->first('form.descripcion')" class="md:col-span-2">
                    <x-ui.textarea wire:model="form.descripcion" rows="3" :disabled="$modoSoloLectura" />
                </x-ui.field>

                @if (! $form->id)
                    <p class="md:col-span-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        Para asignar responsables y trabajadores, primero crea este proyecto y luego edítalo.
                    </p>
                @endif

                @if ($form->id)
                    <x-ui.field label="Trabajador añadir" :error="$errors->first('trabajadorAAgregar')" class="md:col-span-2">
                        <div class="flex items-center gap-2">
                            <x-ui.select wire:key="trabajador-select-{{ $trabajadorSelectKey }}" wire:model="trabajadorAAgregar" class="flex-1" :disabled="$modoSoloLectura">
                                <option value="">— Selecciona trabajador —</option>
                                @foreach ($this->trabajadoresDisponibles as $trab)
                                    <option value="{{ $trab->id }}">{{ trim($trab->nombre.' '.$trab->apellidos) }}</option>
                                @endforeach
                            </x-ui.select>
                            @if (! $modoSoloLectura)
                                <x-ui.button type="button" variant="info" wire:click="agregarTrabajador" icon="heroicon-o-plus">
                                    Añadir
                                </x-ui.button>
                            @endif
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Responsable añadir" :error="$errors->first('responsableAAgregar')" class="md:col-span-2">
                        <div class="flex items-center gap-2">
                            <x-ui.select wire:key="responsable-select-{{ $responsableSelectKey }}" wire:model="responsableAAgregar" class="flex-1" :disabled="$modoSoloLectura">
                                <option value="">— Selecciona responsable —</option>
                                @foreach ($this->responsablesProyectoDisponibles as $resp)
                                    <option value="{{ $resp->id }}">{{ trim($resp->nombre.' '.$resp->apellidos) }}</option>
                                @endforeach
                            </x-ui.select>
                            @if (! $modoSoloLectura)
                                <x-ui.button type="button" variant="info" wire:click="agregarResponsableProyecto" icon="heroicon-o-plus">
                                    Añadir
                                </x-ui.button>
                            @endif
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Material añadir" :error="$errors->first('materialAAgregar')" class="md:col-span-2">
                        <div class="flex items-center gap-2">
                            <x-ui.select wire:key="material-select-{{ $materialSelectKey }}" wire:model="materialAAgregar" class="flex-1" :disabled="$modoSoloLectura">
                                <option value="">— Selecciona material —</option>
                                @foreach ($this->materialesDisponibles as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->descripcion }} | {{ $mat->stock }} {{ $mat->unidad_medida }}</option>
                                @endforeach
                            </x-ui.select>
                            @if (! $modoSoloLectura)
                                <x-ui.button type="button" variant="info" wire:click="agregarMaterialProyecto" icon="heroicon-o-plus">
                                    Añadir
                                </x-ui.button>
                            @endif
                        </div>
                    </x-ui.field>

                    <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
                        <div x-data="{ abierto: false }" class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-100 text-slate-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Trabajador</th>
                                        <th class="px-3 py-2 text-right">
                                            <button type="button"
                                                    x-on:click="abierto = !abierto"
                                                    class="inline-flex items-center gap-1 text-xs text-slate-600 hover:text-slate-900"
                                                    x-bind:title="abierto ? 'Ocultar tabla de trabajadores' : 'Mostrar tabla de trabajadores'">
                                                <span x-text="abierto ? 'Ocultar' : 'Mostrar'"></span>
                                                <x-heroicon-o-chevron-down class="size-3 transition-transform" x-bind:class="abierto ? 'rotate-180' : ''" />
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody x-show="abierto" x-cloak x-transition>
                                    @forelse ($this->trabajadoresProyecto as $trab)
                                        <tr wire:key="trabajador-asignado-{{ $trab->id }}" class="border-t border-slate-100">
                                            <td class="px-3 py-2 text-slate-700">{{ trim($trab->nombre.' '.$trab->apellidos) }}</td>
                                            <td class="px-3 py-2 text-right">
                                                @if (! $modoSoloLectura)
                                                    <button type="button"
                                                            wire:click="quitarTrabajador({{ $trab->id }})"
                                                            class="inline-flex size-7 items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"
                                                            title="Quitar trabajador">
                                                        <x-heroicon-o-x-mark class="size-4" />
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-3 py-3 text-center text-xs text-slate-400">Sin trabajadores asignados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div x-data="{ abierto: false }" class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-100 text-slate-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Responsable</th>
                                        <th class="px-3 py-2 text-right">
                                            <button type="button"
                                                    x-on:click="abierto = !abierto"
                                                    class="inline-flex items-center gap-1 text-xs text-slate-600 hover:text-slate-900"
                                                    x-bind:title="abierto ? 'Ocultar tabla de responsables' : 'Mostrar tabla de responsables'">
                                                <span x-text="abierto ? 'Ocultar' : 'Mostrar'"></span>
                                                <x-heroicon-o-chevron-down class="size-3 transition-transform" x-bind:class="abierto ? 'rotate-180' : ''" />
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody x-show="abierto" x-cloak x-transition>
                                    @forelse ($this->responsablesProyecto as $resp)
                                        <tr wire:key="responsable-asignado-{{ $resp->id }}" class="border-t border-slate-100">
                                            <td class="px-3 py-2 text-slate-700">{{ trim($resp->nombre.' '.$resp->apellidos) }}</td>
                                            <td class="px-3 py-2 text-right">
                                                @if (! $modoSoloLectura)
                                                    <button type="button"
                                                            wire:click="quitarResponsableProyecto({{ $resp->id }})"
                                                            class="inline-flex size-7 items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"
                                                            title="Quitar responsable">
                                                        <x-heroicon-o-x-mark class="size-4" />
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-3 py-3 text-center text-xs text-slate-400">Sin responsables asignados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tabla materiales asignados --}}
                    <div x-data="{ abierto: false }" class="md:col-span-2 overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-3 py-2 text-left">Material</th>
                                    <th class="px-3 py-2 text-left">Unidad</th>
                                    <th class="px-3 py-2 text-left">Stock</th>
                                    <th class="px-3 py-2 text-right">
                                        <button type="button"
                                                x-on:click="abierto = !abierto"
                                                class="inline-flex items-center gap-1 text-xs text-slate-600 hover:text-slate-900"
                                                x-bind:title="abierto ? 'Ocultar tabla de materiales' : 'Mostrar tabla de materiales'">
                                            <span x-text="abierto ? 'Ocultar' : 'Mostrar'"></span>
                                            <x-heroicon-o-chevron-down class="size-3 transition-transform" x-bind:class="abierto ? 'rotate-180' : ''" />
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody x-show="abierto" x-cloak x-transition>
                                @forelse ($this->materialesProyecto as $mat)
                                    <tr wire:key="material-asignado-{{ $mat->id }}" class="border-t border-slate-100">
                                        <td class="px-3 py-2 text-slate-700">{{ $mat->descripcion }}</td>
                                        <td class="px-3 py-2 text-slate-500">{{ $mat->unidad_medida }}</td>
                                        <td class="px-3 py-2 text-slate-500">{{ $mat->stock }}</td>
                                        <td class="px-3 py-2 text-right">
                                            @if (! $modoSoloLectura)
                                                <button type="button"
                                                        wire:click="quitarMaterialProyecto({{ $mat->id }})"
                                                        class="inline-flex size-7 items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"
                                                        title="Quitar material">
                                                    <x-heroicon-o-x-mark class="size-4" />
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-3 text-center text-xs text-slate-400">Sin materiales asignados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cerrarModal">Cancelar</x-ui.button>
            @if (! $modoSoloLectura)
                <x-ui.button variant="success" type="submit" form="form-proyecto"
                             wire:loading.attr="disabled" icon="heroicon-o-check">
                    Guardar
                </x-ui.button>
            @endif
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
