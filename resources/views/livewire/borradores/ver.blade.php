<div class="space-y-4" x-data="{ tab: 'borrador' }">
    <x-ui.page-header :title="'Borrador '.$borrador->numero_borrador" subtitle="Detalle del borrador.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('borradores.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @can('update', $borrador)
                <x-ui.button as="a" href="{{ route('borradores.editar', $borrador) }}" wire:navigate.fresh variant="neutral" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('create', App\Models\Borrador::class)
                <x-ui.button as="a" href="{{ route('borradores.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                    Nuevo
                </x-ui.button>
            @endcan
            @can('convertir', $borrador)
                <x-ui.button variant="primary" wire:click="abrirConfirmarConvertir" icon="heroicon-o-arrow-right-circle">
                    Convertir a albarán
                </x-ui.button>
            @endcan
            @can('delete', $borrador)
                <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                    Eliminar
                </x-ui.button>
            @endcan
        </x-slot:actionsLeft>
    </x-ui.page-header>

    {{-- Aviso si ya está convertido --}}
    @if ($borrador->estaConvertido() && $borrador->albaranConvertido)
        <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <x-heroicon-o-check-circle class="size-5 shrink-0" />
            <span>
                Este borrador fue convertido al albarán
                <a href="{{ route('albaranes.editar', $borrador->albaranConvertido) }}" wire:navigate class="font-semibold underline">
                    {{ $borrador->albaranConvertido->numero }}
                </a>.
            </span>
        </div>
    @endif

    <div>
        {{-- Tabs nav --}}
        <div class="flex items-end overflow-x-auto border-b border-slate-200 px-2 pt-1.5">
            <button type="button"
                    @click="tab = 'borrador'"
                    :class="tab === 'borrador'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                Borrador
            </button>

            @foreach ([
                ['key' => 'trabajadores', 'label' => 'Trabajadores', 'count' => $borrador->lineasPersonal->count()],
                ['key' => 'materiales',   'label' => 'Materiales',   'count' => $borrador->lineasMaterial->count()],
            ] as $t)
                <button type="button"
                        @click="tab = '{{ $t['key'] }}'"
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

        {{-- ═══ Tab: Borrador ═══ --}}
        <div x-show="tab === 'borrador'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">

                <x-ui.field label="Nº Borrador">
                    <x-ui.input :value="$borrador->numero_borrador" class="font-mono" readonly />
                </x-ui.field>

                <x-ui.field label="Estado">
                    <div class="flex h-9 items-center">
                        @if ($borrador->estaConvertido())
                            <x-ui.badge tone="success" dot>Convertido</x-ui.badge>
                        @else
                            <x-ui.badge tone="warning" dot>Pendiente</x-ui.badge>
                        @endif
                    </div>
                </x-ui.field>

                <x-ui.field label="Proyecto" class="md:col-span-2">
                    <x-ui.input :value="$borrador->proyectoNombre()" readonly />
                </x-ui.field>

                <x-ui.field label="Cliente" class="md:col-span-2">
                    <x-ui.input :value="$borrador->clienteNombre()" readonly />
                </x-ui.field>

                <x-ui.field label="Concepto">
                    <x-ui.input :value="$borrador->conceptoNombre()" readonly />
                </x-ui.field>

                <x-ui.field label="Responsable">
                    <x-ui.input :value="$borrador->responsable ? trim($borrador->responsable->nombre.' '.$borrador->responsable->apellidos) : '—'" readonly />
                </x-ui.field>

                <x-ui.field label="Fecha">
                    <x-ui.input :value="$borrador->fecha?->format('d/m/Y')" readonly />
                </x-ui.field>

                <x-ui.field label="Tipo de jornada">
                    <x-ui.input :value="match($borrador->tipo_hora) {
                        'laboral'        => 'Laboral',
                        'laboral_noche'  => 'Laboral (noche)',
                        'festivo'        => 'Festivo',
                        'festivo_noche'  => 'Festivo (noche)',
                        default          => $borrador->tipo_hora,
                    }" readonly />
                </x-ui.field>

                <x-ui.field label="Observaciones" class="md:col-span-2">
                    <x-ui.textarea :value="$borrador->observaciones" rows="3" readonly />
                </x-ui.field>

                <x-ui.field label="Creado por">
                    <x-ui.input :value="$borrador->creador ? trim($borrador->creador->nombre.' '.$borrador->creador->apellidos) : '—'" readonly />
                </x-ui.field>
            </div>
        </div>

        {{-- ═══ Tab: Trabajadores ═══ --}}
        <div x-show="tab === 'trabajadores'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                <p class="mt-0.5 text-xs text-slate-400">Trabajadores asignados a este parte</p>
            </div>
            @if ($borrador->lineasPersonal->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin trabajadores asignados.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Trabajador</th>
                            <th class="w-28 px-4 py-2.5 text-right">Horas</th>
                            <th class="w-28 px-4 py-2.5 text-right">H. Extra</th>
                            <th class="w-28 px-4 py-2.5 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($borrador->lineasPersonal as $linea)
                            <tr wire:key="ver-personal-{{ $linea->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ $linea->trabajadorNombre() }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $linea->horas, 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $linea->horas_extra, 2) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-slate-700">{{ number_format((float) $linea->horas + (float) $linea->horas_extra, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- ═══ Tab: Materiales ═══ --}}
        <div x-show="tab === 'materiales'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Materiales</span>
                <p class="mt-0.5 text-xs text-slate-400">Materiales utilizados en este parte</p>
            </div>
            @if ($borrador->lineasMaterial->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin materiales asignados.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Material</th>
                            <th class="w-28 px-4 py-2.5 text-right">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($borrador->lineasMaterial as $linea)
                            <tr wire:key="ver-material-{{ $linea->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ $linea->materialNombre() }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $linea->cantidad, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar borrador"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <p class="text-sm text-slate-700">
                ¿Eliminar el borrador <strong>{{ $borrador->numero_borrador }}</strong>? Se enviará a la papelera.
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">Eliminar</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar conversión --}}
    <x-ui.modal
        :show="$confirmarConvertir"
        title="Convertir a albarán"
        close-action="cancelarConvertir"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600">
                <x-heroicon-o-arrow-right-circle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Se creará un albarán oficial a partir de este borrador.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Las líneas con texto libre sin resolver (sin FK) no se copiarán al albarán.
                    El borrador quedará marcado como <strong>convertido</strong>.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarConvertir">Cancelar</x-ui.button>
            <x-ui.button variant="primary" wire:click="convertirAAlbaran" icon="heroicon-o-arrow-right-circle">
                Convertir
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
