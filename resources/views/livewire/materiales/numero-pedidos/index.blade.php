<div>
    <x-ui.page-header title="Pedidos" subtitle="Documentos de compra al proveedor con sus materiales." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por nº pedido, proveedor o descripción…"
            :filtros-aplicados="0"
            :panel-open="false"
            :reset-key="$resetKey"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="trim($buscar) !== ''">

            <x-slot:leftActions>
                @can('create', App\Models\NumeroPedido::class)
                    <x-ui.button as="a" href="{{ route('pedidos.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>
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
            </select>
        </div>
        {{ $pedidos->links() }}
    </div>

    <x-ui.data-table :colspan="6" empty="No hay pedidos que coincidan con la búsqueda.">
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
                <x-ui.sortable-header>Descripción</x-ui.sortable-header>
                <x-ui.sortable-header align="center">Materiales</x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($pedidos as $pedido)
                <tr wire:key="pedido-{{ $pedido->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono font-medium text-slate-900">{{ $pedido->numero }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $pedido->fecha?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $pedido->proveedor ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ \Illuminate\Support\Str::limit($pedido->descripcion ?? '—', 60) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                            {{ $pedido->materiales_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @can('view', $pedido)
                                <x-ui.icon-button
                                    as="a"
                                    href="{{ route('pedidos.ver', $pedido) }}"
                                    wire:navigate
                                    icon="heroicon-o-eye"
                                    variant="neutral"
                                    tooltip="Ver detalle" />
                            @endcan
                            @can('update', $pedido)
                                <x-ui.icon-button
                                    as="a"
                                    href="{{ route('pedidos.editar', $pedido) }}"
                                    wire:navigate.fresh
                                    icon="heroicon-o-pencil-square"
                                    variant="info"
                                    tooltip="Editar" />
                            @endcan
                            @can('delete', $pedido)
                                <x-ui.icon-button
                                    wire:click="confirmarEliminar({{ $pedido->id }})"
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

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar pedido"
        close-action="cancelarEliminar"
        size="sm">
        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    ¿Eliminar este pedido?
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Si tiene materiales asociados también se eliminarán (siempre que ninguno esté usado en un albarán).
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
