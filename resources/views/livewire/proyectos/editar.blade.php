<div class="space-y-4" x-data="{ tab: 'proyecto' }">
    <x-ui.page-header :title="$titulo" subtitle="Cabecera, equipo y recursos del proyecto.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('proyectos.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($proyecto)
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
            @endif
        </x-slot:actionsLeft>

        <x-slot:actionsRight>
            <x-ui.button variant="neutral" wire:click="deshacer" wire:loading.attr="disabled" wire:target="deshacer">
                <x-heroicon-o-arrow-uturn-left wire:loading.remove wire:target="deshacer" class="size-4" />
                <svg wire:loading wire:target="deshacer" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="deshacer">Deshacer</span>
                <span wire:loading wire:target="deshacer">Deshaciendo…</span>
            </x-ui.button>
            <x-ui.button variant="info" type="submit" form="form-proyecto" wire:loading.attr="disabled" wire:target="guardar">
                <x-heroicon-o-arrow-down-tray wire:loading.remove wire:target="guardar" class="size-4" />
                <svg wire:loading wire:target="guardar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actionsRight>
    </x-ui.page-header>

    {{-- Tabs + contenido --}}
    @php $modoCrear = $proyecto === null; @endphp
    <div>
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
            ['key' => 'trabajadores',  'label' => 'Trabajadores',  'count' => $proyecto ? $this->trabajadoresProyecto->count() : null],
            ['key' => 'responsables',  'label' => 'Responsables',  'count' => $proyecto ? $this->responsablesProyecto->count() : null],
            ['key' => 'conceptos',     'label' => 'Conceptos',     'count' => $proyecto ? $this->conceptosProyecto->count() : null],
            \App\Support\Modulos::materialesAvanzado() ? ['key' => 'materiales', 'label' => 'Materiales', 'count' => $proyecto ? $this->materialesProyecto->count() : null] : false,
            ['key' => 'albaranes',     'label' => 'Albaranes',     'count' => $proyecto ? $this->albaranesDelProyecto->count() : null],
        ])) as $t)
            @if ($modoCrear)
                <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                      title="Guarda primero el proyecto para acceder a esta sección">
                    <x-heroicon-o-lock-closed class="size-3" />
                    {{ $t['label'] }}
                </span>
            @else
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
            @endif
        @endforeach
    </div>

    {{-- ═══ Tab: Proyecto ═══ --}}
    <form wire:submit="guardar" id="form-proyecto" autocomplete="off">
        <div x-show="tab === 'proyecto'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                {{-- Fila 1: Código | Grupo --}}
                <x-ui.field label="Código proyecto" required :error="$errors->first('form.codigo')"
                            hint="Generado automáticamente. Puedes modificarlo si lo necesitas.">
                    <x-ui.input wire:model="form.codigo" class="font-mono" autofocus />
                </x-ui.field>

                <x-ui.field label="Grupo" :error="$errors->first('form.tipo_proyecto_id')">
                    <x-ui.searchable-select
                        wire-model="selectorGrupo"
                        :value="$selectorGrupo ?: null"
                        :options="$this->tiposDisponibles->map(fn($t) => ['value' => $t->id, 'label' => $t->nombre])->all()"
                        placeholder="— Sin grupo —"
                    />
                </x-ui.field>

                {{-- Fila 2: Nombre | Cliente --}}
                <x-ui.field label="Nombre proyecto" required :error="$errors->first('form.nombre')">
                    <x-ui.input wire:model="form.nombre" />
                </x-ui.field>

                <x-ui.field label="Cliente" required :error="$errors->first('form.cliente_id')">
                    <x-ui.searchable-select
                        wire-model="form.cliente_id"
                        :value="$form->cliente_id"
                        :options="$this->clientesDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->codigo_cliente.' · '.$c->nombre])->all()"
                        placeholder="— Selecciona cliente —"
                    />
                </x-ui.field>

                {{-- Fila 3: Fecha inicio | Fecha fin --}}
                <x-ui.field label="Fecha inicio" :error="$errors->first('form.fecha_inicio')">
                    <x-ui.date-input wireModel="form.fecha_inicio" :value="$form->fecha_inicio" placeholder="dd/mm/aaaa" />
                </x-ui.field>

                <x-ui.field label="Fecha fin" :error="$errors->first('form.fecha_fin')">
                    <x-ui.date-input wireModel="form.fecha_fin" :value="$form->fecha_fin" placeholder="dd/mm/aaaa" />
                </x-ui.field>

                {{-- Fila 4: Estado (media columna) --}}
                <x-ui.field label="Estado" required :error="$errors->first('form.estado')">
                    <x-ui.select wire:model="form.estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="cerrado">Cerrado</option>
                    </x-ui.select>
                </x-ui.field>

                {{-- Fila 5: Observaciones --}}
                <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')" class="md:col-span-2">
                    <x-ui.textarea wire:model="form.descripcion" rows="3" />
                </x-ui.field>

                @if ($proyecto === null)
                    <p class="md:col-span-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        Para asignar responsables y trabajadores, primero crea este proyecto y luego edítalo.
                    </p>
                @endif
            </div>

        </div>
    </form>

    {{-- ═══ Tab: Trabajadores ═══ --}}
    <div x-show="tab === 'trabajadores'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                    @if ($proyecto)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $this->trabajadoresProyecto->count() }}
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Asigna trabajadores a este proyecto</p>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <x-ui.searchable-select
                        wire:key="trabajador-select-{{ $trabajadorSelectKey }}"
                        wire-model="trabajadorAAgregar"
                        :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim(($u->numero_empleado ? $u->numero_empleado.' · ' : '').trim($u->nombre.' '.$u->apellidos))])"
                        placeholder="— Selecciona trabajador —"
                    />
                </div>
                <x-ui.button type="button" variant="success" wire:click="agregarTrabajador"
                             wire:loading.attr="disabled" wire:target="agregarTrabajador">
                    <x-heroicon-o-plus wire:loading.remove wire:target="agregarTrabajador" class="size-4" />
                    <svg wire:loading wire:target="agregarTrabajador" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="agregarTrabajador">Añadir</span>
                    <span wire:loading wire:target="agregarTrabajador">Añadiendo…</span>
                </x-ui.button>
            </div>
            @error('trabajadorAAgregar')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($this->trabajadoresProyecto->isNotEmpty())
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <x-ui.sortable-header column="nombre" :current-column="$ordenTrabajadoresColumna" :current-direction="$ordenTrabajadoresDireccion" action="ordenarTrabajadoresPor">
                            Nombre
                        </x-ui.sortable-header>
                        <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($this->trabajadoresProyecto as $trab)
                        <tr wire:key="trab-{{ $trab->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-700">{{ trim($trab->nombre.' '.$trab->apellidos) }}</td>
                            <td class="px-6 py-3 text-right">
                                <x-ui.icon-button wire:click="quitarTrabajador({{ $trab->id }})"
                                    icon="heroicon-o-x-mark" variant="danger" tooltip="Quitar" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-8 text-center text-sm text-slate-400">
                Sin trabajadores asignados. Usa el selector para añadir.
            </div>
        @endif
    </div>

    {{-- ═══ Tab: Responsables ═══ --}}
    <div x-show="tab === 'responsables'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Responsables</span>
                    @if ($proyecto)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $this->responsablesProyecto->count() }}
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Asigna responsables a este proyecto</p>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <x-ui.searchable-select
                        wire:key="responsable-select-{{ $responsableSelectKey }}"
                        wire-model="responsableAAgregar"
                        :options="$this->responsablesProyectoDisponibles->map(fn($u) => ['value' => $u->id, 'label' => $u->id.' · '.trim($u->nombre.' '.$u->apellidos)])"
                        placeholder="— Selecciona responsable —"
                    />
                </div>
                <x-ui.button type="button" variant="success" wire:click="agregarResponsableProyecto"
                             wire:loading.attr="disabled" wire:target="agregarResponsableProyecto">
                    <x-heroicon-o-plus wire:loading.remove wire:target="agregarResponsableProyecto" class="size-4" />
                    <svg wire:loading wire:target="agregarResponsableProyecto" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="agregarResponsableProyecto">Añadir</span>
                    <span wire:loading wire:target="agregarResponsableProyecto">Añadiendo…</span>
                </x-ui.button>
            </div>
            @error('responsableAAgregar')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($this->responsablesProyecto->isNotEmpty())
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <x-ui.sortable-header column="nombre" :current-column="$ordenResponsablesColumna" :current-direction="$ordenResponsablesDireccion" action="ordenarResponsablesPor">
                            Nombre
                        </x-ui.sortable-header>
                        <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($this->responsablesProyecto as $resp)
                        <tr wire:key="resp-{{ $resp->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-700">{{ trim($resp->nombre.' '.$resp->apellidos) }}</td>
                            <td class="px-6 py-3 text-right">
                                <x-ui.icon-button wire:click="quitarResponsableProyecto({{ $resp->id }})"
                                    icon="heroicon-o-x-mark" variant="danger" tooltip="Quitar" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-8 text-center text-sm text-slate-400">
                Sin responsables asignados. Usa el selector para añadir.
            </div>
        @endif
    </div>

    {{-- ═══ Tab: Conceptos ═══ --}}
    <div x-show="tab === 'conceptos'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Conceptos</span>
                    @if ($proyecto)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $this->conceptosProyecto->count() }}
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Conceptos de trabajo disponibles en los albaranes de este proyecto</p>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <x-ui.searchable-select
                        wire:key="concepto-select-{{ $conceptoSelectKey }}"
                        wire-model="conceptoAAgregar"
                        :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->id.' · '.$c->nombre])"
                        placeholder="— Selecciona concepto —"
                    />
                </div>
                <x-ui.button type="button" variant="success" wire:click="agregarConceptoProyecto"
                             wire:loading.attr="disabled" wire:target="agregarConceptoProyecto">
                    <x-heroicon-o-plus wire:loading.remove wire:target="agregarConceptoProyecto" class="size-4" />
                    <svg wire:loading wire:target="agregarConceptoProyecto" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="agregarConceptoProyecto">Añadir</span>
                    <span wire:loading wire:target="agregarConceptoProyecto">Añadiendo…</span>
                </x-ui.button>
            </div>
            @error('conceptoAAgregar')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($this->conceptosProyecto->isNotEmpty())
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <x-ui.sortable-header column="nombre" :current-column="$ordenConceptosColumna" :current-direction="$ordenConceptosDireccion" action="ordenarConceptosPor">
                            Concepto
                        </x-ui.sortable-header>
                        <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($this->conceptosProyecto as $concepto)
                        <tr wire:key="concepto-{{ $concepto->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-700">{{ $concepto->nombre }}</td>
                            <td class="px-6 py-3 text-right">
                                <x-ui.icon-button wire:click="quitarConceptoProyecto({{ $concepto->id }})"
                                    icon="heroicon-o-x-mark" variant="danger" tooltip="Quitar" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-8 text-center text-sm text-slate-400">
                Sin conceptos asignados. Usa el selector para añadir.
            </div>
        @endif
    </div>

    @if (\App\Support\Modulos::materialesAvanzado())
    {{-- ═══ Tab: Materiales ═══ --}}
    <div x-show="tab === 'materiales'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Materiales</span>
                    @if ($proyecto)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $this->materialesProyecto->count() }}
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Materiales disponibles para consumir en los albaranes de este proyecto</p>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <x-ui.searchable-select
                        wire:key="material-select-{{ $materialSelectKey }}"
                        wire-model="materialAAgregar"
                        :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => ($m->numeroPedido?->numero ? $m->numeroPedido->numero.' · ' : '').$m->descripcion.' · '.$m->stock.' '.$m->unidad_medida])"
                        placeholder="— Selecciona material —"
                    />
                </div>
                <x-ui.button type="button" variant="success" wire:click="agregarMaterialProyecto"
                             wire:loading.attr="disabled" wire:target="agregarMaterialProyecto">
                    <x-heroicon-o-plus wire:loading.remove wire:target="agregarMaterialProyecto" class="size-4" />
                    <svg wire:loading wire:target="agregarMaterialProyecto" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="agregarMaterialProyecto">Añadir</span>
                    <span wire:loading wire:target="agregarMaterialProyecto">Añadiendo…</span>
                </x-ui.button>
            </div>
            @error('materialAAgregar')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($this->materialesProyecto->isNotEmpty())
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <x-ui.sortable-header column="descripcion" :current-column="$ordenMaterialesColumna" :current-direction="$ordenMaterialesDireccion" action="ordenarMaterialesPor">
                            Material
                        </x-ui.sortable-header>
                        <x-ui.sortable-header column="stock" :current-column="$ordenMaterialesColumna" :current-direction="$ordenMaterialesDireccion" action="ordenarMaterialesPor" class="w-36">
                            Stock
                        </x-ui.sortable-header>
                        <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($this->materialesProyecto as $mat)
                        <tr wire:key="mat-{{ $mat->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-700">{{ $mat->descripcion }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $mat->stock }} {{ $mat->unidad_medida }}</td>
                            <td class="px-6 py-3 text-right">
                                <x-ui.icon-button wire:click="quitarMaterialProyecto({{ $mat->id }})"
                                    icon="heroicon-o-x-mark" variant="danger" tooltip="Quitar" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-8 text-center text-sm text-slate-400">
                Sin materiales asignados. Usa el selector para añadir.
            </div>
        @endif
    </div>
    @endif
    {{-- ═══ Tab: Albaranes ═══ --}}
    <div x-show="tab === 'albaranes'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="px-6 py-4">
            <p class="text-sm font-semibold text-slate-900">Albaranes del proyecto</p>
            <p class="text-xs text-slate-500">Solo lectura. Para crear o editar un albarán ve a la sección de Albaranes.</p>
        </div>

        @if ($this->albaranesDelProyecto->isNotEmpty())
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <x-ui.sortable-header column="numero" :current-column="$ordenAlbaranesColumna" :current-direction="$ordenAlbaranesDireccion" action="ordenarAlbaranesPor">
                            Nº Albarán
                        </x-ui.sortable-header>
                        <x-ui.sortable-header column="fecha" :current-column="$ordenAlbaranesColumna" :current-direction="$ordenAlbaranesDireccion" action="ordenarAlbaranesPor">
                            Fecha
                        </x-ui.sortable-header>
                        <x-ui.sortable-header column="estado" :current-column="$ordenAlbaranesColumna" :current-direction="$ordenAlbaranesDireccion" action="ordenarAlbaranesPor">
                            Estado
                        </x-ui.sortable-header>
                        <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($this->albaranesDelProyecto as $albaran)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-mono font-medium text-slate-800">
                                {{ $albaran->numero ?? '#'.$albaran->id }}
                            </td>
                            <td class="px-6 py-3 text-slate-500">
                                {{ $albaran->fecha?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-3">
                                <x-ui.badge :tone="$albaran->estado->tono()" dot>
                                    {{ $albaran->estado->etiqueta() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-3 text-right">
                                @can('albaranes.ver_todos')
                                    <x-ui.icon-button
                                        as="a"
                                        href="{{ route('albaranes.ver', $albaran) }}"
                                        wire:navigate
                                        icon="heroicon-o-eye"
                                        variant="neutral"
                                        tooltip="Ver albarán" />
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-8 text-center text-sm text-slate-400">
                Este proyecto no tiene albaranes asociados.
            </div>
        @endif
    </div>

    </div>{{-- /tabs + contenido --}}

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
                    Esta acción enviará <strong>{{ $proyecto?->nombre }}</strong> a la <strong>papelera</strong>.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Albaranes y horas asociadas mantendrán la referencia hasta que el proyecto sea restaurado.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cancelarEliminar">Cancelar</x-ui.button>
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
