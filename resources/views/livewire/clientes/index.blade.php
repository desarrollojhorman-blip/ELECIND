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
    <x-ui.page-header title="Clientes" :subtitle="$this->subtituloListado" />

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
                    <x-ui.button as="a" href="{{ route('clientes.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan

                <x-ui.actions-menu label="Acciones" icon="heroicon-o-bars-3">
                    @can('clientes.importar')
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-up-tray"
                                                href="{{ route('clientes.importar') }}" wire:navigate>
                            Importar Excel
                        </x-ui.actions-menu-item>
                    @else
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-up-tray" disabled badge="Sin permiso">
                            Importar Excel
                        </x-ui.actions-menu-item>
                    @endcan
                    <x-ui.actions-menu-divider />
                    @can('clientes.exportar')
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
                    @else
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-down-tray" disabled badge="Sin permiso">
                            Exportar a Excel
                        </x-ui.actions-menu-item>
                    @endcan
                    @can('clientes.exportar')
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
                        <x-ui.actions-menu-item icon="heroicon-o-document-arrow-down" disabled badge="Sin permiso">
                            PDF Vertical
                        </x-ui.actions-menu-item>
                        <x-ui.actions-menu-item icon="heroicon-o-document-arrow-down" disabled badge="Sin permiso">
                            PDF Horizontal
                        </x-ui.actions-menu-item>
                    @endcan
                </x-ui.actions-menu>

                {{-- Modo Papelera: visible solo a quien tenga el permiso
                     `clientes.gestionar_papelera` (por defecto solo superadmin).
                     Sobrescribe el filtro Estado. --}}
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

            {{-- Panel desplegable --}}
            <div class="grid gap-3 md:grid-cols-2">
                <x-ui.field label="Estado">
                    {{-- Dos bloques: directivas @if/@disabled dentro de los atributos
                         de un <x-componente> rompen Blade (memoria
                         "directiva-blade-en-x-componente-rompe"). --}}
                    @if ($modoPapelera)
                        <x-ui.select wire:key="estado-{{ $resetKey }}"
                                     wire:model.live="filtroEstado"
                                     disabled>
                            <option value="">Todos los estados</option>
                            <option value="activas">Activas</option>
                            <option value="inactivas">Inactivas</option>
                        </x-ui.select>
                        <p class="text-xs text-slate-400">Ignorado en modo Papelera.</p>
                    @else
                        <x-ui.select wire:key="estado-{{ $resetKey }}"
                                     wire:model.live="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activas">Activas</option>
                            <option value="inactivas">Inactivas</option>
                        </x-ui.select>
                    @endif
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

    {{-- Banner de modo Papelera (solo superadmin) --}}
    @if ($modoPapelera)
        <div class="mb-3 flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
            <x-heroicon-o-archive-box class="mt-0.5 size-4 shrink-0" />
            <p class="flex-1">
                <strong>Modo Papelera</strong> — viendo
                {{ $this->totalPapelera }}
                {{ $this->totalPapelera === 1 ? 'cliente eliminado' : 'clientes eliminados' }}.
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
        {{ $clientes->links() }}
    </div>
    <x-ui.data-table :colspan="8" empty="No hay clientes que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="codigo_cliente" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Código cliente
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
                    <td class="px-4 py-3 font-mono text-slate-700">
                        @if ($cliente->codigo_cliente !== null)
                            {{ $cliente->codigo_cliente }}
                        @elseif ($cliente->codigo_cliente_anterior !== null)
                            <span class="text-slate-400">{{ $cliente->codigo_cliente_anterior }} <span class="text-xs">(archivado)</span></span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
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
                                        wire:loading.attr="disabled"
                                        wire:target="restaurar({{ $cliente->id }})"
                                        variant="success"
                                        tooltip="Restaurar">
                                        <span wire:loading.remove wire:target="restaurar({{ $cliente->id }})">
                                            <x-heroicon-o-arrow-uturn-left class="size-4" />
                                        </span>
                                        <svg wire:loading wire:target="restaurar({{ $cliente->id }})" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                    </x-ui.icon-button>
                                @endcan
                            @else
                                @can('view', $cliente)
                                    <x-ui.icon-button
                                        as="a"
                                        href="{{ route('clientes.ver', $cliente) }}"
                                        wire:navigate
                                        icon="heroicon-o-eye"
                                        variant="neutral"
                                        tooltip="Ver detalle" />
                                @endcan
                                @can('update', $cliente)
                                    <x-ui.icon-button
                                        as="a"
                                        href="{{ route('clientes.editar', $cliente) }}"
                                        wire:navigate.fresh
                                        icon="heroicon-o-pencil-square"
                                        variant="info"
                                        tooltip="Editar" />
                                @endcan
                                @can('clientes.eliminar')
                                    {{-- @can('clientes.eliminar') en lugar de @can('delete', $cliente):
                                         el botón se muestra a todo el que tenga permiso.
                                         El chequeo de dependencias va al pulsar (Policy + Gate::inspect). --}}
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
                    ¿Eliminar este cliente?
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
