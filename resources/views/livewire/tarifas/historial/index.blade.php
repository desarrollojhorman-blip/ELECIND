<div>
    <x-ui.page-header
        title="Tarifas — Historial"
        subtitle="Registro de auditoría: todos los cambios realizados en las tarifas (clientes y trabajadores). Solo lectura."
    />

    {{-- ── Filtros ──────────────────────────────────────────────── --}}
    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <x-ui.field label="Buscar">
                <x-ui.input
                    wire:model.live.debounce.400ms="buscar"
                    placeholder="Cliente, trabajador, motivo…"
                />
            </x-ui.field>

            <x-ui.field label="Tipo">
                <x-ui.select wire:model.live="filtroTipo">
                    <option value="">Todos</option>
                    <option value="cliente">Cliente</option>
                    <option value="trabajador">Trabajador</option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Atributo">
                <x-ui.select wire:model.live="filtroAtributo">
                    <option value="">Todos</option>
                    @foreach ($this->atributos as $a)
                        <option value="{{ $a->id }}">{{ $a->nombre_corto }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Cambiado por">
                <x-ui.select wire:model.live="filtroCambiadoPor">
                    <option value="">Todos</option>
                    @foreach ($this->usuarios as $u)
                        <option value="{{ $u->id }}">{{ trim($u->apellidos.' '.$u->nombre) }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        </div>

        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4">
            <x-ui.field label="Desde">
                <x-ui.date-input wireModel="fechaDesde" :value="$fechaDesde" :live="true" placeholder="dd/mm/aaaa" />
            </x-ui.field>

            <x-ui.field label="Hasta">
                <x-ui.date-input wireModel="fechaHasta" :value="$fechaHasta" :live="true" placeholder="dd/mm/aaaa" />
            </x-ui.field>
        </div>

        @if ($buscar || $filtroTipo || $filtroAtributo || $filtroCambiadoPor || $fechaDesde || $fechaHasta)
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
        {{ $registros->links() }}
    </div>

    {{-- ── Tabla ───────────────────────────────────────────────── --}}
    @php
        $fmt = function ($v): string {
            $v = (float) $v;
            if ($v == 0.0) {
                return '0';
            }

            return rtrim(rtrim(number_format($v, 3, ',', '.'), '0'), ',');
        };
    @endphp

    <x-ui.data-table :colspan="8" empty="No hay cambios registrados con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="created_at" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Fecha
                </x-ui.sortable-header>
                <x-ui.sortable-header column="tipo" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Tipo
                </x-ui.sortable-header>
                <x-ui.sortable-header>Referencia</x-ui.sortable-header>
                <x-ui.sortable-header column="atributo_id" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Atributo
                </x-ui.sortable-header>
                <x-ui.sortable-header column="importe_anterior" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="right">
                    Antes
                </x-ui.sortable-header>
                <x-ui.sortable-header column="importe_nuevo" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="right">
                    Después
                </x-ui.sortable-header>
                <x-ui.sortable-header align="right">Δ</x-ui.sortable-header>
                <x-ui.sortable-header column="cambiado_por" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Cambiado por
                </x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($registros as $r)
                @php
                    $delta = (float) $r->importe_nuevo - (float) $r->importe_anterior;
                @endphp
                <tr class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                        {{ $r->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if ($r->tipo === 'cliente')
                            <span class="inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">Cliente</span>
                        @else
                            <span class="inline-flex items-center rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Trabajador</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        {{ $referenciaTexto[$r->id] ?? '—' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                        {{ $r->atributo?->nombre_corto ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums text-slate-500 whitespace-nowrap">
                        {{ $fmt($r->importe_anterior) }}
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums font-medium text-slate-800 whitespace-nowrap">
                        {{ $fmt($r->importe_nuevo) }}
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap">
                        @if ($delta > 0)
                            <span class="text-emerald-700">+{{ $fmt($delta) }}</span>
                        @elseif ($delta < 0)
                            <span class="text-red-700">{{ $fmt($delta) }}</span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ $r->cambiadoPor ? trim($r->cambiadoPor->apellidos.' '.$r->cambiadoPor->nombre) : '—' }}
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>
</div>
