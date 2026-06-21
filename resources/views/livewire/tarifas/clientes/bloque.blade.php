<div>
    <x-ui.flash />

    @php
        // Formato sin ceros sobrantes (coma como decimal, 0 → "0").
        $fmt = function ($v): string {
            $v = (float) $v;
            if ($v == 0.0) {
                return '0';
            }

            return rtrim(rtrim(number_format($v, 3, ',', '.'), '0'), ',');
        };
    @endphp

    <div class="mb-3">
        <p class="text-xs text-slate-400">
            Tarifas (€/hora y € flat) que se cobran a este cliente por tipo de proyecto.
            También se pueden editar desde
            <a href="{{ route('tarifas.clientes') }}" class="text-primary-600 underline" wire:navigate>Tarifas → Clientes</a>.
        </p>
    </div>

    <x-ui.data-table :colspan="1 + $this->atributos->count() + 1" empty="No hay tipos de proyecto activos.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header>Tipo proyecto</x-ui.sortable-header>
                @foreach ($this->atributos as $attr)
                    <th class="px-4 py-3 whitespace-nowrap text-center">
                        @if (! $soloLectura)
                            @can('tarifas.editar_clientes')
                                <button type="button" wire:click="abrirBulk({{ $attr->id }})"
                                        class="text-table-header-text/90 transition-colors hover:text-primary-600"
                                        style="text-transform: none;" title="{{ $attr->nombre_largo }} — Pulsa para aplicar a todos los tipos">
                                    {{ $attr->nombre_corto }}
                                </button>
                            @else
                                <span class="text-table-header-text" style="text-transform: none;">{{ $attr->nombre_corto }}</span>
                            @endcan
                        @else
                            <span class="text-table-header-text" style="text-transform: none;">{{ $attr->nombre_corto }}</span>
                        @endif
                    </th>
                @endforeach
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($tiposProyecto as $tp)
                @php
                    $tId = $tp->id;
                    $enEdicion = isset($editando[$tId]);
                @endphp
                <tr wire:key="cli-{{ $clienteId }}-tp-{{ $tId }}" @class([
                    'transition-colors hover:bg-slate-50',
                    'bg-amber-50' => $enEdicion,
                ])>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                        {{ $tp->nombre }}
                    </td>
                    @foreach ($this->atributos as $attr)
                        @php $importe = $matriz[$tId][$attr->id] ?? 0; @endphp
                        <td class="px-2 py-2 text-center">
                            @if ($enEdicion)
                                <input
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    max="9999.999"
                                    wire:model="ediciones.{{ $tId }}.{{ $attr->id }}"
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
                                    <button type="button"
                                            wire:click="guardar({{ $tId }})"
                                            class="rounded p-1.5 bg-emerald-600 text-white hover:bg-emerald-700 transition-colors"
                                            title="Guardar cambios">
                                        <x-heroicon-o-check class="size-4" />
                                    </button>
                                    <button type="button"
                                            wire:click="cancelarEdicion({{ $tId }})"
                                            class="rounded p-1.5 bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors"
                                            title="Cancelar edición">
                                        <x-heroicon-o-x-mark class="size-4" />
                                    </button>
                                @endcan
                            @else
                                @can('tarifas.historial_ver')
                                    <button type="button"
                                            wire:click="abrirHistorial({{ $tId }})"
                                            class="rounded p-1.5 text-slate-600 hover:bg-slate-100"
                                            title="Ver historial">
                                        <x-heroicon-o-clock class="size-4" />
                                    </button>
                                @endcan
                                @if (! $soloLectura)
                                    @can('tarifas.editar_clientes')
                                        <button type="button"
                                                wire:click="editar({{ $tId }})"
                                                class="rounded p-1.5 text-blue-600 hover:bg-blue-50 transition-colors"
                                                title="Editar tarifas">
                                            <x-heroicon-o-pencil-square class="size-4" />
                                        </button>
                                    @endcan
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    {{-- ── Modal Bulk por atributo ───────────────────────────── --}}
    @php $atributoBulk = $this->atributos->firstWhere('id', $bulkAtributoId); @endphp
    <x-ui.modal :show="$bulkAtributoId !== null" title="Aplicar a todos los tipos" close-action="cerrarBulk" size="sm">
        <p class="mb-4 text-sm text-slate-600">
            Se cambiará <strong>{{ $atributoBulk?->nombre_corto }}</strong> en todos los tipos de proyecto activos de este cliente.
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
            <x-ui.button wire:click="aplicarBulk" variant="primary">Aplicar</x-ui.button>
            <x-ui.button wire:click="cerrarBulk" variant="secondary">Cancelar</x-ui.button>
        </x-slot>
    </x-ui.modal>

    {{-- ── Modal Historial contextual ──────────────────────────── --}}
    @php
        $tituloHist = 'Historial';
        if ($tipoActual) {
            $tituloHist .= ' — '.$tipoActual->nombre;
        }
    @endphp
    <x-ui.modal :show="$historialTipoProyectoId !== null" :title="$tituloHist" close-action="cerrarHistorial" size="lg">
        <div class="max-h-96 overflow-y-auto">
            @php $items = $this->historialDelTipo; @endphp
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
