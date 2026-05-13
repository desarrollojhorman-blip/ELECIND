<div>
    <x-ui.page-header title="Materiales" subtitle="Catálogo de materiales con control de stock por lotes." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por nombre, código, grupo o descripción…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Material::class)
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

            <div class="grid gap-3 md:grid-cols-2">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="todos">Todos</option>
                        <option value="activos">Activos</option>
                        <option value="inactivos">Inactivos</option>
                        <option value="papelera">En papelera</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Grupo">
                    <x-ui.select wire:key="grupo-{{ $resetKey }}" wire:model.live="filtroGrupo">
                        <option value="">Todos los grupos</option>
                        @foreach ($this->gruposDisponibles as $grupo)
                            <option value="{{ $grupo }}">{{ $grupo }}</option>
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
                        @if ($filtroGrupo !== '')
                            <x-ui.filter-chip label="Grupo" :value="$filtroGrupo" remove-action="quitarFiltroGrupo" />
                        @endif
                    </div>
                </x-slot:chips>
            @endif
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla --}}
    <x-ui.data-table :colspan="7" empty="No hay materiales que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="codigo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Código
                </x-ui.sortable-header>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header column="grupo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Grupo
                </x-ui.sortable-header>
                <x-ui.sortable-header>Stock total</x-ui.sortable-header>
                <x-ui.sortable-header>Stock mínimo</x-ui.sortable-header>
                <x-ui.sortable-header column="activo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($materiales as $material)
                @php
                    $stockTotal = (float) ($material->stock_total ?? 0);
                    $stockMinimo = (float) $material->stock_minimo;
                    $stockBajo = $stockMinimo > 0 && $stockTotal <= $stockMinimo;
                @endphp
                <tr wire:key="mat-{{ $material->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">
                        {{ $material->codigo ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ $material->nombre }}</div>
                        @if ($material->descripcion)
                            <div class="text-xs text-slate-500 line-clamp-1">{{ $material->descripcion }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($material->grupo)
                            <x-ui.badge tone="primary">{{ $material->grupo }}</x-ui.badge>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span @class([
                                'text-sm font-semibold',
                                'text-amber-700' => $stockBajo,
                                'text-slate-700' => ! $stockBajo,
                            ])>
                                {{ rtrim(rtrim(number_format($stockTotal, 2, ',', ''), '0'), ',') }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $material->unidad_medida }}</span>
                            @if ($stockBajo)
                                <span title="Stock bajo el mínimo">
                                    <x-heroicon-m-exclamation-triangle class="size-4 text-amber-600" />
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ rtrim(rtrim(number_format($stockMinimo, 2, ',', ''), '0'), ',') }}
                        <span class="text-xs text-slate-400">{{ $material->unidad_medida }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if ($material->trashed())
                            <x-ui.badge tone="danger" dot>Eliminado</x-ui.badge>
                        @elseif ($material->activo)
                            <x-ui.badge tone="success" dot>Activo</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral" dot>Inactivo</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($material->trashed())
                                @can('restore', $material)
                                    <x-ui.icon-button wire:click="restaurar({{ $material->id }})"
                                        icon="heroicon-o-arrow-uturn-left" variant="success" tooltip="Restaurar" />
                                @endcan
                            @else
                                <a href="{{ route('materiales.lotes', $material) }}"
                                   class="inline-flex size-8 items-center justify-center rounded-md text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900"
                                   title="Ver lotes">
                                    <x-heroicon-o-archive-box class="size-4" />
                                </a>
                                @can('update', $material)
                                    <x-ui.icon-button wire:click="abrirEditar({{ $material->id }})"
                                        icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                @endcan
                                @can('delete', $material)
                                    <x-ui.icon-button wire:click="confirmarEliminar({{ $material->id }})"
                                        icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    <div class="mt-3">{{ $materiales->links() }}</div>

    {{-- Modal crear/editar material --}}
    <x-ui.modal :show="$modalAbierto"
        :title="$form->id ? 'Editar material' : 'Nuevo material'"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-material" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Código" :error="$errors->first('form.codigo')"
                            hint="Único en todo el catálogo. Opcional.">
                    <x-ui.input wire:model="form.codigo" placeholder="Ej. MAT-1234" class="font-mono" />
                </x-ui.field>

                <x-ui.field label="Grupo" :error="$errors->first('form.grupo')"
                            hint="Familia o categoría (Cableado, Mecanismos, …).">
                    <x-ui.input wire:model="form.grupo" placeholder="Ej. Cableado" />
                </x-ui.field>

                <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')" class="md:col-span-2">
                    <x-ui.input wire:model="form.nombre" autofocus placeholder="Ej. Cable H07V-K 2,5mm² negro" />
                </x-ui.field>

                <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')" class="md:col-span-2">
                    <x-ui.textarea wire:model="form.descripcion" rows="2" />
                </x-ui.field>

                <x-ui.field label="Unidad de medida" required :error="$errors->first('form.unidad_medida')"
                            hint="Ej. ud, m, kg, l…">
                    <x-ui.input wire:model="form.unidad_medida" />
                </x-ui.field>

                <x-ui.field label="Stock mínimo" required :error="$errors->first('form.stock_minimo')"
                            hint="Por debajo se considera stock bajo.">
                    <x-ui.input type="number" step="0.01" min="0" wire:model="form.stock_minimo" />
                </x-ui.field>

                <div class="md:col-span-2 space-y-2">
                    <x-ui.checkbox wire:model="form.notificar_stock_bajo" label="Notificar cuando el stock baje del mínimo" />
                    <x-ui.checkbox wire:model="form.activo" label="Material activo" />
                </div>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cerrarModal">Cancelar</x-ui.button>
            <x-ui.button variant="success" type="submit" form="form-material"
                         wire:loading.attr="disabled" icon="heroicon-o-check">
                Guardar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal :show="$confirmarEliminarId !== null"
        title="Eliminar material"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el material a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Sus lotes se mantienen intactos. Albaranes asociados conservarán la referencia.
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
