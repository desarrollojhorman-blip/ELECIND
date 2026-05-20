<div>
    <x-ui.page-header title="Grupo proyectos"
                      subtitle="Gestiona grupos de proyectos y asigna proyectos existentes a cada grupo." />

    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por grupo o descripcion..."
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle=""
            :panel-open="false"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\TiposProyecto::class)
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>

            <div class="grid gap-3 md:grid-cols-3">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="todos">Todos</option>
                        <option value="activos">Activos</option>
                        <option value="desactivados">Desactivados</option>
                        <option value="papelera">En papelera</option>
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
                    </div>
                </x-slot:chips>
            @endif
        </x-ui.search-and-filter>
    </div>

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
        {{ $grupos->links() }}
    </div>
    <x-ui.data-table :colspan="5" empty="No hay grupos que coincidan con la búsqueda o filtros.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Grupo
                </x-ui.sortable-header>
                <x-ui.sortable-header column="descripcion" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Descripcion
                </x-ui.sortable-header>
                <x-ui.sortable-header align="center">Proyectos</x-ui.sortable-header>
                <x-ui.sortable-header column="activo" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="center">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($grupos as $grupo)
                <tr wire:key="grp-{{ $grupo->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $grupo->nombre }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $grupo->descripcion ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-slate-700">{{ $grupo->proyectos_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($grupo->trashed())
                            <x-ui.badge tone="danger" dot>Eliminado</x-ui.badge>
                        @elseif ($grupo->activo)
                            <x-ui.badge tone="success" dot>Activo</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral" dot>Desactivado</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($grupo->trashed())
                                @can('restore', $grupo)
                                    <x-ui.icon-button wire:click="restaurar({{ $grupo->id }})"
                                        icon="heroicon-o-arrow-uturn-left" variant="success" tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('view', $grupo)
                                    <x-ui.icon-button wire:click="abrirVer({{ $grupo->id }})"
                                        icon="heroicon-o-eye" variant="secondary" tooltip="Ver" />
                                @endcan
                                @can('update', $grupo)
                                    <x-ui.icon-button wire:click="abrirEditar({{ $grupo->id }})"
                                        icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                @endcan
                                @can('delete', $grupo)
                                    <x-ui.icon-button wire:click="confirmarEliminar({{ $grupo->id }})"
                                        icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    <x-ui.modal :show="$modalAbierto"
        :title="$modoSoloLectura ? 'Ver grupo de proyectos' : ($form->id ? 'Editar grupo de proyectos' : 'Nuevo grupo de proyectos')"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-grupo-proyecto" class="space-y-4">
            <x-ui.field label="Grupo" required :error="$errors->first('form.nombre')">
                <x-ui.input wire:model="form.nombre" autofocus
                            placeholder="Ej. Marzo 2026"
                            :disabled="$modoSoloLectura" />
            </x-ui.field>

            <x-ui.field label="Descripcion" :error="$errors->first('form.descripcion')">
                <x-ui.input wire:model="form.descripcion"
                            placeholder="Opcional"
                            :disabled="$modoSoloLectura" />
            </x-ui.field>

            <x-ui.field label="Estado" :error="$errors->first('form.activo')">
                <x-ui.select wire:model="form.activo" :disabled="$modoSoloLectura">
                    <option value="1">Activo</option>
                    <option value="0">Desactivado</option>
                </x-ui.select>
            </x-ui.field>
        </form>

        @if ($form->id)
            <div class="mt-5 border-t border-slate-200 pt-4">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Proyectos en este grupo
                </h3>

                @if (! $modoSoloLectura && auth()->user()?->can('grupos_proyecto.modificar'))
                    <div class="mb-3 rounded-md border border-dashed border-slate-300 bg-slate-50 p-3">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">
                            Añadir proyecto sin grupo
                        </label>
                        <div class="flex items-stretch gap-2">
                            <div class="min-w-0 flex-1">
                                <x-ui.select wire:model="proyectoAAsignar" class="w-full">
                                    <option value="">— Selecciona un proyecto —</option>
                                    @foreach ($this->proyectosSinGrupo as $sinGrupo)
                                        <option value="{{ $sinGrupo->id }}">
                                            {{ $sinGrupo->nombre }}
                                            @if ($sinGrupo->codigo)
                                                · {{ $sinGrupo->codigo }}
                                            @endif
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <x-ui.button variant="success" wire:click="agregarProyectoAGrupo"
                                         icon="heroicon-o-plus" class="shrink-0 whitespace-nowrap">
                                Añadir
                            </x-ui.button>
                        </div>
                        @if ($this->proyectosSinGrupo->isEmpty())
                            <p class="mt-2 text-xs text-slate-500">
                                No hay proyectos sin grupo disponibles.
                            </p>
                        @endif
                    </div>
                @endif

                <div class="mt-3" x-data="{ abierto: false }">
                    <div class="mb-2 flex items-center justify-between">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Proyectos ya asignados
                            <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-700">
                                {{ $this->proyectosDelGrupoActual->count() }}
                            </span>
                        </h4>
                        <button type="button" x-on:click="abierto = !abierto"
                                class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                x-bind:title="abierto ? 'Plegar lista' : 'Desplegar lista'">
                            <x-heroicon-o-chevron-down x-bind:class="abierto ? 'rotate-180' : ''"
                                                       class="size-4 transition-transform" />
                        </button>
                    </div>

                    <div x-show="abierto" x-cloak x-transition>
                        @if ($this->proyectosDelGrupoActual->isNotEmpty())
                            <div class="overflow-hidden rounded-md border border-slate-200">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2 text-center">Proyecto</th>
                                            <th class="px-3 py-2 text-center">Codigo</th>
                                            <th class="px-3 py-2 text-center">Cliente</th>
                                            <th class="px-3 py-2 text-center">Estado</th>
                                            @if (! $modoSoloLectura)
                                                <th class="px-3 py-2 text-center"></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($this->proyectosDelGrupoActual as $proyecto)
                                            <tr wire:key="grp-proy-{{ $proyecto->id }}" class="hover:bg-slate-50">
                                                <td class="px-3 py-2 text-slate-800">{{ $proyecto->nombre }}</td>
                                                <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $proyecto->codigo ?? '—' }}</td>
                                                <td class="px-3 py-2 text-slate-600">{{ $proyecto->cliente?->nombre ?? '—' }}</td>
                                                <td class="px-3 py-2 text-slate-600">{{ ucfirst($proyecto->estado) }}</td>
                                                @if (! $modoSoloLectura)
                                                    <td class="px-3 py-2 text-right">
                                                        <button type="button"
                                                                wire:click="quitarProyectoDeGrupo({{ $proyecto->id }})"
                                                                class="text-slate-400 hover:text-red-500"
                                                                title="Quitar del grupo">
                                                            <x-heroicon-o-trash class="size-4" />
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="rounded-md border border-dashed border-slate-200 bg-slate-50 px-3 py-6 text-center text-sm text-slate-500">
                                Este grupo aun no tiene proyectos asignados.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <x-slot:footer>
            @if (!$modoSoloLectura)
                <x-ui.button variant="neutral" wire:click="cerrarModal">Cancelar</x-ui.button>
                <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="submit" form="form-grupo-proyecto"
                             wire:loading.attr="disabled">
                    Guardar
                </x-ui.button>
            @endif
        </x-slot:footer>
    </x-ui.modal>

    <x-ui.modal :show="$confirmarEliminarId !== null"
        title="Eliminar grupo de proyectos"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta accion enviara el grupo a la <strong>papelera</strong> (eliminacion logica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Los proyectos asignados quedaran <strong>sin grupo</strong>, pero no se borran.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
