<div>
    <x-ui.page-header
        title="Tarifas — Clientes"
        subtitle="Importes (€/hora y € flat) que se cobran al cliente por cada tipo de hora y plus. Pulsa el botón editar de una fila para modificar."
    />

    <x-ui.flash />

    {{-- ── Filtros ──────────────────────────────────────────────── --}}
    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <x-ui.field label="Buscar">
                <x-ui.input
                    wire:model.live.debounce.400ms="buscar"
                    placeholder="Código, cliente o tipo de proyecto…"
                />
            </x-ui.field>

            <x-ui.field label="Cliente">
                <x-ui.select wire:model.live="filtroCliente">
                    <option value="">Todos</option>
                    @foreach ($this->clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Tipo de proyecto">
                <x-ui.select wire:model.live="filtroTipoProyecto">
                    <option value="">Todos</option>
                    @foreach ($this->tiposProyecto as $tp)
                        <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        </div>

        @if ($buscar || $filtroCliente || $filtroTipoProyecto)
            <div class="mt-3 flex justify-end">
                <button wire:click="limpiarFiltros"
                        class="text-xs text-primary-600 underline hover:text-primary-800">
                    Limpiar filtros
                </button>
            </div>
        @endif
    </div>

    {{-- ── Filas por página + paginación arriba ─────────────────── --}}
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
        {{ $paginador->links() }}
    </div>

    {{-- ── Tabla ───────────────────────────────────────────────── --}}
    @php
        // Formato para mostrar (coma como separador decimal, sin ceros sobrantes).
        $fmt = function ($v): string {
            $v = (float) $v;
            if ($v == 0.0) {
                return '0';
            }

            return rtrim(rtrim(number_format($v, 3, ',', '.'), '0'), ',');
        };
    @endphp
    <x-ui.data-table :colspan="3 + $this->atributos->count() + 1" empty="No hay combinaciones que coincidan con los filtros.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="codigo_cliente" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Código
                </x-ui.sortable-header>
                <x-ui.sortable-header column="cliente" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Cliente
                </x-ui.sortable-header>
                <x-ui.sortable-header column="tipo_proyecto" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Grupo proyecto
                </x-ui.sortable-header>
                @foreach ($this->atributos as $attr)
                    <th class="px-4 py-3 whitespace-nowrap text-center">
                        @can('tarifas.editar_clientes')
                            <button type="button" wire:click="abrirBulk({{ $attr->id }})"
                                    class="text-table-header-text/90 transition-colors hover:text-primary-600"
                                    style="text-transform: none;" title="{{ $attr->nombre_largo }} — Pulsa para aplicar a todas las filas filtradas">
                                {{ $attr->nombre_corto }}
                            </button>
                        @else
                            <span class="text-table-header-text" style="text-transform: none;" title="{{ $attr->nombre_largo }}">{{ $attr->nombre_corto }}</span>
                        @endcan
                    </th>
                @endforeach
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($paginador as $combo)
                @php
                    $cId = $combo->cliente_id;
                    $tId = $combo->tipo_proyecto_id;
                    $key = "{$cId}_{$tId}";
                    $enEdicion = isset($editando[$key]);
                @endphp
                <tr wire:key="combo-{{ $key }}" @class([
                    'transition-colors hover:bg-slate-50',
                    'bg-amber-50' => $enEdicion,
                ])>
                    <td class="px-4 py-3 font-mono text-slate-700 whitespace-nowrap">
                        {{ $combo->codigo_cliente ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ $combo->cliente_nombre }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $combo->tipo_proyecto_nombre }}
                    </td>
                    @foreach ($this->atributos as $attr)
                        @php
                            $importe = $matriz[$cId][$tId][$attr->id] ?? 0;
                        @endphp
                        <td class="px-2 py-2 text-center">
                            @if ($enEdicion)
                                <input
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    max="9999.999"
                                    wire:model="ediciones.{{ $key }}.{{ $attr->id }}"
                                    class="w-20 rounded border border-primary-300 bg-white px-1.5 py-1 text-right text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                                />
                            @else
                                <span class="block w-20 mx-auto tabular-nums text-right text-sm text-slate-700">
                                    {{ $fmt($importe) }}
                                </span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if ($enEdicion)
                                @can('tarifas.editar_clientes')
                                    <button
                                        type="button"
                                        wire:click="guardar({{ $cId }}, {{ $tId }})"
                                        class="rounded p-1.5 bg-emerald-600 text-white hover:bg-emerald-700 transition-colors"
                                        title="Guardar cambios"
                                    >
                                        <x-heroicon-o-check class="size-4" />
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="cancelarEdicion({{ $cId }}, {{ $tId }})"
                                        class="rounded p-1.5 bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors"
                                        title="Cancelar edición"
                                    >
                                        <x-heroicon-o-x-mark class="size-4" />
                                    </button>
                                @endcan
                            @else
                                @can('tarifas.historial_ver')
                                    <button
                                        type="button"
                                        wire:click="abrirHistorial({{ $cId }}, {{ $tId }})"
                                        class="rounded p-1.5 text-slate-600 hover:bg-slate-100"
                                        title="Ver historial"
                                    >
                                        <x-heroicon-o-clock class="size-4" />
                                    </button>
                                @endcan
                                @can('tarifas.editar_clientes')
                                    <button
                                        type="button"
                                        wire:click="editar({{ $cId }}, {{ $tId }})"
                                        class="rounded p-1.5 text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Editar tarifas"
                                    >
                                        <x-heroicon-o-pencil-square class="size-4" />
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    {{-- ── Modal Bulk por atributo ───────────────────────────── --}}
    @php $atributoBulk = $this->atributos->firstWhere('id', $bulkAtributoId); @endphp
    <x-ui.modal :show="$bulkAtributoId !== null" title="Aplicar a filas filtradas" close-action="cerrarBulk" size="sm">
        <p class="mb-4 text-sm text-slate-600">
            Se cambiará <strong>{{ $atributoBulk?->nombre_corto }}</strong> en todas las combinaciones activas filtradas.
        </p>
        <x-ui.field label="Importe (€)" :error="$errors->first('bulkValor')">
            <x-ui.input
                type="number"
                step="0.001"
                min="0"
                max="9999.999"
                wire:model="bulkValor"
                wire:keydown.enter="aplicarBulk"
                placeholder="0"
                autofocus
            />
        </x-ui.field>

        <x-slot name="footer">
            <x-ui.button wire:click="cerrarBulk" variant="neutral">Cancelar</x-ui.button>
            <x-ui.button wire:click="aplicarBulk" variant="info">Guardar</x-ui.button>
        </x-slot>
    </x-ui.modal>

    {{-- ── Modal Historial contextual ──────────────────────────── --}}
    @php
        $tituloHist = 'Historial';
        if ($historialCombinacion) {
            $clienteNombre = \App\Models\Cliente::find($historialCombinacion['cliente_id'])?->nombre ?? '?';
            $tipoNombre = \App\Models\TiposProyecto::find($historialCombinacion['tipo_proyecto_id'])?->nombre ?? '?';
            $tituloHist .= " — $clienteNombre / $tipoNombre";
        }
    @endphp
    <x-ui.modal :show="$historialCombinacion !== null" :title="$tituloHist" close-action="cerrarHistorial" size="lg">
        <div class="max-h-96 overflow-y-auto">
            @php $items = $this->historialDeCombinacion; @endphp
            @if ($items->isEmpty())
                <p class="py-6 text-center text-sm text-slate-500">Sin cambios registrados.</p>
            @else
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Atributo</th>
                            <th class="px-3 py-2 text-right">Antes</th>
                            <th class="px-3 py-2 text-right">Después</th>
                            <th class="px-3 py-2 text-left">Por</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($items as $h)
                            <tr>
                                <td class="px-3 py-2 text-xs text-slate-500">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $h->atributo?->nombre_corto ?? '—' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ $fmt($h->importe_anterior) }}</td>
                                <td class="px-3 py-2 text-right tabular-nums font-medium text-slate-800">{{ $fmt($h->importe_nuevo) }}</td>
                                <td class="px-3 py-2 text-xs text-slate-500">
                                    {{ $h->cambiadoPor ? trim($h->cambiadoPor->apellidos.' '.$h->cambiadoPor->nombre) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <x-slot name="footer">
            <x-ui.button wire:click="cerrarHistorial" variant="neutral">Cerrar</x-ui.button>
        </x-slot>
    </x-ui.modal>
</div>
