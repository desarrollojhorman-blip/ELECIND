<div>
    <x-ui.page-header title="Clientes" subtitle="Gestión de clientes y sus datos fiscales." />

    {{-- Toolbar: acciones izquierdas + buscador + filtros --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por nombre, CIF, email o población…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Cliente::class)
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

            {{-- Panel desplegable --}}
            <div class="grid gap-3 md:grid-cols-2">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="activas">Activas</option>
                        <option value="inactivas">Inactivas</option>
                        <option value="papelera">En papelera</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Provincia">
                    <x-ui.input
                        wire:key="provincia-{{ $resetKey }}"
                        wire:model.live.debounce.300ms="filtroProvincia"
                        placeholder="Escribe provincia..." />
                </x-ui.field>
            </div>

            {{-- Chips de filtros activos --}}
            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroEstado !== '')
                            <x-ui.filter-chip
                                label="Estado"
                                :value="ucfirst($filtroEstado)"
                                remove-action="quitarFiltroEstado" />
                        @endif
                        @if ($filtroProvincia !== '')
                            <x-ui.filter-chip
                                label="Provincia"
                                :value="$filtroProvincia"
                                remove-action="quitarFiltroProvincia" />
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
    <x-ui.data-table :colspan="8" empty="No hay clientes que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="numero_cliente" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nº cliente
                </x-ui.sortable-header>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header column="cif" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    CIF
                </x-ui.sortable-header>
                <x-ui.sortable-header column="poblacion" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Población
                </x-ui.sortable-header>
                <x-ui.sortable-header column="email" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Email
                </x-ui.sortable-header>
                <x-ui.sortable-header column="telefono" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Teléfono
                </x-ui.sortable-header>
                <x-ui.sortable-header column="activo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($clientes as $cliente)
                <tr wire:key="cliente-{{ $cliente->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-700">{{ $cliente->numero_cliente }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ $cliente->nombre }}</div>
                        @if ($cliente->nombre_comercial)
                            <div class="text-xs text-slate-500">{{ $cliente->nombre_comercial }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $cliente->cif ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">
                        <div>{{ $cliente->poblacion ?? '—' }}</div>
                        @if ($cliente->provincia)
                            <div class="text-xs text-slate-400">{{ $cliente->provincia }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $cliente->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $cliente->telefono ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($cliente->trashed())
                            <x-ui.badge tone="danger" dot>Eliminada</x-ui.badge>
                        @elseif ($cliente->activo)
                            <x-ui.badge tone="success" dot>Activa</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral" dot>Inactiva</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($cliente->trashed())
                                @can('restore', $cliente)
                                    <x-ui.icon-button
                                        wire:click="restaurar({{ $cliente->id }})"
                                        icon="heroicon-o-arrow-uturn-left"
                                        variant="success"
                                        tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('view', $cliente)
                                    <x-ui.icon-button
                                        wire:click="abrirVer({{ $cliente->id }})"
                                        icon="heroicon-o-eye"
                                        variant="neutral"
                                        tooltip="Ver detalle" />
                                @endcan
                                @can('update', $cliente)
                                    <x-ui.icon-button
                                        wire:click="abrirEditar({{ $cliente->id }})"
                                        icon="heroicon-o-pencil-square"
                                        variant="info"
                                        tooltip="Editar" />
                                @endcan
                                @can('delete', $cliente)
                                    <x-ui.icon-button
                                        wire:click="confirmarEliminar({{ $cliente->id }})"
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
        {{ $clientes->links() }}
    </div>

    {{-- Modal crear/editar/ver --}}
    <x-ui.modal
        :show="$modalAbierto"
        :title="$modoSoloLectura ? 'Ver cliente' : ($form->id ? 'Editar cliente' : 'Nuevo cliente')"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-empresa" class="space-y-4" autocomplete="off">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nº cliente" required :error="$errors->first('form.numero_cliente')">
                    <x-ui.input type="number" min="1" step="1" wire:model="form.numero_cliente" :disabled="$modoSoloLectura" autofocus />
                </x-ui.field>

                <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                    <x-ui.input wire:model="form.nombre" :disabled="$modoSoloLectura" />
                </x-ui.field>

                <x-ui.field label="Nombre comercial" :error="$errors->first('form.nombre_comercial')">
                    <x-ui.input wire:model="form.nombre_comercial" :disabled="$modoSoloLectura" />
                </x-ui.field>

                <x-ui.field label="CIF" :error="$errors->first('form.cif')">
                    <x-ui.input wire:model="form.cif" :disabled="$modoSoloLectura" />
                </x-ui.field>

                <x-ui.field label="Teléfono" :error="$errors->first('form.telefono')">
                    <x-ui.input wire:model="form.telefono" :disabled="$modoSoloLectura" />
                </x-ui.field>

                <x-ui.field label="Email" :error="$errors->first('form.email')">
                    <x-ui.input type="email" wire:model="form.email" :disabled="$modoSoloLectura" />
                </x-ui.field>

                <x-ui.field label="Dirección" :error="$errors->first('form.direccion')">
                    <x-ui.input wire:model="form.direccion" :disabled="$modoSoloLectura" autocomplete="off" />
                </x-ui.field>

                <x-ui.field label="Código postal" :error="$errors->first('form.codigo_postal')">
                    <x-ui.input wire:model="form.codigo_postal" :disabled="$modoSoloLectura" autocomplete="off" />
                </x-ui.field>

                <x-ui.field label="Población" :error="$errors->first('form.poblacion')">
                    <x-ui.input wire:model="form.poblacion" :disabled="$modoSoloLectura" autocomplete="off" />
                </x-ui.field>

                <x-ui.field label="Provincia" :error="$errors->first('form.provincia')">
                    <x-ui.input wire:model="form.provincia" :disabled="$modoSoloLectura" autocomplete="off" />
                </x-ui.field>

                <x-ui.field label="Observaciones" class="md:col-span-2" :error="$errors->first('form.observaciones')">
                    <x-ui.textarea wire:model="form.observaciones" :disabled="$modoSoloLectura" rows="3" />
                </x-ui.field>

                <div class="md:col-span-2">
                    <x-ui.checkbox wire:model="form.activo" label="Cliente activo" :disabled="$modoSoloLectura" />
                </div>
            </div>

            @if ($form->id)
                <div class="mt-5 border-t border-slate-200 pt-4">
                    <div class="space-y-4">
                        <div x-data="{ abierto: false }">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Proyectos vinculados</h3>
                                <button
                                    type="button"
                                    x-on:click="abierto = !abierto"
                                    x-bind:aria-label="abierto ? 'Ocultar proyectos vinculados' : 'Mostrar proyectos vinculados'"
                                    x-bind:title="abierto ? 'Ocultar proyectos vinculados' : 'Mostrar proyectos vinculados'"
                                    class="inline-flex items-center justify-center p-0.5 text-slate-500 transition-colors hover:text-slate-900">
                                    <x-dynamic-component :component="'heroicon-o-chevron-down'" x-bind:class="abierto ? 'rotate-180' : ''" class="size-4 transition-transform" />
                                </button>
                            </div>

                            <div x-show="abierto" x-cloak x-transition>
                                @if ($this->proyectosDelClienteActual->isEmpty())
                                    <p class="text-sm text-slate-500">Este cliente no tiene proyectos asociados.</p>
                                @else
                                    <ul class="space-y-1.5">
                                        @foreach ($this->proyectosDelClienteActual as $proyecto)
                                            <li class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2.5">
                                                <div class="text-sm font-semibold leading-5 text-slate-900">{{ $proyecto->nombre }}</div>
                                                <div class="mt-1.5 flex flex-wrap gap-2 text-xs text-slate-600">
                                                    <span class="inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5">
                                                        Código: {{ $proyecto->codigo ?? '—' }}
                                                    </span>
                                                    <span class="inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5">
                                                        Estado: {{ $proyecto->estado ? ucfirst($proyecto->estado) : '—' }}
                                                    </span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        <div x-data="{ abierto: false }">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Usuarios vinculados</h3>
                                <button
                                    type="button"
                                    x-on:click="abierto = !abierto"
                                    x-bind:aria-label="abierto ? 'Ocultar usuarios vinculados' : 'Mostrar usuarios vinculados'"
                                    x-bind:title="abierto ? 'Ocultar usuarios vinculados' : 'Mostrar usuarios vinculados'"
                                    class="inline-flex items-center justify-center p-0.5 text-slate-500 transition-colors hover:text-slate-900">
                                    <x-dynamic-component :component="'heroicon-o-chevron-down'" x-bind:class="abierto ? 'rotate-180' : ''" class="size-4 transition-transform" />
                                </button>
                            </div>

                            <div x-show="abierto" x-cloak x-transition>
                                @if ($this->usuariosDeLosProyectosDelClienteActual->isEmpty())
                                    <p class="text-sm text-slate-500">No hay usuarios vinculados a los proyectos de este cliente.</p>
                                @else
                                    <ul class="space-y-1.5">
                                        @foreach ($this->usuariosDeLosProyectosDelClienteActual as $usuario)
                                            <li class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                                {{ trim($usuario->nombre.' '.$usuario->apellidos) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cerrarModal">
                {{ $modoSoloLectura ? 'Cerrar' : 'Cancelar' }}
            </x-ui.button>
            @if (! $modoSoloLectura)
                <x-ui.button variant="success" type="submit" form="form-empresa" wire:loading.attr="disabled" icon="heroicon-o-check">
                    Guardar
                </x-ui.button>
            @endif
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar cliente"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el cliente a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Podrás restaurarla más tarde desde el filtro <em>«En papelera»</em>.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cancelarEliminar">
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
