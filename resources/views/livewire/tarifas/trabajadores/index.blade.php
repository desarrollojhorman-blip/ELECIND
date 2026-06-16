<div>
    <x-ui.page-header
        title="Tarifas — Trabajadores"
        subtitle="Tasas (€/hora) que paga la empresa a cada trabajador por cada tipo de hora. Pulsa el botón editar de una fila para modificar."
    />

    <x-ui.flash />

    {{-- ── Filtros ──────────────────────────────────────────────── --}}
    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <x-ui.field label="Buscar trabajador">
                <x-ui.input
                    wire:model.live.debounce.400ms="buscar"
                    placeholder="Nombre, apellidos, nº empleado…"
                />
            </x-ui.field>

            <x-ui.field label="Rol">
                <x-ui.select wire:model.live="filtroRol">
                    <option value="">Todos</option>
                    @foreach ($this->rolesDisponibles as $rol)
                        <option value="{{ $rol->name }}">{{ $rol->etiqueta ?: $rol->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        </div>

        @if ($buscar || $filtroRol)
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
        {{ $usuarios->links() }}
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

    <x-ui.data-table :colspan="2 + $this->atributosHora->count() + 1" empty="No hay trabajadores que coincidan con la búsqueda.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="numero_empleado" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nº
                </x-ui.sortable-header>
                <x-ui.sortable-header column="apellidos" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                @foreach ($this->atributosHora as $attr)
                    <x-ui.sortable-header
                        column="{{ $attr->mapeo_tasa }}"
                        :current-column="$ordenColumna"
                        :current-direction="$ordenDireccion"
                        align="center"
                        class="whitespace-nowrap"
                        title="{{ $attr->nombre_largo }}"
                    >
                        {{ $attr->nombre_corto }}
                    </x-ui.sortable-header>
                @endforeach
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($usuarios as $u)
                @php
                    $enEdicion = isset($editando[$u->id]);
                @endphp
                <tr wire:key="user-row-{{ $u->id }}" @class([
                    'transition-colors hover:bg-slate-50',
                    'bg-amber-50' => $enEdicion,
                ])>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $u->numero_empleado ?: '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="font-medium text-slate-900">{{ trim($u->apellidos.' '.$u->nombre) ?: $u->username }}</div>
                        <div class="text-xs text-slate-400">{{ $u->username }}</div>
                    </td>
                    @foreach ($this->atributosHora as $attr)
                        @php $campo = $attr->mapeo_tasa; @endphp
                        <td class="px-2 py-2 text-center">
                            @if ($enEdicion)
                                <input
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    max="9999.999"
                                    wire:model="ediciones.{{ $u->id }}.{{ $campo }}"
                                    class="w-20 rounded border border-primary-300 bg-white px-1.5 py-1 text-right text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                                />
                            @else
                                <span class="block w-20 mx-auto tabular-nums text-right text-sm text-slate-700">
                                    {{ $fmt($u->{$campo}) }}
                                </span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if ($enEdicion)
                                @can('tarifas.editar_trabajadores')
                                    <button
                                        type="button"
                                        wire:click="guardar({{ $u->id }})"
                                        class="rounded p-1.5 bg-emerald-600 text-white hover:bg-emerald-700 transition-colors"
                                        title="Guardar cambios"
                                    >
                                        <x-heroicon-o-check class="size-4" />
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="cancelarEdicion({{ $u->id }})"
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
                                        wire:click="abrirHistorial({{ $u->id }})"
                                        class="rounded p-1.5 text-slate-600 hover:bg-slate-100"
                                        title="Ver historial"
                                    >
                                        <x-heroicon-o-clock class="size-4" />
                                    </button>
                                @endcan
                                @can('tarifas.editar_trabajadores')
                                    <button
                                        type="button"
                                        wire:click="editar({{ $u->id }})"
                                        class="rounded p-1.5 text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Editar tasas"
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

    {{-- ── Modal Historial contextual ──────────────────────────── --}}
    @php
        $tituloHist = 'Historial de tasas';
        if ($this->usuarioHistorial) {
            $tituloHist .= ' — '.trim($this->usuarioHistorial->apellidos.' '.$this->usuarioHistorial->nombre);
        }
    @endphp
    <x-ui.modal :show="$historialUserId !== null" :title="$tituloHist" close-action="cerrarHistorial" size="lg">
        <div class="max-h-96 overflow-y-auto">
            @php $items = $this->historialDelUsuario; @endphp
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
            <x-ui.button wire:click="cerrarHistorial" variant="secondary">Cerrar</x-ui.button>
        </x-slot>
    </x-ui.modal>
</div>
