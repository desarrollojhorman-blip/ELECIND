<div class="space-y-4" x-data="{ tab: 'proyecto' }">
    <x-ui.page-header :title="$titulo" subtitle="Cabecera, equipo y recursos del proyecto.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('proyectos.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($proyecto)
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
            @endif
        </x-slot:actionsLeft>

        <x-slot:actionsRight>
            <x-ui.button variant="neutral" wire:click="deshacer" icon="heroicon-o-arrow-uturn-left">
                Deshacer
            </x-ui.button>
            <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="submit" form="form-proyecto" wire:loading.attr="disabled">
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

        @foreach ([
            ['key' => 'trabajadores',  'label' => 'Trabajadores',  'count' => $proyecto ? $this->trabajadoresProyecto->count() : null],
            ['key' => 'responsables',  'label' => 'Responsables',  'count' => $proyecto ? $this->responsablesProyecto->count() : null],
            ['key' => 'conceptos',     'label' => 'Conceptos',     'count' => $proyecto ? $this->conceptosProyecto->count() : null],
            ['key' => 'materiales',    'label' => 'Materiales',    'count' => $proyecto ? $this->materialesProyecto->count() : null],
        ] as $t)
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
                <x-ui.field label="Código proyecto" :error="$errors->first('form.codigo')"
                            hint="Único por cliente. Se usará en albaranes y reportes.">
                    <x-ui.input wire:model="form.codigo" placeholder="Ej. MAR-A-2026" class="font-mono" autofocus />
                </x-ui.field>

                <x-ui.field label="Nombre proyecto" required :error="$errors->first('form.nombre')">
                    <x-ui.input wire:model="form.nombre" />
                </x-ui.field>

                <x-ui.field label="Grupo" :error="$errors->first('form.tipo_proyecto_id')">
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <x-ui.select wire:model.live="selectorGrupo">
                                <option value="">— Sin grupo —</option>
                                @foreach ($this->tiposDisponibles as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                                <option value="__otro__">Otro…</option>
                            </x-ui.select>
                        </div>
                        @can('create', App\Models\TiposProyecto::class)
                            <x-ui.button type="button" variant="ghost" wire:click="abrirModalTipo">
                                + Crear tipo
                            </x-ui.button>
                        @endcan
                    </div>
                </x-ui.field>

                <x-ui.field label="Estado" required :error="$errors->first('form.estado')">
                    <x-ui.select wire:model="form.estado">
                        <option value="activo">Activo</option>
                        <option value="cerrado">Cerrado</option>
                        <option value="archivado">Archivado</option>
                    </x-ui.select>
                </x-ui.field>

                @if ($selectorGrupo === '__otro__')
                    <x-ui.field label="Nombre del nuevo grupo" class="md:col-span-2" :error="$errors->first('nuevoGrupoNombre')">
                        <x-ui.input wire:model="nuevoGrupoNombre"
                                    placeholder="Escribe el nombre del nuevo grupo" />
                    </x-ui.field>
                @endif

                <x-ui.field label="Fecha inicio" :error="$errors->first('form.fecha_inicio')">
                    <x-ui.input type="date" wire:model="form.fecha_inicio" />
                </x-ui.field>

                <x-ui.field label="Fecha fin" :error="$errors->first('form.fecha_fin')">
                    <x-ui.input type="date" wire:model="form.fecha_fin" />
                </x-ui.field>

                <x-ui.field label="Cliente" required :error="$errors->first('form.cliente_id')" class="md:col-span-2">
                    <x-ui.select wire:model="form.cliente_id">
                        <option value="">— Selecciona cliente —</option>
                        @foreach ($this->clientesDisponibles as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')" class="md:col-span-2">
                    <x-ui.textarea wire:model="form.descripcion" rows="3" />
                </x-ui.field>

                @if ($proyecto === null)
                    <p class="md:col-span-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        Para asignar responsables y trabajadores, primero crea este proyecto y luego edítalo.
                    </p>
                @endif
            </div>

            <x-ui.flash class="mt-4" />
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
                        :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                        placeholder="— Selecciona trabajador —"
                    />
                </div>
                <x-ui.button type="button" variant="success" wire:click="agregarTrabajador" icon="heroicon-o-plus">
                    Añadir
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
                        <th class="px-6 py-2.5">Nombre</th>
                        <th class="w-20 px-6 py-2.5 text-right">Acciones</th>
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
                        :options="$this->responsablesProyectoDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                        placeholder="— Selecciona responsable —"
                    />
                </div>
                <x-ui.button type="button" variant="success" wire:click="agregarResponsableProyecto" icon="heroicon-o-plus">
                    Añadir
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
                        <th class="px-6 py-2.5">Nombre</th>
                        <th class="w-20 px-6 py-2.5 text-right">Acciones</th>
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
                        :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                        placeholder="— Selecciona concepto —"
                    />
                </div>
                <x-ui.button type="button" variant="success" wire:click="agregarConceptoProyecto" icon="heroicon-o-plus">
                    Añadir
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
                        <th class="px-6 py-2.5">Concepto</th>
                        <th class="w-20 px-6 py-2.5 text-right">Acciones</th>
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
                        :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | stock: '.$m->stock.' '.$m->unidad_medida])"
                        placeholder="— Selecciona material —"
                    />
                </div>
                <x-ui.button type="button" variant="success" wire:click="agregarMaterialProyecto" icon="heroicon-o-plus">
                    Añadir
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
                        <th class="px-6 py-2.5">Material</th>
                        <th class="w-36 px-6 py-2.5">Stock</th>
                        <th class="w-20 px-6 py-2.5 text-right">Acciones</th>
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
    </div>{{-- /tabs + contenido --}}

    {{-- Modal: crear tipo de proyecto al vuelo --}}
    <x-ui.modal :show="$modalTipoAbierto"
        title="Crear nuevo tipo de proyecto"
        close-action="cerrarModalTipo"
        size="sm">

        <form wire:submit="guardarTipo" id="form-tipo-rapido" class="space-y-4">
            <x-ui.field label="Nombre" required :error="$errors->first('tipoForm.nombre')">
                <x-ui.input wire:model="tipoForm.nombre" autofocus
                            placeholder="Ej. Marzo, Mantenimiento, Aluan-2026…" />
            </x-ui.field>
            <x-ui.field label="Descripción (opcional)" :error="$errors->first('tipoForm.descripcion')">
                <x-ui.textarea wire:model="tipoForm.descripcion" rows="2" />
            </x-ui.field>
            <p class="text-xs text-slate-500">Al guardar, el tipo se selecciona automáticamente en el proyecto.</p>
        </form>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModalTipo">Cancelar</x-ui.button>
            <x-ui.button variant="success" type="submit" form="form-tipo-rapido"
                         wire:loading.attr="disabled">
                Crear tipo
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

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
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
