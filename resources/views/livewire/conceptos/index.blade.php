<div x-data
     x-on:descargar.window="
        const a = document.createElement('a');
        a.href = $event.detail.url;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
     ">
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
                    @can('conceptos.importar')
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-up-tray"
                                                href="{{ route('conceptos.importar') }}" wire:navigate>
                            Importar Excel
                        </x-ui.actions-menu-item>
                    @else
                        <x-ui.actions-menu-item icon="heroicon-o-arrow-up-tray" disabled badge="Sin permiso">
                            Importar Excel
                        </x-ui.actions-menu-item>
                    @endcan
                    <x-ui.actions-menu-divider />
                    @can('conceptos.exportar')
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

                {{-- Toggle Papelera: solo visible con `conceptos.gestionar_papelera`. --}}
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

            <div class="grid gap-3 md:grid-cols-2">
                <x-ui.field label="Estado">
                    {{-- Dos bloques: directivas Blade dentro de los atributos
                         de un <x-componente> rompen el parser. --}}
                    @if ($verPapelera && $this->puedeVerPapelera)
                        <x-ui.select wire:key="estado-{{ $resetKey }}"
                                     wire:model.live="filtroEstado"
                                     disabled>
                            <option value="">Todos</option>
                            <option value="activos">Activos</option>
                            <option value="inactivos">Inactivos</option>
                        </x-ui.select>
                        <p class="text-xs text-slate-400">Ignorado en modo Papelera.</p>
                    @else
                        <x-ui.select wire:key="estado-{{ $resetKey }}"
                                     wire:model.live="filtroEstado">
                            <option value="">Todos</option>
                            <option value="activos">Activos</option>
                            <option value="inactivos">Inactivos</option>
                        </x-ui.select>
                    @endif
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
        {{ $conceptos->links() }}
    </div>
    <x-ui.data-table :colspan="7" empty="No hay conceptos que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="id" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    ID
                </x-ui.sortable-header>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header>Descripción</x-ui.sortable-header>
                <x-ui.sortable-header>Proyectos</x-ui.sortable-header>
                <x-ui.sortable-header>Albaranes</x-ui.sortable-header>
                <x-ui.sortable-header column="activo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($conceptos as $concepto)
                <tr wire:key="concepto-{{ $concepto->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $concepto->id }}</td>
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
                    <td class="px-4 py-3 text-slate-600">
                        <x-ui.badge tone="neutral">{{ $concepto->albaranes_count }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        @if ($concepto->trashed())
                            <x-ui.badge tone="danger" dot>Eliminado</x-ui.badge>
                        @else
                            @can('update', $concepto)
                                <button type="button"
                                        wire:click="toggleActivo({{ $concepto->id }})"
                                        class="group inline-flex items-center"
                                        title="{{ $concepto->activo ? 'Pulsa para desactivar (saldrá de los selectores)' : 'Pulsa para activar' }}">
                                    @if ($concepto->activo)
                                        <x-ui.badge tone="success" dot class="cursor-pointer transition-opacity group-hover:opacity-70">Activo</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="neutral" dot class="cursor-pointer transition-opacity group-hover:opacity-70">Inactivo</x-ui.badge>
                                    @endif
                                </button>
                            @else
                                @if ($concepto->activo)
                                    <x-ui.badge tone="success" dot>Activo</x-ui.badge>
                                @else
                                    <x-ui.badge tone="neutral" dot>Inactivo</x-ui.badge>
                                @endif
                            @endcan
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($concepto->trashed())
                                @can('view', $concepto)
                                    <x-ui.icon-button
                                        wire:click="abrirVer({{ $concepto->id }})"
                                        icon="heroicon-o-eye"
                                        variant="ghost"
                                        tooltip="Ver" />
                                @endcan
                                @can('restore', $concepto)
                                    <x-ui.icon-button
                                        wire:click="restaurar({{ $concepto->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="restaurar({{ $concepto->id }})"
                                        variant="success"
                                        tooltip="Restaurar">
                                        <span wire:loading.remove wire:target="restaurar({{ $concepto->id }})">
                                            <x-heroicon-o-arrow-uturn-left class="size-4" />
                                        </span>
                                        <svg wire:loading wire:target="restaurar({{ $concepto->id }})" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                    </x-ui.icon-button>
                                @endcan
                            @else
                                @can('view', $concepto)
                                    <x-ui.icon-button
                                        wire:click="abrirVer({{ $concepto->id }})"
                                        icon="heroicon-o-eye"
                                        variant="ghost"
                                        tooltip="Ver" />
                                @endcan
                                @can('update', $concepto)
                                    <x-ui.icon-button
                                        wire:click="abrirEditar({{ $concepto->id }})"
                                        icon="heroicon-o-pencil-square"
                                        variant="info"
                                        tooltip="Editar" />
                                @endcan
                                @can('conceptos.eliminar')
                                    {{-- @can('conceptos.eliminar') en lugar de @can('delete'): el botón se muestra
                                         a todo el que tenga permiso. El chequeo de dependencias va al pulsar
                                         (Policy + Gate::inspect). --}}
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

    {{-- Modal crear/editar/ver --}}
    <x-ui.modal
        :show="$modalAbierto"
        :title="$soloLectura ? 'Ver concepto' : ($form->id ? 'Editar concepto' : 'Nuevo concepto')"
        close-action="cerrarModal"
        :size="$form->id ? 'lg' : 'md'">

        <form wire:submit="guardar" id="form-concepto" class="space-y-4">
            <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                @if ($soloLectura)
                    <x-ui.input wire:model="form.nombre" readonly />
                @else
                    <x-ui.input wire:model="form.nombre" autofocus />
                @endif
            </x-ui.field>

            <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')">
                @if ($soloLectura)
                    <x-ui.textarea wire:model="form.descripcion" rows="3" readonly />
                @else
                    <x-ui.textarea wire:model="form.descripcion" rows="3" />
                @endif
            </x-ui.field>

            @if ($soloLectura)
                <x-ui.checkbox wire:model="form.activo" label="Concepto activo" disabled />
            @else
                <x-ui.checkbox wire:model="form.activo" label="Concepto activo" />
            @endif
        </form>

        @if ($form->id)
            <div class="mt-5 border-t border-slate-200 pt-4 space-y-3">

                {{-- Albaranes vinculados --}}
                <div x-data="{ abierto: false }">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Albaranes vinculados
                            <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-700">
                                {{ $this->albaranesDelConcepto->count() }}
                            </span>
                        </h4>
                        <button type="button" x-on:click="abierto = !abierto"
                                class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                x-bind:title="abierto ? 'Plegar' : 'Desplegar'">
                            <x-heroicon-o-chevron-down x-bind:class="abierto ? 'rotate-180' : ''"
                                                       class="size-4 transition-transform" />
                        </button>
                    </div>

                    <div x-show="abierto" x-cloak x-transition class="mt-2">
                        @if ($this->albaranesDelConcepto->isEmpty())
                            <p class="text-sm text-slate-400">No hay albaranes vinculados a este concepto.</p>
                        @else
                            <div class="overflow-hidden rounded-md border border-slate-200">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Número</th>
                                            <th class="px-3 py-2 text-left">Fecha</th>
                                            <th class="px-3 py-2 text-left">Proyecto</th>
                                            <th class="px-3 py-2 text-left">Estado</th>
                                            <th class="px-3 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($this->albaranesDelConcepto as $albaran)
                                            <tr wire:key="alb-conc-{{ $albaran->id }}" class="hover:bg-slate-50">
                                                <td class="px-3 py-2 font-mono text-xs text-slate-700">{{ $albaran->numero ?? '#'.$albaran->id }}</td>
                                                <td class="px-3 py-2 text-slate-500">{{ $albaran->fecha?->format('d/m/Y') ?? '—' }}</td>
                                                <td class="px-3 py-2 text-slate-700">{{ $albaran->proyecto?->nombre ?? '—' }}</td>
                                                <td class="px-3 py-2">
                                                    @php $est = $albaran->estado instanceof \BackedEnum ? $albaran->estado->value : (string) $albaran->estado; @endphp
                                                    <x-ui.badge :tone="match($est) { 'firmado','facturado' => 'success', 'pendiente' => 'warning', default => 'neutral' }" dot>
                                                        {{ ucfirst($est) }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-3 py-2 text-right">
                                                    <x-ui.icon-button as="a" href="{{ route('albaranes.ver', $albaran) }}" wire:navigate
                                                        icon="heroicon-o-arrow-top-right-on-square" variant="info" tooltip="Ver albarán" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Proyectos vinculados --}}
                <div x-data="{ abierto: false }">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Proyectos vinculados
                            <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-700">
                                {{ $this->proyectosDelConcepto->count() }}
                            </span>
                        </h4>
                        <button type="button" x-on:click="abierto = !abierto"
                                class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                x-bind:title="abierto ? 'Plegar' : 'Desplegar'">
                            <x-heroicon-o-chevron-down x-bind:class="abierto ? 'rotate-180' : ''"
                                                       class="size-4 transition-transform" />
                        </button>
                    </div>

                    <div x-show="abierto" x-cloak x-transition class="mt-2">
                        @if ($this->proyectosDelConcepto->isEmpty())
                            <p class="text-sm text-slate-400">No hay proyectos vinculados a este concepto.</p>
                        @else
                            <div class="overflow-hidden rounded-md border border-slate-200">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Proyecto</th>
                                            <th class="px-3 py-2 text-left">Código</th>
                                            <th class="px-3 py-2 text-left">Cliente</th>
                                            <th class="px-3 py-2 text-left">Estado</th>
                                            <th class="px-3 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($this->proyectosDelConcepto as $proyecto)
                                            <tr wire:key="proy-conc-{{ $proyecto->id }}" class="hover:bg-slate-50">
                                                <td class="px-3 py-2 font-medium text-slate-800">{{ $proyecto->nombre }}</td>
                                                <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $proyecto->codigo ?? '—' }}</td>
                                                <td class="px-3 py-2 text-slate-500">{{ $proyecto->cliente?->nombre ?? '—' }}</td>
                                                <td class="px-3 py-2 text-slate-500">{{ $proyecto->estado ? ucfirst($proyecto->estado) : '—' }}</td>
                                                <td class="px-3 py-2 text-right">
                                                    <x-ui.icon-button as="a" href="{{ route('proyectos.ver', $proyecto) }}" wire:navigate
                                                        icon="heroicon-o-arrow-top-right-on-square" variant="info" tooltip="Ver proyecto" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        @endif

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModal">
                {{ $soloLectura ? 'Cerrar' : 'Cancelar' }}
            </x-ui.button>
            @unless ($soloLectura)
                <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="submit" form="form-concepto" wire:loading.attr="disabled">
                    Guardar
                </x-ui.button>
            @endunless
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
            <div class="space-y-2">
                <p class="text-sm text-slate-700">
                    Esta acción enviará el concepto a la <strong>papelera</strong>.
                </p>

                @if ($confirmarEliminarProyectosCount > 0)
                    <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        <p class="font-medium">
                            Se quitará de
                            {{ $confirmarEliminarProyectosCount }}
                            {{ $confirmarEliminarProyectosCount === 1 ? 'proyecto' : 'proyectos' }}.
                        </p>
                        <p class="mt-0.5 text-amber-700">
                            Los proyectos no se borran; solo dejan de tener este concepto disponible.
                            Si quieres seguir conservándolo, mejor <strong>desactívalo</strong> desde Editar.
                        </p>
                    </div>
                @else
                    <p class="text-sm text-slate-500">
                        El concepto dejará de aparecer en los selectores.
                    </p>
                @endif
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
</div>
