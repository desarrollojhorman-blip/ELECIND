<div>
    <x-ui.page-header title="Conceptos" subtitle="Catálogo global de conceptos asignables a proyectos." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por nombre o descripción…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Concepto::class)
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
                </x-ui.actions-menu>
            </x-slot:leftActions>

            <div class="grid gap-3 md:grid-cols-2">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="activos">Activos</option>
                        <option value="inactivos">Inactivos</option>
                        <option value="todos">Todos</option>
                        <option value="papelera">En papelera</option>
                    </x-ui.select>
                </x-ui.field>
            </div>

            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        <x-ui.filter-chip label="Estado" :value="ucfirst($filtroEstado)" remove-action="quitarFiltroEstado" />
                        <button type="button"
                                wire:click="limpiarFiltros"
                                class="text-xs text-slate-500 underline hover:text-slate-700">
                            Limpiar todos
                        </button>
                    </div>
                </x-slot:chips>
            @endif
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla --}}
    <x-ui.data-table :colspan="5" empty="No hay conceptos que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header>Descripción</x-ui.sortable-header>
                <x-ui.sortable-header>Proyectos</x-ui.sortable-header>
                <x-ui.sortable-header column="activo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($conceptos as $concepto)
                <tr wire:key="concepto-{{ $concepto->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ $concepto->nombre }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        @if ($concepto->descripcion)
                            <div class="line-clamp-2 text-sm">{{ $concepto->descripcion }}</div>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        <x-ui.badge tone="neutral">{{ $concepto->proyectos_count }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        @if ($concepto->trashed())
                            <x-ui.badge tone="danger" dot>Eliminado</x-ui.badge>
                        @elseif ($concepto->activo)
                            <x-ui.badge tone="success" dot>Activo</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral" dot>Inactivo</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($concepto->trashed())
                                @can('restore', $concepto)
                                    <x-ui.icon-button
                                        wire:click="restaurar({{ $concepto->id }})"
                                        icon="heroicon-o-arrow-uturn-left"
                                        variant="success"
                                        tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('update', $concepto)
                                    <x-ui.icon-button
                                        wire:click="abrirEditar({{ $concepto->id }})"
                                        icon="heroicon-o-pencil-square"
                                        variant="info"
                                        tooltip="Editar" />
                                @endcan
                                @can('delete', $concepto)
                                    <x-ui.icon-button
                                        wire:click="confirmarEliminar({{ $concepto->id }})"
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

    <div class="mt-3">
        {{ $conceptos->links() }}
    </div>

    {{-- Modal crear/editar --}}
    <x-ui.modal
        :show="$modalAbierto"
        :title="$form->id ? 'Editar concepto' : 'Nuevo concepto'"
        close-action="cerrarModal"
        size="md">

        <form wire:submit="guardar" id="form-concepto" class="space-y-4">
            <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                <x-ui.input wire:model="form.nombre" autofocus />
            </x-ui.field>

            <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')">
                <x-ui.textarea wire:model="form.descripcion" rows="3" />
            </x-ui.field>

            <x-ui.checkbox wire:model="form.activo" label="Concepto activo" />
        </form>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModal">
                Cancelar
            </x-ui.button>
            <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="submit" form="form-concepto" wire:loading.attr="disabled">
                Guardar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar concepto"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el concepto a la <strong>papelera</strong>.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Las asignaciones a proyectos se mantienen pero el concepto dejará de aparecer en los selectores.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">
                Cancelar
            </x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})"
                         icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
