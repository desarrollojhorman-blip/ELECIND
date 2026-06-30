<div class="space-y-4" x-data="{ tab: 'parte' }">
    <x-ui.page-header title="Ver parte" :id-badge="$parte->id" subtitle="Detalle del parte y sus líneas.">
        <x-slot:actions>
            <div class="text-right">
                <div class="text-xl font-semibold text-slate-900 font-mono">{{ $parte->numero }}</div>
                <div class="text-sm text-slate-500">
                    {{ ucfirst($parte->estado) }}
                    @if ($parte->tieneAlbaran())
                        · <a href="{{ route('albaranes.ver', $parte->albaran_id) }}" wire:navigate class="text-blue-600 underline">
                            {{ $parte->albaran?->numero ?? 'Albarán generado' }}
                        </a>
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
            @if ($parte->tieneAlbaran())
                <x-ui.button as="a" href="{{ route('albaranes.ver', $parte->albaran_id) }}" wire:navigate
                    variant="info" icon="heroicon-o-arrow-top-right-on-square">
                    Ver albarán
                </x-ui.button>
            @endif
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
    <div class="flex items-end overflow-x-auto border-b border-slate-200 px-2 pt-1.5">
        @foreach ([
            ['key' => 'parte',        'label' => 'Parte',        'count' => null],
            ['key' => 'trabajadores', 'label' => 'Trabajadores', 'count' => $parte->lineasPersonal->count()],
            ['key' => 'materiales',   'label' => 'Materiales',   'count' => $parte->lineasMaterial->count()],
            ['key' => 'costes',       'label' => 'Costes/Gastos', 'count' => null],
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

    {{-- ═══ Tab: Parte ═══ --}}
    <div x-show="tab === 'parte'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field label="Nº Parte">
                <x-ui.input :value="$parte->numero" class="font-mono" readonly />
            </x-ui.field>
            <x-ui.field label="Proyecto">
                <x-ui.input :value="($parte->proyecto_codigo_snapshot ?? '').' · '.($parte->proyecto_nombre_snapshot ?? '—')" readonly />
            </x-ui.field>
            <x-ui.field label="Cliente">
                <x-ui.input :value="$parte->cliente_nombre_snapshot ?? '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Concepto">
                <x-ui.input :value="$parte->concepto_nombre_snapshot ?? '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Responsable">
                <x-ui.input :value="trim(($parte->responsable_apellidos_snapshot ?? '').' '.($parte->responsable_nombre_snapshot ?? '')) ?: '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Creador">
                <x-ui.input :value="trim(($parte->creador_apellidos_snapshot ?? '').' '.($parte->creador_nombre_snapshot ?? '')) ?: '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Tipo de jornada">
                <x-ui.input :value="$parte->tipo_hora?->etiqueta() ?? '—'" readonly />
            </x-ui.field>
            <x-ui.field label="Fecha">
                <x-ui.input :value="$parte->fecha?->format('d/m/Y') ?? '—'" readonly />
            </x-ui.field>

            <x-ui.field label="Observaciones" class="md:col-span-2">
                <x-ui.textarea :value="$parte->observaciones ?? '—'" readonly rows="3" />
            </x-ui.field>
        </div>
    </div>

    {{-- ═══ Tab: Trabajadores ═══ --}}
    <div x-show="tab === 'trabajadores'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        @if ($parte->lineasPersonal->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-slate-400">No hay trabajadores en este parte.</div>
        @else
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <th class="px-6 py-2.5">Trabajador</th>
                        <th class="px-6 py-2.5 text-right">Horas</th>
                        <th class="px-6 py-2.5 text-right">Horas extra</th>
                        <th class="px-6 py-2.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($parte->lineasPersonal as $linea)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-2 text-slate-700">
                                {{ trim(($linea->trabajador?->apellidos ?? $linea->trabajador_apellidos_snapshot ?? '').' '.($linea->trabajador?->nombre ?? $linea->trabajador_nombre_snapshot ?? '')) ?: '—' }}
                            </td>
                            <td class="px-6 py-2 text-right tabular-nums">{{ number_format((float) $linea->horas, 2, ',', '.') }}</td>
                            <td class="px-6 py-2 text-right tabular-nums">{{ number_format((float) $linea->horas_extra, 2, ',', '.') }}</td>
                            <td class="px-6 py-2 text-right tabular-nums font-medium">{{ number_format((float) $linea->horas + (float) $linea->horas_extra, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ═══ Tab: Materiales ═══ --}}
    <div x-show="tab === 'materiales'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        @if ($parte->lineasMaterial->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-slate-400">No hay materiales en este parte.</div>
        @else
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <th class="px-6 py-2.5">Material</th>
                        <th class="px-6 py-2.5">Familia</th>
                        <th class="px-6 py-2.5 text-right">Cantidad</th>
                        <th class="px-6 py-2.5">Unidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($parte->lineasMaterial as $linea)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-2 text-slate-700">{{ $linea->material?->descripcion ?? $linea->material_descripcion_snapshot ?? '—' }}</td>
                            <td class="px-6 py-2 text-xs text-slate-500">{{ $linea->material_familia_snapshot ?? '—' }}</td>
                            <td class="px-6 py-2 text-right tabular-nums">{{ number_format((float) $linea->cantidad, 2, ',', '.') }}</td>
                            <td class="px-6 py-2 text-xs text-slate-500">{{ $linea->material?->unidad_medida ?? $linea->material_unidad_medida_snapshot ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ═══ Tab: Costes/Gastos (solo lectura) ═══ --}}
    <div x-show="tab === 'costes'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <x-costes-resumen :doc="$parte" />
    </div>
    </div>

    {{-- Modal eliminar --}}
    <x-ui.modal :show="$confirmarEliminarId !== null" title="Eliminar parte" close-action="cancelarEliminar" size="sm">
        <p class="text-sm text-slate-700">¿Eliminar el parte <strong class="font-mono">{{ $parte->numero }}</strong>?</p>
        <p class="mt-1 text-xs text-slate-500">Esta acción se puede revertir desde papelera.</p>
        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar">Eliminar</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
