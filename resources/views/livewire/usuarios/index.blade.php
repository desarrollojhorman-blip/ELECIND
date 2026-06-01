<div x-data="{ mostrarDescarga: false }"
     x-on:descargar.window="
        const a = document.createElement('a');
        a.href = $event.detail.url;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        mostrarDescarga = true;
     ">
    <x-ui.page-header title="Usuarios"
                       :subtitle="$this->subtituloListado" />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por usuario, nombre, email o DNI…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\User::class)
                    <x-ui.button as="a" href="{{ route('usuarios.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan

                <x-ui.actions-menu label="Acciones" icon="heroicon-o-bars-3">
                    @can('usuarios.importar')
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-up-tray"
                                                href="{{ route('usuarios.importar') }}" wire:navigate>
                            Importar Excel
                        </x-ui.actions-menu-item>
                    @else
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-up-tray" disabled badge="Sin permiso">
                            Importar Excel
                        </x-ui.actions-menu-item>
                    @endcan
                    <x-ui.actions-menu-divider />
                    @can('usuarios.exportar')
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

                {{-- Modo Papelera: visible solo a quien tenga `usuarios.gestionar_papelera`. --}}
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

            <div class="grid gap-3 md:grid-cols-4">
                <x-ui.field label="Estado">
                    @if ($modoPapelera)
                        <x-ui.select wire:key="estado-{{ $resetKey }}"
                                     wire:model.live="filtroEstado"
                                     disabled>
                            <option value="activos">Activos</option>
                            <option value="inactivos">Inactivos</option>
                            <option value="todos">Todos</option>
                        </x-ui.select>
                        <p class="text-xs text-slate-400">Ignorado en modo Papelera.</p>
                    @else
                        <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                            <option value="activos">Activos</option>
                            <option value="inactivos">Inactivos</option>
                            <option value="todos">Todos</option>
                        </x-ui.select>
                    @endif
                </x-ui.field>

                <x-ui.field label="Tipo">
                    <x-ui.select wire:key="tipo-{{ $resetKey }}" wire:model.live="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="interno">Interno (Elecind)</option>
                        <option value="externo">Externo (cliente)</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Rol">
                    <x-ui.select wire:key="rol-{{ $resetKey }}" wire:model.live="filtroRol">
                        <option value="">Todos los roles</option>
                        @foreach ($this->rolesDisponibles as $rol)
                            <option value="{{ $rol->name }}">{{ ucfirst($rol->name) }} (nivel {{ $rol->nivel }})</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Cliente">
                    <x-ui.input
                        wire:key="cliente-{{ $resetKey }}"
                        wire:model.live.debounce.300ms="filtroEmpresaCliente"
                        placeholder="Escribe nombre del cliente..." />
                </x-ui.field>
            </div>

            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroEstado !== 'activos')
                            <x-ui.filter-chip label="Estado" :value="ucfirst($filtroEstado)" remove-action="quitarFiltroEstado" />
                        @endif
                        @if ($filtroTipo !== null)
                            <x-ui.filter-chip label="Tipo" :value="ucfirst($filtroTipo)" remove-action="quitarFiltroTipo" />
                        @endif
                        @if ($filtroRol !== null)
                            <x-ui.filter-chip label="Rol" :value="ucfirst($filtroRol)" remove-action="quitarFiltroRol" />
                        @endif
                        @if (trim($filtroEmpresaCliente) !== '')
                            <x-ui.filter-chip label="Empresa"
                            :value="$filtroEmpresaCliente"
                                remove-action="quitarFiltroEmpresaCliente" />
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

    {{-- Banner de modo Papelera (solo superadmin / permiso usuarios.gestionar_papelera) --}}
    @if ($modoPapelera)
        <div class="mb-3 flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
            <x-heroicon-o-archive-box class="mt-0.5 size-4 shrink-0" />
            <p class="flex-1">
                <strong>Modo Papelera</strong> — viendo
                {{ $this->totalPapelera }}
                {{ $this->totalPapelera === 1 ? 'usuario eliminado' : 'usuarios eliminados' }}.
                Vista exclusiva del superadmin.
            </p>
            <button type="button"
                    wire:click="$set('verPapelera', false)"
                    class="text-xs font-semibold text-amber-700 underline hover:text-amber-900">
                Salir
            </button>
        </div>
    @endif

    {{-- Tabla --}}
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
        {{ $usuarios->links() }}
    </div>
    <x-ui.data-table :colspan="8" empty="No hay usuarios que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="id" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    ID
                </x-ui.sortable-header>
                <x-ui.sortable-header column="username" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Usuario
                </x-ui.sortable-header>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header column="rol" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Rol
                </x-ui.sortable-header>
                <x-ui.sortable-header column="email" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Email
                </x-ui.sortable-header>
                <x-ui.sortable-header column="tipo_usuario" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Tipo
                </x-ui.sortable-header>
                <x-ui.sortable-header>Estado</x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($usuarios as $usuario)
                <tr wire:key="usuario-{{ $usuario->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-slate-500">{{ $usuario->id }}</td>
                    <td class="px-4 py-3">
                        <div class="font-mono text-sm font-medium text-slate-900">{{ $usuario->username }}</div>
                        @php $rolPrincipal = $usuario->roles->first(); @endphp
                        @if ($rolPrincipal)
                            <div class="text-xs text-slate-400">Acceso: {{ ucfirst($rolPrincipal->acceso) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ trim($usuario->nombre.' '.$usuario->apellidos) }}</div>
                        @if ($usuario->telefono)
                            <div class="text-xs text-slate-500">{{ $usuario->telefono }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php $rol = $usuario->roles->first(); @endphp
                        @if ($rol)
                            <x-ui.badge tone="brand">{{ ucfirst($rol->name) }}</x-ui.badge>
                        @else
                            <span class="text-xs text-slate-400">Sin rol</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        <div class="text-sm">{{ $usuario->email ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        <div>{{ ucfirst($usuario->tipo_usuario) }}</div>
                        @if ($usuario->cliente)
                            <div class="text-xs text-slate-400">
                                <span class="font-mono">{{ $usuario->cliente->codigo_cliente }}</span>
                                · {{ $usuario->cliente->nombre }}
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($usuario->trashed())
                            <x-ui.badge tone="danger" dot>Eliminado</x-ui.badge>
                        @elseif ($usuario->activo)
                            <x-ui.badge tone="success" dot>Activo</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral" dot>Inactivo</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($usuario->trashed())
                                @can('restore', $usuario)
                                    <x-ui.icon-button
                                        wire:click="restaurar({{ $usuario->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="restaurar({{ $usuario->id }})"
                                        variant="success"
                                        tooltip="Restaurar">
                                        <span wire:loading.remove wire:target="restaurar({{ $usuario->id }})">
                                            <x-heroicon-o-arrow-uturn-left class="size-4" />
                                        </span>
                                        <svg wire:loading wire:target="restaurar({{ $usuario->id }})" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                    </x-ui.icon-button>
                                @endcan
                            @else
                                @can('view', $usuario)
                                    <x-ui.icon-button
                                        as="a"
                                        href="{{ route('usuarios.ver', $usuario) }}"
                                        wire:navigate
                                        icon="heroicon-o-eye"
                                        variant="neutral"
                                        tooltip="Ver detalle" />
                                @endcan
                                @can('update', $usuario)
                                    <x-ui.icon-button
                                        as="a"
                                        href="{{ route('usuarios.editar', $usuario) }}"
                                        wire:navigate
                                        icon="heroicon-o-pencil-square"
                                        variant="info"
                                        tooltip="Editar" />
                                @endcan
                                @can('usuarios.eliminar')
                                    {{-- @can('usuarios.eliminar') (no @can('delete',$usuario)):
                                         visible para quien tenga el permiso; el bloqueo
                                         por dependencias se gestiona al pulsar. --}}
                                    <x-ui.icon-button
                                        wire:click="confirmarEliminar({{ $usuario->id }})"
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

    {{-- Los modales antiguos de creación/edición rápida y duplicados se eliminaron:
         el flujo de crear/editar usuario se hace en las páginas dedicadas
         (Usuarios\Crear y Usuarios\Editar). --}}

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar usuario"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    ¿Eliminar este usuario?
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Esta acción no se puede deshacer.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">
                Cancelar
            </x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})"
                         wire:loading.attr="disabled"
                         wire:target="eliminar">
                <x-heroicon-o-trash wire:loading.remove wire:target="eliminar" class="size-4" />
                <svg wire:loading wire:target="eliminar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="eliminar">Eliminar</span>
                <span wire:loading wire:target="eliminar">Eliminando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal informativo: la eliminación está bloqueada por dependencias --}}
    <x-ui.modal
        :show="$bloqueadoEliminarMensaje !== null"
        title="No se puede eliminar"
        close-action="cerrarBloqueo"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    {{ $bloqueadoEliminarMensaje }}
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    Elimina o reasigna primero esos elementos.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarBloqueo">
                Entendido
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal de descarga: avisa al usuario que el archivo se está bajando.
         Vive en Alpine (no Livewire) porque es UX puramente local. --}}
    <div x-show="mostrarDescarga"
         x-cloak
         x-transition.opacity
         x-on:keydown.escape.window="mostrarDescarga = false"
         class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4">
        <div class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-slate-900/5"
             @click.outside="mostrarDescarga = false">
            <div class="flex items-center justify-between rounded-t-lg border-b border-accent-200 bg-accent-100 px-5 py-3">
                <h3 class="text-base font-semibold text-primary-800">Descargando archivo</h3>
                <button type="button"
                        @click="mostrarDescarga = false"
                        class="rounded p-1 text-slate-500 transition-colors hover:bg-white/60 hover:text-slate-700">
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>

            <div class="px-5 py-6 text-center">
                <x-heroicon-o-arrow-down-tray class="mx-auto mb-3 size-10 text-primary-600" />
                <p class="text-sm text-slate-700">
                    Tu archivo se está descargando. Espera unos segundos y revisa la barra de descargas del navegador.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 rounded-b-lg border-t border-slate-200 bg-slate-50 px-5 py-3">
                <button type="button"
                        @click="mostrarDescarga = false"
                        class="rounded-md bg-primary-700 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-primary-800">
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>
