<div class="space-y-4">
    {{-- Cabecera --}}
    <x-ui.page-header :title="$proyecto->nombre" subtitle="Ver proyecto">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('proyectos.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @can('update', $proyecto)
                <x-ui.button as="a" href="{{ route('proyectos.editar', $proyecto) }}" wire:navigate.fresh variant="neutral" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('proyectos.ver')
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

    {{-- Datos del proyecto (solo lectura) --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
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

    {{-- Trabajadores --}}
    <div x-data="{ abierto: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <button type="button"
                x-on:click="abierto = !abierto"
                class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">
                Trabajadores
                <span class="ml-1 font-normal text-slate-400">({{ $this->trabajadoresProyecto->count() }})</span>
            </h2>
            <x-heroicon-o-chevron-down class="size-4 text-slate-400 transition-transform duration-150"
                                       x-bind:class="abierto ? 'rotate-180' : ''" />
        </button>
        <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
            @if ($this->trabajadoresProyecto->isEmpty())
                <p class="px-6 py-4 text-sm text-slate-500">Sin trabajadores asignados.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Nombre</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->trabajadoresProyecto as $trab)
                            <tr wire:key="trabajador-ver-{{ $trab->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ trim($trab->nombre.' '.$trab->apellidos) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Responsables --}}
    <div x-data="{ abierto: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <button type="button"
                x-on:click="abierto = !abierto"
                class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">
                Responsables
                <span class="ml-1 font-normal text-slate-400">({{ $this->responsablesProyecto->count() }})</span>
            </h2>
            <x-heroicon-o-chevron-down class="size-4 text-slate-400 transition-transform duration-150"
                                       x-bind:class="abierto ? 'rotate-180' : ''" />
        </button>
        <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
            @if ($this->responsablesProyecto->isEmpty())
                <p class="px-6 py-4 text-sm text-slate-500">Sin responsables asignados.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Nombre</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->responsablesProyecto as $resp)
                            <tr wire:key="responsable-ver-{{ $resp->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ trim($resp->nombre.' '.$resp->apellidos) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Conceptos --}}
    <div x-data="{ abierto: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <button type="button"
                x-on:click="abierto = !abierto"
                class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">
                Conceptos
                <span class="ml-1 font-normal text-slate-400">({{ $this->conceptosProyecto->count() }})</span>
            </h2>
            <x-heroicon-o-chevron-down class="size-4 text-slate-400 transition-transform duration-150"
                                       x-bind:class="abierto ? 'rotate-180' : ''" />
        </button>
        <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
            @if ($this->conceptosProyecto->isEmpty())
                <p class="px-6 py-4 text-sm text-slate-500">Sin conceptos asignados.</p>
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
    </div>

    {{-- Materiales --}}
    <div x-data="{ abierto: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <button type="button"
                x-on:click="abierto = !abierto"
                class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">
                Materiales
                <span class="ml-1 font-normal text-slate-400">({{ $this->materialesProyecto->count() }})</span>
            </h2>
            <x-heroicon-o-chevron-down class="size-4 text-slate-400 transition-transform duration-150"
                                       x-bind:class="abierto ? 'rotate-180' : ''" />
        </button>
        <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
            @if ($this->materialesProyecto->isEmpty())
                <p class="px-6 py-4 text-sm text-slate-500">Sin materiales asignados.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Material</th>
                            <th class="px-6 py-2.5">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->materialesProyecto as $mat)
                            <tr wire:key="material-ver-{{ $mat->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-700">{{ $mat->descripcion }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $mat->stock }} {{ $mat->unidad_medida }}</td>
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
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
