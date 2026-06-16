<div class="space-y-4" x-data="{ tab: 'proyecto' }">
    <x-ui.page-header title="Ver proyecto" subtitle="Cabecera, equipo y recursos del proyecto.">
        <x-slot:actions>
            <div class="text-right">
                @if ($proyecto->codigo)
                    <div class="text-xl font-semibold text-slate-900 font-mono">{{ $proyecto->codigo }}</div>
                @endif
                @if ($proyecto->nombre)
                    <div class="text-sm text-slate-500">{{ $proyecto->nombre }}</div>
                @endif
            </div>
        </x-slot:actions>

        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('proyectos.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @can('update', $proyecto)
                <x-ui.button as="a" href="{{ route('proyectos.editar', $proyecto) }}" wire:navigate.fresh variant="neutral" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('create', App\Models\Proyecto::class)
                <x-ui.button as="a" href="{{ route('proyectos.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                    Nuevo
                </x-ui.button>
            @endcan
            @can('delete', $proyecto)
                <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                    Eliminar
                </x-ui.button>
            @endcan
        </x-slot:actionsLeft>
    </x-ui.page-header>

    <div>
        {{-- Tabs nav --}}
        <div class="flex items-end overflow-x-auto border-b border-slate-200 px-2 pt-1.5">
            <button type="button"
                    @click="tab = 'proyecto'"
                    :class="tab === 'proyecto'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                Proyecto
            </button>

            @foreach (array_values(array_filter([
                ['key' => 'trabajadores', 'label' => 'Trabajadores', 'count' => $this->trabajadoresProyecto->count()],
                ['key' => 'responsables', 'label' => 'Responsables', 'count' => $this->responsablesProyecto->count()],
                ['key' => 'conceptos',    'label' => 'Conceptos',    'count' => $this->conceptosProyecto->count()],
                \App\Support\Modulos::materialesAvanzado() ? ['key' => 'materiales', 'label' => 'Materiales', 'count' => $this->materialesProyecto->count()] : false,
            ])) as $t)
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

        {{-- ═══ Tab: Proyecto ═══ --}}
        <div x-show="tab === 'proyecto'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Código proyecto">
                    <x-ui.input :value="$proyecto->codigo" class="font-mono" readonly />
                </x-ui.field>

                <x-ui.field label="Nombre proyecto">
                    <x-ui.input :value="$proyecto->nombre" readonly />
                </x-ui.field>

                <x-ui.field label="Grupo">
                    <x-ui.input :value="$proyecto->tipoProyecto?->nombre ?? '—'" readonly />
                </x-ui.field>

                <x-ui.field label="Estado">
                    <x-ui.input :value="ucfirst($proyecto->estado)" readonly />
                </x-ui.field>

                <x-ui.field label="Fecha inicio">
                    <x-ui.input :value="$proyecto->fecha_inicio?->format('d/m/Y')" readonly />
                </x-ui.field>

                <x-ui.field label="Fecha fin">
                    <x-ui.input :value="$proyecto->fecha_fin?->format('d/m/Y')" readonly />
                </x-ui.field>

                <x-ui.field label="Cliente" class="md:col-span-2">
                    <x-ui.input :value="$proyecto->cliente?->nombre ?? '—'" readonly />
                </x-ui.field>

                <x-ui.field label="Descripción" class="md:col-span-2">
                    <x-ui.textarea :value="$proyecto->descripcion" rows="3" readonly />
                </x-ui.field>
            </div>
        </div>

        {{-- ═══ Tab: Trabajadores ═══ --}}
        <div x-show="tab === 'trabajadores'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $this->trabajadoresProyecto->count() }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Trabajadores asignados a este proyecto</p>
            </div>
            @if ($this->trabajadoresProyecto->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin trabajadores asignados.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Nombre</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->trabajadoresProyecto as $trab)
                            <tr wire:key="trab-ver-{{ $trab->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ trim($trab->nombre.' '.$trab->apellidos) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- ═══ Tab: Responsables ═══ --}}
        <div x-show="tab === 'responsables'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Responsables</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $this->responsablesProyecto->count() }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Responsables asignados a este proyecto</p>
            </div>
            @if ($this->responsablesProyecto->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin responsables asignados.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Nombre</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->responsablesProyecto as $resp)
                            <tr wire:key="resp-ver-{{ $resp->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ trim($resp->nombre.' '.$resp->apellidos) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- ═══ Tab: Conceptos ═══ --}}
        <div x-show="tab === 'conceptos'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Conceptos</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $this->conceptosProyecto->count() }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Conceptos de trabajo disponibles en los albaranes de este proyecto</p>
            </div>
            @if ($this->conceptosProyecto->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin conceptos asignados.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Concepto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->conceptosProyecto as $concepto)
                            <tr wire:key="concepto-ver-{{ $concepto->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ $concepto->nombre }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @if (\App\Support\Modulos::materialesAvanzado())
        {{-- ═══ Tab: Materiales ═══ --}}
        <div x-show="tab === 'materiales'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Materiales</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $this->materialesProyecto->count() }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Materiales disponibles para consumir en los albaranes de este proyecto</p>
            </div>
            @if ($this->materialesProyecto->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin materiales asignados.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Material</th>
                            <th class="w-28 px-4 py-2.5 text-right">Stock</th>
                            <th class="w-24 px-4 py-2.5">Unidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->materialesProyecto as $mat)
                            <tr wire:key="mat-ver-{{ $mat->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ $mat->descripcion }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $mat->stock, 2) }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $mat->unidad_medida }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @endif
    </div>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar proyecto"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará <strong>{{ $proyecto->nombre }}</strong> a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Albaranes y horas asociadas mantendrán la referencia hasta que el proyecto sea restaurado.
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
