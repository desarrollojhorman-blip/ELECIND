<div class="space-y-4" x-data="{ tab: 'pedido' }">
    <x-ui.page-header :title="$titulo" :id-badge="$pedido?->id"
                       subtitle="Datos del pedido y materiales que lo componen.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('materiales.pedidos') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($pedido)
                @can('create', App\Models\NumeroPedido::class)
                    <x-ui.button as="a" href="{{ route('pedidos.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
                @can('delete', $pedido)
                    <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                        Eliminar
                    </x-ui.button>
                @endcan
            @endif
        </x-slot:actionsLeft>

        <x-slot:actionsRight>
            <x-ui.button variant="neutral" wire:click="deshacer" icon="heroicon-o-arrow-uturn-left">
                Deshacer
            </x-ui.button>
            <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="submit" form="form-pedido" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actionsRight>
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

        @if ($pedido)
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
        @else
            <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                  title="Guarda primero el pedido para acceder al consumo">
                <x-heroicon-o-lock-closed class="size-3" />
                Consumo
            </span>
        @endif
    </div>

    {{-- ═══ Tab: Pedido ═══ --}}
    <div x-show="tab === 'pedido'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white px-6 py-6 shadow-sm space-y-6">
        <form wire:submit="guardar" id="form-pedido" class="space-y-6">
            {{-- Cabecera del pedido --}}
            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Cabecera</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Nº pedido" required :error="$errors->first('form.numero')">
                        <x-ui.input wire:model.live.debounce.500ms="form.numero" autofocus />
                    </x-ui.field>

                    <x-ui.field label="Fecha" required :error="$errors->first('form.fecha')">
                        <x-ui.input type="date" wire:model="form.fecha" />
                    </x-ui.field>

                    <x-ui.field label="Proveedor" :error="$errors->first('form.proveedor')">
                        <x-ui.input wire:model="form.proveedor" />
                    </x-ui.field>

                    <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')" class="md:col-span-2">
                        <x-ui.textarea wire:model="form.descripcion" rows="2" />
                    </x-ui.field>
                </div>
            </div>

            {{-- Crear artículo (tabla de materiales del pedido) --}}
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Crear artículo
                        @if (count($lineas) > 0)
                            <span class="ml-1 rounded-full bg-slate-100 px-1.5 text-[10px] font-normal text-slate-600">{{ count($lineas) }}</span>
                        @endif
                    </h3>
                    @can('create', App\Models\Material::class)
                        <x-ui.button type="button" variant="success" size="sm" wire:click="agregarLinea" icon="heroicon-o-plus">
                            Añadir
                        </x-ui.button>
                    @endcan
                </div>

                @if (count($lineas) === 0)
                    <div class="rounded-md border border-dashed border-slate-200 px-6 py-8 text-center text-sm text-slate-400">
                        Aún no hay materiales. Pulsa <strong>Añadir</strong> para empezar.
                    </div>
                @else
                    <div class="overflow-x-auto rounded-md border border-slate-200">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="w-10 px-2 py-2 text-center">#</th>
                                    <th class="px-2 py-2">Descripción <span class="text-red-500">*</span></th>
                                    <th class="px-2 py-2">Familia</th>
                                    <th class="px-2 py-2 w-24">Unidad <span class="text-red-500">*</span></th>
                                    <th class="px-2 py-2 w-28 text-right">Stock <span class="text-red-500">*</span></th>
                                    @can('materiales.gestionar_precios')
                                        <th class="px-2 py-2 w-28 text-right">Coste €</th>
                                        <th class="px-2 py-2 w-28 text-right">Venta €</th>
                                    @endcan
                                    <th class="w-20 px-2 py-2 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($lineas as $i => $linea)
                                    <tr wire:key="linea-{{ $i }}-{{ $linea['id'] ?? 'new' }}"
                                        class="align-top hover:bg-slate-50/50">
                                        <td class="px-2 py-2 text-center text-xs text-slate-400">
                                            {{ $i + 1 }}
                                            @if (! $linea['id'])
                                                <div class="mt-0.5">
                                                    <span class="rounded bg-emerald-100 px-1 py-0.5 text-[10px] text-emerald-700">nuevo</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2">
                                            <x-ui.input wire:model="lineas.{{ $i }}.descripcion" />
                                            @error('lineas.'.$i.'.descripcion')
                                                <p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>
                                            @enderror
                                            @error('lineas.'.$i)
                                                <p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-2 py-2">
                                            <x-ui.select wire:model="lineas.{{ $i }}.familia_id">
                                                <option value="">—</option>
                                                @foreach ($this->familiasDisponibles as $fam)
                                                    <option value="{{ $fam->id }}">{{ $fam->nombre }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <x-ui.select wire:model="lineas.{{ $i }}.unidad_medida">
                                                <option value="ud">ud</option>
                                                <option value="m">m</option>
                                                <option value="m2">m²</option>
                                                <option value="m3">m³</option>
                                                <option value="kg">kg</option>
                                                <option value="l">l</option>
                                            </x-ui.select>
                                            @error('lineas.'.$i.'.unidad_medida')
                                                <p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-2 py-2">
                                            <x-ui.input type="number" step="0.01" min="0"
                                                        wire:model="lineas.{{ $i }}.stock"
                                                        class="text-right font-mono" />
                                            @error('lineas.'.$i.'.stock')
                                                <p class="mt-0.5 text-right text-[11px] text-red-600">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        @can('materiales.gestionar_precios')
                                            <td class="px-2 py-2">
                                                <x-ui.input type="number" step="0.01" min="0"
                                                            wire:model="lineas.{{ $i }}.precio_coste"
                                                            placeholder="0,00"
                                                            class="text-right font-mono" />
                                            </td>
                                            <td class="px-2 py-2">
                                                <x-ui.input type="number" step="0.01" min="0"
                                                            wire:model="lineas.{{ $i }}.precio_venta"
                                                            placeholder="0,00"
                                                            class="text-right font-mono" />
                                            </td>
                                        @endcan
                                        <td class="px-2 py-2">
                                            <div class="flex items-center justify-end gap-1">
                                                @if ($linea['id'])
                                                    <x-ui.icon-button as="a"
                                                                       href="{{ route('materiales.ver', $linea['id']) }}"
                                                                       wire:navigate
                                                                       size="sm"
                                                                       variant="info"
                                                                       icon="heroicon-o-arrow-top-right-on-square"
                                                                       tooltip="Ir al material" />
                                                @endif
                                                <x-ui.icon-button
                                                    wire:click="quitarLinea({{ $i }})"
                                                    wire:confirm="¿Quitar este material del pedido?"
                                                    size="sm"
                                                    variant="danger"
                                                    icon="heroicon-o-x-mark"
                                                    tooltip="Quitar del pedido" />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </form>
    </div>

    {{-- ═══ Tab: Consumo ═══ --}}
    @if ($pedido)
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
    @endif

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
                    ¿Eliminar el pedido <strong>{{ $pedido?->numero }}</strong>?
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Esta acción no se puede deshacer.
                </p>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
