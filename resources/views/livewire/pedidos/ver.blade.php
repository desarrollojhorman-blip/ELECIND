<div class="space-y-4" x-data="{ tab: 'pedido' }">
    <x-ui.page-header title="Ver pedido" :id-badge="$pedido->id"
                       subtitle="Datos del pedido y materiales que lo componen.">
        <x-slot:actions>
            <div class="text-right">
                <div class="text-xl font-semibold text-slate-900 font-mono">{{ $pedido->numero }}</div>
            </div>
        </x-slot:actions>

        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('materiales.pedidos') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @can('create', App\Models\NumeroPedido::class)
                <x-ui.button as="a" href="{{ route('pedidos.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                    Nuevo
                </x-ui.button>
            @endcan
            @can('update', $pedido)
                <x-ui.button as="a" href="{{ route('pedidos.editar', $pedido) }}" wire:navigate variant="info" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('delete', $pedido)
                <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                    Eliminar
                </x-ui.button>
            @endcan
        </x-slot:actionsLeft>
    </x-ui.page-header>

    {{-- Tabs --}}
    <div class="flex items-end overflow-x-auto border-b border-slate-200 px-2 pt-1.5">
        <button type="button"
                @click="tab = 'pedido'"
                :class="tab === 'pedido'
                    ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                    : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
            Pedido
        </button>
        <button type="button"
                @click="tab = 'consumo'"
                :class="tab === 'consumo'
                    ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                    : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
            Consumo
            @if ($this->materialesConConsumo->count() > 0)
                <span class="rounded-full bg-slate-100 px-1.5 text-xs">{{ $this->materialesConConsumo->count() }}</span>
            @endif
        </button>
    </div>

    {{-- ═══ Tab: Pedido ═══ --}}
    <div x-show="tab === 'pedido'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white px-6 py-6 shadow-sm space-y-6">
        {{-- Cabecera --}}
        <div>
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Cabecera</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nº pedido">
                    <x-ui.input :value="$pedido->numero" readonly />
                </x-ui.field>
                <x-ui.field label="Fecha">
                    <x-ui.input :value="$pedido->fecha?->format('d/m/Y') ?? '—'" readonly />
                </x-ui.field>
                <x-ui.field label="Proveedor">
                    <x-ui.input :value="$pedido->proveedor ?? '—'" readonly />
                </x-ui.field>
                <x-ui.field label="Descripción" class="md:col-span-2">
                    <x-ui.textarea :value="$pedido->descripcion ?? ''" readonly rows="2" />
                </x-ui.field>
            </div>
        </div>

        {{-- Materiales --}}
        <div>
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Materiales del pedido
                @if ($this->materialesDelPedido->count() > 0)
                    <span class="ml-1 rounded-full bg-slate-100 px-1.5 text-[10px] font-normal text-slate-600">{{ $this->materialesDelPedido->count() }}</span>
                @endif
            </h3>

            @if ($this->materialesDelPedido->isEmpty())
                <div class="rounded-md border border-dashed border-slate-200 px-6 py-8 text-center text-sm text-slate-400">
                    Este pedido no tiene materiales.
                </div>
            @else
                <div class="overflow-x-auto rounded-md border border-slate-200">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-3 py-2 w-12">ID</th>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2">Familia</th>
                                <th class="px-3 py-2 w-20">Unidad</th>
                                <th class="px-3 py-2 w-24 text-right">Stock</th>
                                @can('materiales.gestionar_precios')
                                    <th class="px-3 py-2 w-28 text-right">Coste €</th>
                                    <th class="px-3 py-2 w-28 text-right">Venta €</th>
                                @endcan
                                <th class="px-3 py-2 w-16 text-right">Ver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->materialesDelPedido as $m)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-slate-500">{{ $m->id }}</td>
                                    <td class="px-3 py-2 text-slate-700">{{ $m->descripcion }}</td>
                                    <td class="px-3 py-2 text-slate-500">{{ $m->familia?->nombre ?? '—' }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ $m->unidad_medida }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-slate-700">{{ rtrim(rtrim(number_format((float) $m->stock, 2, ',', ''), '0'), ',') ?: '0' }}</td>
                                    @can('materiales.gestionar_precios')
                                        <td class="px-3 py-2 text-right font-mono text-slate-600">{{ $m->precio_coste !== null ? number_format((float) $m->precio_coste, 2, ',', '.') : '—' }}</td>
                                        <td class="px-3 py-2 text-right font-mono text-slate-700">{{ $m->precio_venta !== null ? number_format((float) $m->precio_venta, 2, ',', '.') : '—' }}</td>
                                    @endcan
                                    <td class="px-3 py-2 text-right">
                                        @can('view', $m)
                                            <x-ui.icon-button as="a" href="{{ route('materiales.ver', $m) }}" wire:navigate icon="heroicon-o-arrow-top-right-on-square" variant="neutral" tooltip="Ir al material" />
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ Tab: Consumo ═══ --}}
    <div x-show="tab === 'consumo'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="px-6 py-4">
            <span class="text-sm font-semibold text-slate-900">Consumo de materiales</span>
            <p class="mt-0.5 text-xs text-slate-400">
                Cuánto se ha gastado en albaranes y cuánto queda en stock por cada material del pedido.
            </p>
        </div>
        @if ($this->materialesConConsumo->isEmpty())
            <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                El pedido no tiene materiales todavía.
            </div>
        @else
            <div class="overflow-x-auto border-t border-slate-100">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Material</th>
                            <th class="px-4 py-2 text-right">Consumido</th>
                            <th class="px-4 py-2 text-right">Stock actual</th>
                            <th class="px-4 py-2 w-16 text-right">Ver</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->materialesConConsumo as $m)
                            <tr>
                                <td class="px-4 py-2 text-slate-700">{{ $m->descripcion }}</td>
                                <td class="px-4 py-2 text-right font-mono text-slate-600">
                                    {{ rtrim(rtrim(number_format((float) ($m->cantidad_consumida ?? 0), 2, ',', ''), '0'), ',') ?: '0' }} {{ $m->unidad_medida }}
                                </td>
                                <td class="px-4 py-2 text-right font-mono text-slate-900">
                                    {{ rtrim(rtrim(number_format((float) $m->stock, 2, ',', ''), '0'), ',') ?: '0' }} {{ $m->unidad_medida }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    @can('view', $m)
                                        <x-ui.icon-button as="a"
                                                           href="{{ route('materiales.ver', $m) }}"
                                                           wire:navigate
                                                           size="sm"
                                                           variant="info"
                                                           icon="heroicon-o-arrow-top-right-on-square"
                                                           tooltip="Ir al material" />
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

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
                    ¿Eliminar el pedido <strong>{{ $pedido->numero }}</strong>?
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Esta acción no se puede deshacer.
                </p>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminar"
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
