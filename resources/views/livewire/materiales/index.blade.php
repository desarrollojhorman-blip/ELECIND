<div>
    <x-ui.page-header title="Materiales" subtitle="Catálogo de materiales agrupados por número de pedido." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por descripción, unidad, nº pedido o familia…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle=""
            :panel-open="false"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Material::class)
                    <x-ui.button as="a" href="{{ route('materiales.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
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
                {{-- Filtro por pedido --}}
                <x-ui.field label="Filtrar por Nº Pedido">
                    <x-ui.select wire:key="pedido-{{ $resetKey }}" wire:model.live="filtroPedido">
                        <option value="">Todos los pedidos</option>
                        @foreach ($this->pedidosDisponibles as $ped)
                            <option value="{{ $ped->id }}">
                                {{ $ped->numero }}{{ $ped->proveedor ? ' - '.$ped->proveedor : '' }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                {{-- Filtro por familia --}}
                <x-ui.field label="Filtrar por Familia">
                    <x-ui.select wire:key="familia-{{ $resetKey }}" wire:model.live="filtroFamilia">
                        <option value="">Todas las familias</option>
                        <option value="sin_familia">Sin familia</option>
                        @foreach ($this->familiasDisponibles as $fam)
                            <option value="{{ $fam->id }}">{{ $fam->nombre }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroPedido !== '')
                            @php $pedNombre = $this->pedidosDisponibles->firstWhere('id', (int) $filtroPedido)?->numero ?? $filtroPedido; @endphp
                            <x-ui.filter-chip label="Pedido" :value="$pedNombre" remove-action="quitarFiltroPedido" />
                        @endif
                        @if ($filtroFamilia !== '')
                            @php
                                $famNombre = $filtroFamilia === 'sin_familia'
                                    ? 'Sin familia'
                                    : ($this->familiasDisponibles->firstWhere('id', (int) $filtroFamilia)?->nombre ?? $filtroFamilia);
                            @endphp
                            <x-ui.filter-chip label="Familia" :value="$famNombre" remove-action="quitarFiltroFamilia" />
                        @endif
                    </div>
                </x-slot:chips>
            @endif
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla --}}
    @if ($verPapelera && $this->puedeVerPapelera)
        <div class="mb-3 flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
            <x-heroicon-o-archive-box class="mt-0.5 size-4 shrink-0" />
            <p class="flex-1">
                <strong>Modo Papelera</strong> — viendo
                {{ $this->totalPapelera }}
                {{ $this->totalPapelera === 1 ? 'material eliminado' : 'materiales eliminados' }}.
            </p>
            <button type="button"
                    wire:click="$set('verPapelera', false)"
                    class="text-xs font-semibold text-amber-700 underline hover:text-amber-900">
                Salir
            </button>
        </div>
    @endif

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
        {{ $materiales->links() }}
    </div>
    <x-ui.data-table :colspan="7" empty="No hay materiales que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="numero_pedido_id" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Nº Pedido
                </x-ui.sortable-header>
                <x-ui.sortable-header column="descripcion" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Descripción
                </x-ui.sortable-header>
                <x-ui.sortable-header column="familia_id" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Familia
                </x-ui.sortable-header>
                <x-ui.sortable-header column="unidad_medida" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Unidad
                </x-ui.sortable-header>
                <x-ui.sortable-header column="stock" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Stock
                </x-ui.sortable-header>
                <x-ui.sortable-header column="activo" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Estado
                </x-ui.sortable-header>
                <x-ui.sortable-header align="center">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($materiales as $material)
                <tr wire:key="mat-{{ $material->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        @if ($material->numeroPedido)
                            <span class="font-mono text-xs font-semibold text-primary-700">
                                {{ $material->numeroPedido->numero }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-slate-900">{{ $material->descripcion }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($material->familia)
                            <span class="inline-flex items-center rounded-full bg-accent-100 px-2 py-0.5 text-xs font-medium text-primary-700">
                                {{ $material->familia->nombre }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $material->unidad_medida }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-mono text-sm font-semibold text-slate-700">
                            {{ rtrim(rtrim(number_format((float) $material->stock, 2, ',', ''), '0'), ',') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($material->activo)
                            <x-ui.badge tone="success" dot>Activo</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral" dot>Inactivo</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($material->trashed())
                                @can('restore', $material)
                                    <x-ui.icon-button
                                        wire:click="restaurar({{ $material->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="restaurar({{ $material->id }})"
                                        variant="success"
                                        tooltip="Restaurar">
                                        <span wire:loading.remove wire:target="restaurar({{ $material->id }})">
                                            <x-heroicon-o-arrow-uturn-left class="size-4" />
                                        </span>
                                        <svg wire:loading wire:target="restaurar({{ $material->id }})" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                    </x-ui.icon-button>
                                @endcan
                            @else
                                @can('view', $material)
                                    <x-ui.icon-button as="a" href="{{ route('materiales.ver', $material) }}" wire:navigate
                                        icon="heroicon-o-eye" variant="secondary" tooltip="Ver" />
                                @endcan
                                @can('update', $material)
                                    <x-ui.icon-button as="a" href="{{ route('materiales.editar', $material) }}" wire:navigate
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

    {{-- Modal crear/ver/editar material --}}
    <x-ui.modal :show="$modalAbierto"
        :title="$modoSoloLectura ? 'Ver material' : ($form->id ? 'Editar material' : 'Nuevo material')"
        close-action="cerrarModal"
        size="md">

        <form wire:submit="guardar" id="form-material" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nº Pedido" required :error="$errors->first('form.numero_pedido_id')">
                    <x-ui.searchable-select
                        wire:key="pedido-select-{{ $modalKey }}"
                        wire-model="form.numero_pedido_id"
                        :value="$form->numero_pedido_id"
                        :options="$this->pedidosDisponibles->map(fn($p) => ['value' => $p->id, 'label' => $p->numero.($p->proveedor ? ' ('.$p->proveedor.')' : '')])->all()"
                        placeholder="— Selecciona un pedido —"
                        :disabled="$modoSoloLectura"
                    />
                </x-ui.field>

                <x-ui.field label="Familia" :error="$errors->first('form.familia_id')"
                            hint="Opcional. Da de alta familias en Materiales > Familias.">
                    <x-ui.searchable-select
                        wire:key="familia-select-{{ $modalKey }}"
                        wire-model="form.familia_id"
                        :value="$form->familia_id"
                        :options="$this->familiasDisponibles->map(fn($f) => ['value' => $f->id, 'label' => $f->id.' · '.$f->nombre])->all()"
                        placeholder="— Sin familia —"
                        :disabled="$modoSoloLectura"
                    />
                </x-ui.field>
            </div>

            <x-ui.field label="Descripción" required :error="$errors->first('form.descripcion')">
                <x-ui.input wire:model="form.descripcion" autofocus placeholder="Ej. Cable H07V-K 2,5mm² negro" :disabled="$modoSoloLectura" />
            </x-ui.field>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field label="Unidad de medida" required :error="$errors->first('form.unidad_medida')">
                    <x-ui.select wire:model="form.unidad_medida" :disabled="$modoSoloLectura">
                        <option value="ud">ud (unidades)</option>
                        <option value="m">m (metros)</option>
                        <option value="kg">kg (kilogramos)</option>
                        <option value="l">l (litros)</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Stock" required :error="$errors->first('form.stock')">
                    <x-ui.input type="number" step="0.01" min="0" wire:model="form.stock" :disabled="$modoSoloLectura" />
                </x-ui.field>
            </div>
        </form>

        <x-slot:footer>
            @if (!$modoSoloLectura)
                <x-ui.button variant="neutral" wire:click="cerrarModal">Cancelar</x-ui.button>
                <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="submit" form="form-material"
                             wire:loading.attr="disabled">
                    Guardar
                </x-ui.button>
            @endif
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal :show="$confirmarEliminarId !== null"
        title="Eliminar material"
        close-action="cancelarEliminar"
        size="sm">

        @if ($errorEliminar)
            <div class="flex gap-3 rounded-md border border-red-200 bg-red-50 p-3">
                <x-heroicon-o-no-symbol class="mt-0.5 size-5 shrink-0 text-red-500" />
                <p class="text-sm text-red-700">{{ $errorEliminar }}</p>
            </div>
        @else
            <div class="flex gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <x-heroicon-o-exclamation-triangle class="size-5" />
                </div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el material a la <strong>papelera</strong> (eliminación lógica).
                </p>
            </div>
        @endif

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            @if (!$errorEliminar)
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
            @endif
        </x-slot:footer>
    </x-ui.modal>
</div>
