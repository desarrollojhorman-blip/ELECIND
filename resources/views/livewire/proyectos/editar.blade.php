<div class="space-y-4">
    <x-ui.page-header :title="$titulo" subtitle="Datos del proyecto.">
        <x-slot:actions>
            @if ($proyecto)
                <x-ui.button as="a" href="{{ route('proyectos.index') }}" wire:navigate variant="ghost" icon="heroicon-o-arrow-left">
                    Proyectos
                </x-ui.button>
                @can('proyectos.ver')
                    <x-ui.button as="a" href="{{ route('proyectos.crear') }}" wire:navigate variant="ghost" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
            @else
                <x-ui.button as="a" href="{{ route('proyectos.index') }}" wire:navigate variant="ghost" icon="heroicon-o-x-mark">
                    Cancelar
                </x-ui.button>
            @endif
            @if ($proyecto)
                @can('delete', $proyecto)
                    <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                        Eliminar
                    </x-ui.button>
                @endcan
            @endif
            <x-ui.button variant="success" type="submit" form="form-proyecto" wire:loading.attr="disabled" icon="heroicon-o-check">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="guardar" id="form-proyecto" autocomplete="off">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
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
        </div>
    </form>

    {{-- Acordeones de relaciones: solo si el proyecto ya existe --}}
    @if ($proyecto)

        {{-- Trabajadores --}}
        <div x-data="{ abierto: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <button type="button"
                    x-on:click="abierto = !abierto"
                    class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-slate-50">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $this->trabajadoresProyecto->count() }}</span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400">Añadir o consultar trabajadores del proyecto</p>
                </div>
                <x-heroicon-o-chevron-down class="size-4 shrink-0 text-slate-400 transition-transform duration-150"
                                           x-bind:class="abierto ? 'rotate-180' : ''" />
            </button>
            <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
                {{-- Fila de añadir --}}
                <div class="flex items-center gap-2 px-6 py-4">
                    <div class="flex-1">
                        <x-ui.searchable-select
                            wire:key="trabajador-select-{{ $trabajadorSelectKey }}"
                            wire-model="trabajadorAAgregar"
                            :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                            placeholder="— Selecciona trabajador —"
                        />
                    </div>
                    <x-ui.button type="button" variant="info" wire:click="agregarTrabajador" icon="heroicon-o-plus">
                        Añadir
                    </x-ui.button>
                </div>
                @error('trabajadorAAgregar')
                    <p class="px-6 pb-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($this->trabajadoresProyecto->isEmpty())
                    <p class="px-6 py-4 text-sm text-slate-500">Sin trabajadores asignados.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Nombre</th>
                                <th class="px-6 py-2.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->trabajadoresProyecto as $trab)
                                <tr wire:key="trabajador-asignado-{{ $trab->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 text-slate-700">{{ trim($trab->nombre.' '.$trab->apellidos) }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <x-ui.icon-button
                                            wire:click="quitarTrabajador({{ $trab->id }})"
                                            icon="heroicon-o-x-mark"
                                            variant="danger"
                                            tooltip="Quitar trabajador" />
                                    </td>
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
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-900">Responsables</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $this->responsablesProyecto->count() }}</span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400">Añadir o consultar responsables del proyecto</p>
                </div>
                <x-heroicon-o-chevron-down class="size-4 shrink-0 text-slate-400 transition-transform duration-150"
                                           x-bind:class="abierto ? 'rotate-180' : ''" />
            </button>
            <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
                {{-- Fila de añadir --}}
                <div class="flex items-center gap-2 px-6 py-4">
                    <div class="flex-1">
                        <x-ui.searchable-select
                            wire:key="responsable-select-{{ $responsableSelectKey }}"
                            wire-model="responsableAAgregar"
                            :options="$this->responsablesProyectoDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                            placeholder="— Selecciona responsable —"
                        />
                    </div>
                    <x-ui.button type="button" variant="info" wire:click="agregarResponsableProyecto" icon="heroicon-o-plus">
                        Añadir
                    </x-ui.button>
                </div>
                @error('responsableAAgregar')
                    <p class="px-6 pb-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($this->responsablesProyecto->isEmpty())
                    <p class="px-6 py-4 text-sm text-slate-500">Sin responsables asignados.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Nombre</th>
                                <th class="px-6 py-2.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->responsablesProyecto as $resp)
                                <tr wire:key="responsable-asignado-{{ $resp->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 text-slate-700">{{ trim($resp->nombre.' '.$resp->apellidos) }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <x-ui.icon-button
                                            wire:click="quitarResponsableProyecto({{ $resp->id }})"
                                            icon="heroicon-o-x-mark"
                                            variant="danger"
                                            tooltip="Quitar responsable" />
                                    </td>
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
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-900">Conceptos</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $this->conceptosProyecto->count() }}</span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400">Añadir o consultar conceptos del proyecto</p>
                </div>
                <x-heroicon-o-chevron-down class="size-4 shrink-0 text-slate-400 transition-transform duration-150"
                                           x-bind:class="abierto ? 'rotate-180' : ''" />
            </button>
            <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
                {{-- Fila de añadir --}}
                <div class="flex items-center gap-2 px-6 py-4">
                    <div class="flex-1">
                        <x-ui.searchable-select
                            wire:key="concepto-select-{{ $conceptoSelectKey }}"
                            wire-model="conceptoAAgregar"
                            :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                            placeholder="— Selecciona concepto —"
                        />
                    </div>
                    <x-ui.button type="button" variant="info" wire:click="agregarConceptoProyecto" icon="heroicon-o-plus">
                        Añadir
                    </x-ui.button>
                </div>
                @error('conceptoAAgregar')
                    <p class="px-6 pb-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($this->conceptosProyecto->isEmpty())
                    <p class="px-6 py-4 text-sm text-slate-500">Sin conceptos asignados.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Concepto</th>
                                <th class="px-6 py-2.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->conceptosProyecto as $concepto)
                                <tr wire:key="concepto-asignado-{{ $concepto->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 text-slate-700">{{ $concepto->nombre }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <x-ui.icon-button
                                            wire:click="quitarConceptoProyecto({{ $concepto->id }})"
                                            icon="heroicon-o-x-mark"
                                            variant="danger"
                                            tooltip="Quitar concepto" />
                                    </td>
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
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-900">Materiales</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $this->materialesProyecto->count() }}</span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400">Añadir o consultar materiales del proyecto</p>
                </div>
                <x-heroicon-o-chevron-down class="size-4 shrink-0 text-slate-400 transition-transform duration-150"
                                           x-bind:class="abierto ? 'rotate-180' : ''" />
            </button>
            <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
                {{-- Fila de añadir --}}
                <div class="flex items-center gap-2 px-6 py-4">
                    <div class="flex-1">
                        <x-ui.searchable-select
                            wire:key="material-select-{{ $materialSelectKey }}"
                            wire-model="materialAAgregar"
                            :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | '.$m->stock.' '.$m->unidad_medida])"
                            placeholder="— Selecciona material —"
                        />
                    </div>
                    <x-ui.button type="button" variant="info" wire:click="agregarMaterialProyecto" icon="heroicon-o-plus">
                        Añadir
                    </x-ui.button>
                </div>
                @error('materialAAgregar')
                    <p class="px-6 pb-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($this->materialesProyecto->isEmpty())
                    <p class="px-6 py-4 text-sm text-slate-500">Sin materiales asignados.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Material</th>
                                <th class="px-6 py-2.5">Stock</th>
                                <th class="px-6 py-2.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->materialesProyecto as $mat)
                                <tr wire:key="material-asignado-{{ $mat->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 text-slate-700">{{ $mat->descripcion }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $mat->stock }} {{ $mat->unidad_medida }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <x-ui.icon-button
                                            wire:click="quitarMaterialProyecto({{ $mat->id }})"
                                            icon="heroicon-o-x-mark"
                                            variant="danger"
                                            tooltip="Quitar material" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    @endif

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

            <p class="text-xs text-slate-500">
                Al guardar, el tipo se selecciona automáticamente en el proyecto.
            </p>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cerrarModalTipo">Cancelar</x-ui.button>
            <x-ui.button variant="success" type="submit" form="form-tipo-rapido"
                         wire:loading.attr="disabled" icon="heroicon-o-check">
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
                    Esta acción enviará <strong>{{ $proyecto?->nombre }}</strong> a la <strong>papelera</strong> (eliminación lógica).
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
