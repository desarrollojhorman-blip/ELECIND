<div class="space-y-4" x-data="{ tab: 'parte' }">
    <x-ui.page-header title="Ver parte" :id-badge="$parte->id" subtitle="Detalle del parte y sus líneas.">
        <x-slot:actions>
            <div class="text-right">
                <div class="text-xl font-semibold text-slate-900 font-mono">{{ $parte->codigo }}</div>
                <div class="text-sm text-slate-500">
                    {{ ucfirst($parte->estado) }}
                    @if ($parte->es_albaran)
                        · <span class="text-blue-600">Albarán</span>
                    @endif
                </div>
            </div>
        </x-slot:actions>

        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('partes.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @can('update', $parte)
                <x-ui.button as="a" href="{{ route('partes.editar', $parte) }}" wire:navigate variant="neutral" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('create', App\Models\Parte::class)
                <x-ui.button as="a" href="{{ route('partes.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                    Nuevo
                </x-ui.button>
            @endcan
            @can('delete', $parte)
                <x-ui.button variant="danger" icon="heroicon-o-trash" wire:click="confirmarEliminar">
                    Eliminar
                </x-ui.button>
            @endcan
        </x-slot:actionsLeft>
    </x-ui.page-header>

    <x-ui.flash />

    {{-- Tabs --}}
    <div>
    <div class="flex items-end border-b border-slate-200 px-2 pt-1.5">
        @foreach ([
            ['key' => 'parte',  'label' => 'Parte',  'count' => null],
            ['key' => 'lineas', 'label' => 'Líneas', 'count' => $parte->lineasPersonal->count()],
        ] as $t)
            <button type="button" @click="tab = '{{ $t['key'] }}'"
                    :class="tab === '{{ $t['key'] }}'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                {{ $t['label'] }}
                @if ($t['count'])
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600">
                        {{ $t['count'] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- ═══ Tab: Parte ═══ ─────────────────────────────────────── --}}
    <div x-show="tab === 'parte'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Cabecera</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field label="Operario">
                <x-ui.input :value="$parte->operario_nombre_snapshot ?? '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Proyecto">
                <x-ui.input :value="($parte->proyecto_codigo_snapshot ?? '').' · '.($parte->proyecto_nombre_snapshot ?? '—')" readonly />
            </x-ui.field>
            <x-ui.field label="Cliente">
                <x-ui.input :value="$parte->cliente_nombre_snapshot ?? '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Tipo de proyecto">
                <x-ui.input :value="$parte->tipo_proyecto_nombre_snapshot ?? '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Fecha">
                <x-ui.input :value="$parte->fecha?->format('d/m/Y') ?? '—'" readonly />
            </x-ui.field>
            <x-ui.field label="¿Genera albarán?">
                <x-ui.input :value="$parte->es_albaran ? 'Sí' : 'No'" readonly />
            </x-ui.field>
            <x-ui.field label="Hora inicio">
                <x-ui.input :value="$parte->hora_inicio ? substr($parte->hora_inicio, 0, 5) : '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Hora fin">
                <x-ui.input :value="$parte->hora_fin ? substr($parte->hora_fin, 0, 5) : '—'" readonly />
            </x-ui.field>
        </div>

        <h3 class="mt-6 mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Observaciones</h3>
        <x-ui.field>
            <x-ui.textarea :value="$parte->observaciones ?? '—'" readonly rows="3" />
        </x-ui.field>

        <h3 class="mt-6 mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Totales</h3>
        @php
            $fmt = fn ($v) => number_format((float) $v, 2, ',', '.');
        @endphp
        <div class="grid gap-4 md:grid-cols-4">
            <x-ui.field label="Horas totales">
                <x-ui.input :value="$fmt($parte->horasTotales())" readonly />
            </x-ui.field>
            <x-ui.field label="Facturación">
                <x-ui.input :value="$fmt($parte->facturacionTotal()).' €'" readonly />
            </x-ui.field>
            <x-ui.field label="Coste">
                <x-ui.input :value="$fmt($parte->costeTotal()).' €'" readonly />
            </x-ui.field>
            <x-ui.field label="Margen">
                <x-ui.input :value="$fmt($parte->margenTotal()).' €'" readonly />
            </x-ui.field>
        </div>
    </div>

    {{-- ═══ Tab: Líneas ═══ ────────────────────────────────────── --}}
    <div x-show="tab === 'lineas'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        @if ($parte->lineasPersonal->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-slate-400">
                Sin líneas registradas.
            </div>
        @else
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-4 py-2 text-left">Trabajador</th>
                        <th class="px-4 py-2 text-left">Atributo</th>
                        <th class="px-4 py-2 text-right">Cantidad</th>
                        <th class="px-4 py-2 text-right">Tarifa €/h</th>
                        <th class="px-4 py-2 text-right">Tasa €/h</th>
                        <th class="px-4 py-2 text-right">Facturación</th>
                        <th class="px-4 py-2 text-right">Coste</th>
                        <th class="px-4 py-2 text-left">Motivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($parte->lineasPersonal as $linea)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2 text-slate-700">
                                {{ trim(($linea->trabajador_apellidos_snapshot ?? '').' '.($linea->trabajador_nombre_snapshot ?? '')) ?: '—' }}
                            </td>
                            <td class="px-4 py-2 text-slate-700">{{ $linea->atributo_nombre_snapshot ?? '—' }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $linea->cantidad, 2, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $linea->tarifa_snapshot, 4, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $linea->tasa_snapshot, 3, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right tabular-nums font-medium">{{ number_format((float) $linea->facturacion_snapshot, 2, ',', '.') }} €</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $linea->coste_snapshot, 2, ',', '.') }} €</td>
                            <td class="px-4 py-2 text-xs text-slate-500">{{ $linea->motivo_ajuste ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    </div>

    {{-- Modal eliminar --}}
    <x-ui.modal :show="$confirmarEliminarId !== null" title="Eliminar parte" close-action="cancelarEliminar" size="sm">
        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    ¿Eliminar el parte <strong class="font-mono">{{ $parte->codigo }}</strong>?
                </p>
                <p class="mt-1 text-sm text-slate-500">Esta acción se puede revertir desde papelera.</p>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar">Eliminar</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
