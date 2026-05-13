<div>
    {{-- Migaja --}}
    <nav class="mb-3 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('materiales.index') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-slate-900">
            <x-heroicon-m-arrow-left class="size-4" />
            Materiales
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700">{{ $material->nombre }}</span>
    </nav>

    {{-- Cabecera con datos del material --}}
    <x-ui.card class="mb-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ $material->nombre }}</h2>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    @if ($material->codigo)
                        <span class="font-mono">{{ $material->codigo }}</span>
                        <span class="text-slate-300">·</span>
                    @endif
                    @if ($material->grupo)
                        <x-ui.badge tone="primary">{{ $material->grupo }}</x-ui.badge>
                    @endif
                    @if ($material->descripcion)
                        <span>{{ $material->descripcion }}</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Stock total</p>
                    @php
                        $stockMinimo = (float) $material->stock_minimo;
                        $stockBajo = $stockMinimo > 0 && $this->stockTotal <= $stockMinimo;
                    @endphp
                    <p @class([
                            'text-2xl font-semibold',
                            'text-amber-700' => $stockBajo,
                            'text-slate-900' => ! $stockBajo,
                        ])>
                        {{ rtrim(rtrim(number_format($this->stockTotal, 2, ',', ''), '0'), ',') }}
                        <span class="text-sm font-normal text-slate-500">{{ $material->unidad_medida }}</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Stock mínimo</p>
                    <p class="text-2xl font-semibold text-slate-700">
                        {{ rtrim(rtrim(number_format($stockMinimo, 2, ',', ''), '0'), ',') }}
                        <span class="text-sm font-normal text-slate-500">{{ $material->unidad_medida }}</span>
                    </p>
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- Toolbar de lotes --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por código de lote, proveedor o nº pedido…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\MaterialLote::class)
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
                        Nuevo lote
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>

            <div class="grid gap-3">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="todos">Todos</option>
                        <option value="con_stock">Con stock</option>
                        <option value="sin_stock">Sin stock</option>
                        <option value="papelera">En papelera</option>
                    </x-ui.select>
                </x-ui.field>
            </div>

            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroEstado !== 'todos')
                            <x-ui.filter-chip label="Estado" :value="str_replace('_', ' ', ucfirst($filtroEstado))"
                                              remove-action="quitarFiltroEstado" />
                        @endif
                    </div>
                </x-slot:chips>
            @endif
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla de lotes --}}
    <x-ui.data-table :colspan="7" empty="Este material todavía no tiene lotes registrados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="codigo_lote" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Código lote
                </x-ui.sortable-header>
                <x-ui.sortable-header column="proveedor" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Proveedor
                </x-ui.sortable-header>
                <x-ui.sortable-header>Nº pedido</x-ui.sortable-header>
                <x-ui.sortable-header column="stock_disponible" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Stock (disp / inicial)
                </x-ui.sortable-header>
                <x-ui.sortable-header column="fecha_entrada" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Entrada
                </x-ui.sortable-header>
                <x-ui.sortable-header column="fecha_caducidad" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Caducidad
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($lotes as $lote)
                @php
                    $disponible = (float) $lote->stock_disponible;
                    $inicial = (float) $lote->stock_inicial;
                    $porcentaje = $inicial > 0 ? min(100, max(0, ($disponible / $inicial) * 100)) : 0;
                @endphp
                <tr wire:key="lote-{{ $lote->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">
                        {{ $lote->codigo_lote ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $lote->proveedor ?? '—' }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $lote->n_pedido ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-slate-700">
                                {{ rtrim(rtrim(number_format($disponible, 2, ',', ''), '0'), ',') }}
                            </span>
                            <span class="text-xs text-slate-400">
                                / {{ rtrim(rtrim(number_format($inicial, 2, ',', ''), '0'), ',') }}
                            </span>
                            <div class="ml-1 h-1.5 w-16 overflow-hidden rounded-full bg-slate-200">
                                <div @class([
                                        'h-full transition-all',
                                        'bg-emerald-500' => $porcentaje > 50,
                                        'bg-amber-500' => $porcentaje > 0 && $porcentaje <= 50,
                                        'bg-red-500' => $porcentaje === 0.0,
                                    ])
                                     style="width: {{ $porcentaje }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $lote->fecha_entrada?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        @if ($lote->fecha_caducidad)
                            @php
                                $diasParaCaducar = now()->diffInDays($lote->fecha_caducidad, false);
                                $proximoCaducar = $diasParaCaducar >= 0 && $diasParaCaducar <= 30;
                                $caducado = $diasParaCaducar < 0;
                            @endphp
                            <span @class([
                                'text-red-700 font-medium' => $caducado,
                                'text-amber-700' => $proximoCaducar,
                            ])>
                                {{ $lote->fecha_caducidad->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($lote->trashed())
                                @can('restore', $lote)
                                    <x-ui.icon-button wire:click="restaurar({{ $lote->id }})"
                                        icon="heroicon-o-arrow-uturn-left" variant="success" tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('update', $lote)
                                    <x-ui.icon-button wire:click="abrirEditar({{ $lote->id }})"
                                        icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                @endcan
                                @can('delete', $lote)
                                    <x-ui.icon-button wire:click="confirmarEliminar({{ $lote->id }})"
                                        icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    <div class="mt-3">{{ $lotes->links() }}</div>

    {{-- Modal crear/editar lote --}}
    <x-ui.modal :show="$modalAbierto"
        :title="$form->id ? 'Editar lote' : 'Nuevo lote'"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-lote" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Código de lote" :error="$errors->first('form.codigo_lote')"
                            hint="Único en todos los lotes (opcional).">
                    <x-ui.input wire:model="form.codigo_lote" placeholder="Ej. LOT-2026-001" class="font-mono" />
                </x-ui.field>

                <x-ui.field label="Nº pedido" :error="$errors->first('form.n_pedido')">
                    <x-ui.input wire:model="form.n_pedido" placeholder="Ej. PED-2026/05" class="font-mono" />
                </x-ui.field>

                <x-ui.field label="Proveedor" :error="$errors->first('form.proveedor')" class="md:col-span-2">
                    <x-ui.input wire:model="form.proveedor" />
                </x-ui.field>

                <x-ui.field label="Stock inicial" required :error="$errors->first('form.stock_inicial')"
                            hint="Cantidad recibida en este lote.">
                    <x-ui.input type="number" step="0.01" min="0" wire:model.live="form.stock_inicial" />
                </x-ui.field>

                <x-ui.field label="Stock disponible" required :error="$errors->first('form.stock_disponible')"
                            hint="Cantidad actual disponible (≤ inicial).">
                    <x-ui.input type="number" step="0.01" min="0" wire:model="form.stock_disponible" />
                </x-ui.field>

                <x-ui.field label="Fecha de entrada" :error="$errors->first('form.fecha_entrada')">
                    <x-ui.input type="date" wire:model="form.fecha_entrada" />
                </x-ui.field>

                <x-ui.field label="Fecha de caducidad" :error="$errors->first('form.fecha_caducidad')">
                    <x-ui.input type="date" wire:model="form.fecha_caducidad" />
                </x-ui.field>

                <x-ui.field label="Stock mínimo del lote" :error="$errors->first('form.stock_minimo_lote')"
                            class="md:col-span-2"
                            hint="Aviso si este lote concreto baja del umbral.">
                    <x-ui.input type="number" step="0.01" min="0" wire:model="form.stock_minimo_lote" />
                </x-ui.field>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cerrarModal">Cancelar</x-ui.button>
            <x-ui.button variant="success" type="submit" form="form-lote"
                         wire:loading.attr="disabled" icon="heroicon-o-check">
                Guardar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación de lote --}}
    <x-ui.modal :show="$confirmarEliminarId !== null"
        title="Eliminar lote"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el lote a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Los movimientos de stock asociados se conservan para trazabilidad.
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
