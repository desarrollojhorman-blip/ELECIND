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
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
                        Nuevo material
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>

            <div class="grid gap-3 md:grid-cols-2">
                {{-- Filtro por pedido --}}
                <x-ui.field label="Filtrar por Nº Pedido">
                    <x-ui.select wire:key="pedido-{{ $resetKey }}" wire:model.live="filtroPedido">
                        <option value="">Todos los pedidos</option>
                        @foreach ($this->pedidosDisponibles as $ped)
                            <option value="{{ $ped->id }}">
                                {{ $ped->numero }}{{ $ped->proveedor ? ' — '.$ped->proveedor : '' }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                {{-- Filtro por familia --}}
                <x-ui.field label="Filtrar por Familia">
                    <x-ui.select wire:key="familia-{{ $resetKey }}" wire:model.live="filtroFamilia">
                        <option value="">Todas las familias</option>
                        <option value="sin_familia">— Sin familia —</option>
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
    <x-ui.data-table :colspan="6" empty="No hay materiales que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="numero_pedido_id" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nº Pedido
                </x-ui.sortable-header>
                <x-ui.sortable-header column="descripcion" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Descripción
                </x-ui.sortable-header>
                <x-ui.sortable-header column="familia_id" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Familia
                </x-ui.sortable-header>
                <x-ui.sortable-header column="unidad_medida" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Unidad
                </x-ui.sortable-header>
                <x-ui.sortable-header column="stock" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Stock
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
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
                        <span class="text-xs text-slate-400">{{ $material->unidad_medida }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($material->trashed())
                                @can('restore', $material)
                                    <x-ui.icon-button wire:click="restaurar({{ $material->id }})"
                                        icon="heroicon-o-arrow-uturn-left" variant="success" tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('view', $material)
                                    <x-ui.icon-button wire:click="abrirVer({{ $material->id }})"
                                        icon="heroicon-o-eye" variant="secondary" tooltip="Ver" />
                                @endcan
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

    {{-- Modal crear/ver/editar material --}}
    <x-ui.modal :show="$modalAbierto"
        :title="$modoSoloLectura ? 'Ver material' : ($form->id ? 'Editar material' : 'Nuevo material')"
        close-action="cerrarModal"
        size="md">

        <form wire:submit="guardar" id="form-material" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nº Pedido" required :error="$errors->first('form.numero_pedido_id')">
                    <x-ui.select wire:model="form.numero_pedido_id" :disabled="$modoSoloLectura">
                        <option value="">— Selecciona un pedido —</option>
                        @foreach ($this->pedidosDisponibles as $ped)
                            <option value="{{ $ped->id }}">
                                {{ $ped->numero }}{{ $ped->proveedor ? ' ('.$ped->proveedor.')' : '' }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Familia" :error="$errors->first('form.familia_id')"
                            hint="Opcional. Da de alta familias en Materiales > Familias.">
                    <x-ui.select wire:model="form.familia_id" :disabled="$modoSoloLectura">
                        <option value="">— Sin familia —</option>
                        @foreach ($this->familiasDisponibles as $fam)
                            <option value="{{ $fam->id }}">{{ $fam->nombre }}</option>
                        @endforeach
                    </x-ui.select>
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
            @if ($modoSoloLectura)
                <x-ui.button variant="ghost" wire:click="cerrarModal">Cerrar</x-ui.button>
            @else
                <x-ui.button variant="ghost" wire:click="cerrarModal">Cancelar</x-ui.button>
                <x-ui.button variant="success" type="submit" form="form-material"
                             wire:loading.attr="disabled" icon="heroicon-o-check">
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

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <p class="text-sm text-slate-700">
                Esta acción enviará el material a la <strong>papelera</strong> (eliminación lógica).
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
