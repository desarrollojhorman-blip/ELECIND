<div>
    <x-ui.page-header title="Nº Pedido" subtitle="Gestión de números de pedido y sus materiales." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por número, descripción o proveedor…"
            :filtros-aplicados="0"
            :panel-open="false"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="trim($buscar) !== ''">

            <x-slot:leftActions>
                @can('create', App\Models\NumeroPedido::class)
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
                        Nuevo pedido
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla --}}
    <x-ui.data-table :colspan="5" empty="No hay números de pedido que coincidan con la búsqueda.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="numero" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nº Pedido
                </x-ui.sortable-header>
                <x-ui.sortable-header column="fecha" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Fecha
                </x-ui.sortable-header>
                <x-ui.sortable-header column="proveedor" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Proveedor
                </x-ui.sortable-header>
                <x-ui.sortable-header>Materiales</x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($pedidos as $pedido)
                <tr wire:key="ped-{{ $pedido->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-mono text-sm font-semibold text-slate-900">{{ $pedido->numero }}</div>
                        @if ($pedido->descripcion)
                            <div class="text-xs text-slate-500 line-clamp-1">{{ $pedido->descripcion }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        {{ $pedido->fecha->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        {{ $pedido->proveedor ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge tone="primary">{{ $pedido->materiales_count }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($pedido->trashed())
                                @can('restore', $pedido)
                                    <x-ui.icon-button wire:click="restaurar({{ $pedido->id }})"
                                        icon="heroicon-o-arrow-uturn-left" variant="success" tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('view', $pedido)
                                    <x-ui.icon-button wire:click="abrirVer({{ $pedido->id }})"
                                        icon="heroicon-o-eye" variant="secondary" tooltip="Ver" />
                                @endcan
                                @can('update', $pedido)
                                    <x-ui.icon-button wire:click="abrirEditar({{ $pedido->id }})"
                                        icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                @endcan
                                @can('delete', $pedido)
                                    <x-ui.icon-button wire:click="confirmarEliminar({{ $pedido->id }})"
                                        icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    <div class="mt-3">{{ $pedidos->links() }}</div>

    {{-- Modal crear / ver / editar pedido --}}
    <x-ui.modal :show="$modalAbierto"
        :title="$modoSoloLectura ? 'Ver pedido' : ($form->id ? 'Editar pedido' : 'Nuevo pedido')"
        close-action="cerrarModal"
        size="lg">

        <div class="space-y-5">
            {{-- Campos del pedido --}}
            <form wire:submit="guardar" id="form-pedido" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Nº Pedido" required :error="$errors->first('form.numero')">
                        <x-ui.input wire:model="form.numero" placeholder="Ej. PED-2026-001" class="font-mono"
                                    :disabled="$modoSoloLectura" autofocus />
                    </x-ui.field>

                    <x-ui.field label="Fecha" required :error="$errors->first('form.fecha')">
                        <x-ui.input type="date" wire:model="form.fecha" :disabled="$modoSoloLectura" />
                    </x-ui.field>

                    <x-ui.field label="Proveedor" :error="$errors->first('form.proveedor')" class="md:col-span-2">
                        <x-ui.input wire:model="form.proveedor" placeholder="Nombre del proveedor" :disabled="$modoSoloLectura" />
                    </x-ui.field>

                    <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')" class="md:col-span-2">
                        <x-ui.textarea wire:model="form.descripcion" rows="2" :disabled="$modoSoloLectura" />
                    </x-ui.field>
                </div>
            </form>

            {{-- Sección materiales --}}
            <div class="border-t border-slate-200 pt-4">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Materiales del pedido
                </h3>

                {{-- Materiales ya guardados (solo en edición) --}}
                @if ($form->id !== null)
                    @php $matsGuardados = $this->materialesDelPedidoActual; @endphp
                    @if ($matsGuardados->isNotEmpty())
                        <div class="mb-3 overflow-hidden rounded-md border border-slate-200">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Descripción</th>
                                        <th class="px-3 py-2 text-left">Unidad</th>
                                        <th class="px-3 py-2 text-right">Stock</th>
                                        @if (! $modoSoloLectura)
                                            <th class="px-3 py-2 text-right"></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($matsGuardados as $mat)
                                        <tr wire:key="mat-saved-{{ $mat->id }}" class="hover:bg-slate-50">
                                            <td class="px-3 py-2 text-slate-800">{{ $mat->descripcion }}</td>
                                            <td class="px-3 py-2 text-slate-500">{{ $mat->unidad_medida }}</td>
                                            <td class="px-3 py-2 text-right font-mono text-slate-700">
                                                {{ rtrim(rtrim(number_format((float) $mat->stock, 2, ',', ''), '0'), ',') }}
                                            </td>
                                            @if (! $modoSoloLectura)
                                                <td class="px-3 py-2 text-right">
                                                    @can('delete', $mat)
                                                        <button type="button"
                                                                wire:click="eliminarMaterialDelPedido({{ $mat->id }})"
                                                                class="text-slate-400 hover:text-red-500"
                                                                title="Eliminar material">
                                                            <x-heroicon-o-trash class="size-4" />
                                                        </button>
                                                    @endcan
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mb-3 text-sm text-slate-400">No hay materiales en este pedido aún.</p>
                    @endif
                @endif

                {{-- Materiales pendientes (nuevos aún no guardados) --}}
                @if (count($materialesPendientes) > 0)
                    <div class="mb-3 overflow-hidden rounded-md border border-amber-200 bg-amber-50">
                        <table class="w-full text-sm">
                            <thead class="bg-amber-100 text-xs uppercase text-amber-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Descripción</th>
                                    <th class="px-3 py-2 text-left">Unidad</th>
                                    <th class="px-3 py-2 text-right">Stock</th>
                                    <th class="px-3 py-2 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-100">
                                @foreach ($materialesPendientes as $i => $mat)
                                    <tr wire:key="mat-pend-{{ $i }}" class="hover:bg-amber-100">
                                        <td class="px-3 py-2 text-slate-800">{{ $mat['descripcion'] }}</td>
                                        <td class="px-3 py-2 text-slate-500">{{ $mat['unidad_medida'] }}</td>
                                        <td class="px-3 py-2 text-right font-mono text-slate-700">
                                            {{ rtrim(rtrim(number_format((float) $mat['stock'], 2, ',', ''), '0'), ',') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button"
                                                    wire:click="quitarMaterialPendiente({{ $i }})"
                                                    class="text-amber-500 hover:text-red-500"
                                                    title="Quitar">
                                                <x-heroicon-o-x-mark class="size-4" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Mini-formulario añadir material (solo en modo edición) --}}
                @if (! $modoSoloLectura)
                    <div class="grid grid-cols-12 gap-2 items-end rounded-md border border-dashed border-slate-300 bg-slate-50 p-3">
                        <div class="col-span-6">
                            <x-ui.field label="Descripción" :error="$errors->first('matDescripcion')">
                                <x-ui.input wire:model="matDescripcion" placeholder="Ej. Cable H07V-K 2,5mm²" />
                            </x-ui.field>
                        </div>
                        <div class="col-span-2">
                            <x-ui.field label="Unidad" :error="$errors->first('matUnidad')">
                                <x-ui.select wire:model="matUnidad">
                                    <option value="ud">ud</option>
                                    <option value="m">m</option>
                                    <option value="kg">kg</option>
                                    <option value="l">l</option>
                                </x-ui.select>
                            </x-ui.field>
                        </div>
                        <div class="col-span-2">
                            <x-ui.field label="Stock" :error="$errors->first('matStock')">
                                <x-ui.input type="number" step="0.01" min="0" wire:model="matStock" />
                            </x-ui.field>
                        </div>
                        <div class="col-span-2">
                            <x-ui.button variant="primary" wire:click="agregarMaterialPendiente" icon="heroicon-o-plus" class="w-full">
                                Añadir
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <x-slot:footer>
            @if ($modoSoloLectura)
                <x-ui.button variant="ghost" wire:click="cerrarModal">Cerrar</x-ui.button>
            @else
                <x-ui.button variant="ghost" wire:click="cerrarModal">Cancelar</x-ui.button>
                <x-ui.button variant="success" type="submit" form="form-pedido"
                             wire:loading.attr="disabled" icon="heroicon-o-check">
                    Guardar
                </x-ui.button>
            @endif
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal :show="$confirmarEliminarId !== null"
        title="Eliminar pedido"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el pedido a la <strong>papelera</strong>.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Los materiales vinculados también se eliminarán.
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
