<div>
    <x-ui.page-header title="Roles y permisos" subtitle="Gestión de roles del sistema y personalizados con jerarquía por nivel y ámbito." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por nombre de rol…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Role::class)
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>

            <div class="grid gap-3 md:grid-cols-2">
                <x-ui.field label="Tipo">
                    <x-ui.select wire:key="tipo-{{ $resetKey }}" wire:model.live="filtroTipo">
                        <option value="todos">Todos</option>
                        <option value="sistema">Del sistema</option>
                        <option value="personalizados">Personalizados</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Ámbito">
                    <x-ui.select wire:key="ambito-{{ $resetKey }}" wire:model.live="filtroAmbito">
                        <option value="">Todos los ámbitos</option>
                        <option value="web">Web</option>
                        <option value="movil">Móvil</option>
                        <option value="ambos">Ambos</option>
                    </x-ui.select>
                </x-ui.field>
            </div>

            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroTipo !== 'todos')
                            <x-ui.filter-chip label="Tipo" :value="ucfirst($filtroTipo)" remove-action="quitarFiltroTipo" />
                        @endif
                        @if ($filtroAmbito !== null)
                            <x-ui.filter-chip label="Ámbito" :value="ucfirst($filtroAmbito)" remove-action="quitarFiltroAmbito" />
                        @endif
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
    <x-ui.data-table :colspan="6" empty="No hay roles que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header>Nombre</x-ui.sortable-header>
                <x-ui.sortable-header>Tipo</x-ui.sortable-header>
                <x-ui.sortable-header>Ámbito</x-ui.sortable-header>
                <x-ui.sortable-header>Nivel</x-ui.sortable-header>
                <x-ui.sortable-header>Permisos · Usuarios</x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($roles as $rol)
                <tr wire:key="rol-{{ $rol->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-mono text-sm font-medium text-slate-900">{{ $rol->name }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($rol->es_sistema)
                            <x-ui.badge tone="primary">Sistema</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral">Personalizado</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($rol->acceso === 'web')
                            <x-ui.badge tone="info">Web</x-ui.badge>
                        @elseif ($rol->acceso === 'movil')
                            <x-ui.badge tone="success">Móvil</x-ui.badge>
                        @else
                            <x-ui.badge tone="warning">Ambos</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-sm text-slate-600">{{ $rol->nivel }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        <div>{{ $rol->permissions_count }} permisos</div>
                        <div class="text-xs text-slate-400">{{ $rol->users_count }} usuario(s)</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @can('update', $rol)
                                <x-ui.icon-button
                                    wire:click="abrirEditar({{ $rol->id }})"
                                    icon="heroicon-o-pencil-square"
                                    variant="info"
                                    tooltip="Editar" />
                            @endcan
                            @can('delete', $rol)
                                <x-ui.icon-button
                                    wire:click="confirmarEliminar({{ $rol->id }})"
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

    <div class="mt-3">
        {{ $roles->links() }}
    </div>

    {{-- Modal crear/editar --}}
    <x-ui.modal
        :show="$modalAbierto"
        :title="$form->id ? 'Editar rol' : 'Nuevo rol'"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-rol" class="space-y-5">
            {{-- Identidad del rol --}}
            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.field label="Nombre interno" required :error="$errors->first('form.name')">
                    <x-ui.input wire:model="form.name" :disabled="$form->es_sistema" class="font-mono" />
                    @if ($form->es_sistema)
                        <p class="mt-1 text-xs text-slate-400">Los roles del sistema no se pueden renombrar.</p>
                    @else
                        <p class="mt-1 text-xs text-slate-400">Minúsculas, sin espacios. Ej: supervisor_obra</p>
                    @endif
                </x-ui.field>

                <x-ui.field label="Ámbito" required :error="$errors->first('form.acceso')">
                    <x-ui.select wire:model.live="form.acceso">
                        @foreach ($this->ambitosAsignables as $amb)
                            <option value="{{ $amb }}">{{ ucfirst($amb) }}</option>
                        @endforeach
                    </x-ui.select>
                    <p class="mt-1 text-xs text-slate-400">"Ambos" solo lo puede crear el superadmin.</p>
                </x-ui.field>

                <x-ui.field label="Nivel" required :error="$errors->first('form.nivel')">
                    <x-ui.input type="number" min="1" max="100" wire:model="form.nivel" />
                    <p class="mt-1 text-xs text-slate-400">A mayor nivel, más jerarquía. Tope: tu propio nivel.</p>
                </x-ui.field>
            </div>

            {{-- Permisos agrupados por categoría --}}
            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Permisos asignables ({{ count($this->permisosAgrupados) }} categorías)
                </h3>

                @if (count($this->permisosAgrupados) === 0)
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                        No hay permisos disponibles para este ámbito o no tienes ninguno que delegar.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($this->permisosAgrupados as $categoria => $permisos)
                            @php $estadoCat = $this->estadoCategoria($categoria); @endphp
                            <div x-data="{ abierto: false }" class="rounded-md border border-slate-200">
                                <div class="flex items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-3 py-2">
                                    <label class="flex min-w-0 cursor-pointer items-center gap-2">
                                        <input type="checkbox"
                                               wire:key="cat-toggle-{{ $categoria }}-{{ $estadoCat }}"
                                               wire:click="toggleCategoria('{{ $categoria }}')"
                                               data-state="{{ $estadoCat }}"
                                               x-init="$el.checked = $el.dataset.state === 'all'; $el.indeterminate = $el.dataset.state === 'some'"
                                               class="size-4 shrink-0 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                        <p class="truncate text-xs font-semibold uppercase tracking-wide text-slate-600">{{ str_replace('_', ' ', $categoria) }}</p>
                                    </label>

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-slate-400">
                                            @if ($estadoCat === 'all')
                                                Todos seleccionados ({{ count($permisos) }})
                                            @elseif ($estadoCat === 'some')
                                                Algunos seleccionados
                                            @else
                                                Ninguno seleccionado ({{ count($permisos) }} disponibles)
                                            @endif
                                        </span>
                                        <button type="button"
                                                x-on:click="abierto = !abierto"
                                                x-bind:title="abierto ? 'Plegar permisos' : 'Desplegar permisos'"
                                                class="inline-flex items-center justify-center rounded-md p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700">
                                            <x-heroicon-o-chevron-down x-bind:class="abierto ? 'rotate-180' : ''"
                                                                       class="size-4 transition-transform" />
                                        </button>
                                    </div>
                                </div>

                                <div x-show="abierto" x-cloak x-transition class="grid gap-1 p-3 md:grid-cols-2">
                                    @foreach ($permisos as $permiso)
                                        <label class="flex items-start gap-2 rounded p-1.5 text-sm hover:bg-slate-50">
                                            <input type="checkbox"
                                                   wire:model="form.permisos"
                                                   value="{{ $permiso->id }}"
                                                   class="mt-0.5 size-4 shrink-0 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                            <span class="flex-1">
                                                <span class="block font-mono text-xs text-slate-700">{{ $permiso->name }}</span>
                                                @if ($permiso->descripcion)
                                                    <span class="block text-xs text-slate-500">{{ $permiso->descripcion }}</span>
                                                @endif
                                            </span>
                                            <x-ui.badge :tone="$permiso->ambito === 'web' ? 'info' : ($permiso->ambito === 'movil' ? 'success' : 'warning')">
                                                {{ $permiso->ambito }}
                                            </x-ui.badge>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModal">
                Cancelar
            </x-ui.button>
            <x-ui.button variant="info" type="submit" form="form-rol" wire:loading.attr="disabled" icon="heroicon-o-check">
                Guardar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar cambio de ámbito (reset de permisos) --}}
    <x-ui.modal
        :show="$modalCambioAmbitoAbierto"
        title="Cambiar ámbito del rol"
        close-action="cancelarCambioAmbito"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Cambiar el ámbito a <strong>{{ ucfirst($ambitoNuevoPendiente) }}</strong>
                    eliminará los <strong>{{ $cantidadPermisosAfectados }} permisos</strong> actualmente asignados.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Tendrás que asignar los nuevos permisos compatibles con el nuevo ámbito.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarCambioAmbito">
                Cancelar
            </x-ui.button>
            <x-ui.button variant="warning" wire:click="confirmarCambioAmbito" icon="heroicon-o-arrow-path">
                Cambiar ámbito y resetear
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar rol"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción eliminará el rol <strong>permanentemente</strong>.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Los usuarios que lo tuvieran asignado se quedarán sin ese rol.
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
