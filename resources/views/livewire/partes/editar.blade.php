<div class="space-y-4" x-data="{ tab: 'parte' }">

    {{-- Page Header --}}
    <x-ui.page-header :title="$titulo" :id-badge="$parte?->id" subtitle="Cabecera y líneas del parte.">
        @if ($parte)
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
        @endif

        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('partes.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($parte)
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
                @if (! $parte->tieneAlbaran())
                    @can('update', $parte)
                        <x-ui.button variant="warning" icon="heroicon-o-document-text"
                            wire:click="generarAlbaran"
                            wire:confirm="¿Generar albarán a partir de este parte? Se clonarán cabecera y líneas; el parte quedará vinculado al albarán y se cerrará."
                            wire:loading.attr="disabled" wire:target="generarAlbaran">
                            <span wire:loading.remove wire:target="generarAlbaran">Generar albarán</span>
                            <span wire:loading wire:target="generarAlbaran">Generando…</span>
                        </x-ui.button>
                    @endcan
                @elseif ($parte->tieneAlbaran())
                    <x-ui.button as="a" href="{{ route('albaranes.ver', $parte->albaran_id) }}" wire:navigate
                        variant="neutral" icon="heroicon-o-arrow-top-right-on-square">
                        Ver albarán
                    </x-ui.button>
                @endif
            @endif
        </x-slot:actionsLeft>

        <x-slot:actionsRight>
            @if (! $this->isBloqueado())
                <x-ui.button variant="neutral" wire:click="deshacer" wire:loading.attr="disabled" wire:target="deshacer">
                    <x-heroicon-o-arrow-uturn-left wire:loading.remove wire:target="deshacer" class="size-4" />
                    <span wire:loading.remove wire:target="deshacer">Deshacer</span>
                    <span wire:loading wire:target="deshacer">Deshaciendo…</span>
                </x-ui.button>
                <x-ui.button variant="info" type="submit" form="form-parte" wire:loading.attr="disabled" wire:target="guardar">
                    <x-heroicon-o-arrow-down-tray wire:loading.remove wire:target="guardar" class="size-4" />
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">Guardando…</span>
                </x-ui.button>
            @endif
        </x-slot:actionsRight>
    </x-ui.page-header>

    <x-ui.flash />

    {{-- Banner: parte bloqueado por tener albarán generado --}}
    @if ($parte && $parte->tieneAlbaran())
        <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
            <x-heroicon-o-lock-closed class="size-5 shrink-0 text-amber-600 mt-0.5" />
            <div class="flex-1 text-sm text-amber-900">
                <p class="font-semibold">Este parte está bloqueado.</p>
                <p class="mt-0.5 text-amber-800">
                    Ya se ha generado el albarán
                    <a href="{{ route('albaranes.ver', $parte->albaran_id) }}" wire:navigate class="font-mono font-medium underline">
                        #{{ $parte->albaran?->numero ?? $parte->albaran_id }}
                    </a>
                    a partir de este parte. Si quieres modificarlo, primero elimina el albarán.
                </p>
            </div>
        </div>
    @endif

    {{-- Tabs --}}
    <div>
    <div class="flex items-end overflow-x-auto border-b border-slate-200 px-2 pt-1.5">
        <button type="button" @click="tab = 'parte'"
                :class="tab === 'parte'
                    ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                    : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
            Parte
        </button>

        @foreach ([
            ['key' => 'trabajadores', 'label' => 'Trabajadores',  'count' => $parte?->lineasPersonal->count()],
            ['key' => 'materiales',   'label' => 'Materiales',    'count' => $parte?->lineasMaterial->count()],
            ['key' => 'costes',       'label' => 'Costes/Gastos', 'count' => null],
        ] as $t)
            @if ($modoCrear)
                <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                    title="Guarda primero la cabecera del parte">
                    <x-heroicon-o-lock-closed class="size-3" />
                    {{ $t['label'] }}
                </span>
            @else
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
            @endif
        @endforeach
    </div>

    {{-- ═══ Tab: Parte ═══ --}}
    <form wire:submit="guardar" id="form-parte" autocomplete="off">
        <div x-show="tab === 'parte'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
        <fieldset @if ($this->isBloqueado()) disabled class="opacity-70" @endif>
            <div class="grid gap-4 md:grid-cols-2">

                {{-- Fila 1: Nº Parte · Proyecto --}}
                <x-ui.field label="Nº Parte">
                    <x-ui.input
                        :value="$form->numero ?? ''"
                        class="font-mono"
                        readonly
                        :placeholder="$form->id === null ? 'Se asignará automáticamente al guardar' : ''"
                    />
                </x-ui.field>

                <x-ui.field label="Proyecto" required :error="$errors->first('form.proyecto_id')">
                    <x-ui.searchable-select
                        wire:key="proyecto-select"
                        wire-model="form.proyecto_id"
                        :value="$form->proyecto_id"
                        :options="$this->proyectosDisponibles->map(fn($p) => ['value' => $p->id, 'label' => ($p->codigo ? $p->codigo.' · ' : '').$p->nombre])"
                        placeholder="— Selecciona proyecto —"
                    />
                </x-ui.field>

                {{-- Fila 2: Concepto · Responsable --}}
                <x-ui.field label="Concepto" :error="$errors->first('form.concepto_id')">
                    <x-ui.searchable-select
                        wire:key="concepto-select-{{ $form->proyecto_id }}"
                        wire-model="form.concepto_id"
                        :value="$form->concepto_id"
                        :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->id.' · '.$c->nombre])"
                        placeholder="— Sin concepto —"
                    />
                </x-ui.field>

                <x-ui.field label="Responsable" :error="$errors->first('form.responsable_id')">
                    <x-ui.searchable-select
                        wire:key="responsable-select-{{ $form->proyecto_id }}"
                        wire-model="form.responsable_id"
                        :value="$form->responsable_id"
                        :options="$this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                        placeholder="— Sin responsable —"
                    />
                </x-ui.field>

                {{-- Fila 3: Tipo jornada · Fecha --}}
                <x-ui.field label="Tipo de jornada" required :error="$errors->first('form.tipo_hora')">
                    <x-ui.select wire:model="form.tipo_hora">
                        @foreach ($tiposHora as $tipo)
                            <option value="{{ $tipo->value }}">{{ $tipo->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Fecha" required :error="$errors->first('form.fecha')">
                    <x-ui.date-input wireModel="form.fecha" :value="$form->fecha" placeholder="dd/mm/aaaa" />
                </x-ui.field>

                {{-- Fila 4: Estado --}}
                @if (! $modoCrear)
                    <x-ui.field label="Estado" :error="$errors->first('form.estado')">
                        <x-ui.select wire:model="form.estado">
                            <option value="abierto">Abierto</option>
                            <option value="cerrado">Cerrado</option>
                        </x-ui.select>
                    </x-ui.field>
                @endif

                {{-- Plus de retención --}}
                <div class="md:col-span-2 flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <input type="checkbox" id="plus-reten" wire:model="form.tienesPlusRetencion" class="h-4 w-4 rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                    <label for="plus-reten" class="text-sm font-medium text-amber-900 cursor-pointer select-none">
                        Plus de retención (guardia)
                        <span class="ml-2 text-xs font-normal text-amber-700">Si está activo, se añade el plus de retención a la facturación y coste de cada trabajador.</span>
                    </label>
                </div>

                {{-- Fila 5: Observaciones --}}
                <x-ui.field label="Observaciones" class="md:col-span-2" :error="$errors->first('form.observaciones')">
                    <x-ui.textarea wire:model="form.observaciones" rows="3" placeholder="Notas adicionales del parte…" />
                </x-ui.field>
            </div>

            @if ($form->proyecto_id === null && ! $this->isBloqueado())
                <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    Selecciona un proyecto para poder añadir trabajadores y materiales.
                </p>
            @endif
        </fieldset>
        </div>
    </form>

    {{-- ═══ Tab: Trabajadores ═══ --}}
    @if (! $modoCrear && $parte)
        <div x-show="tab === 'trabajadores'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $parte->lineasPersonal->count() }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400">Trabajadores que participan en este parte</p>
                </div>
                @if (! $parte->tieneAlbaran() && $editandoLineaPersonalId === null)
                    @can('update', $parte)
                        <x-ui.button type="button" variant="success" wire:click="abrirModalTrabajador" icon="heroicon-o-plus">
                            Añadir
                        </x-ui.button>
                    @endcan
                @endif
            </div>

            @if ($parte->lineasPersonal->isNotEmpty() || $editandoLineaPersonalId === 0)
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Trabajador</th>
                                <th class="w-32 px-4 py-2.5 text-right">Horas</th>
                                <th class="w-32 px-4 py-2.5 text-right">H. extra</th>
                                <th class="w-24 px-4 py-2.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($parte->lineasPersonal as $linea)
                                @if ($editandoLineaPersonalId === $linea->id)
                                    {{-- Fila en modo edición --}}
                                    <tr wire:key="linea-personal-edit-{{ $linea->id }}" class="bg-blue-50">
                                        <td colspan="4" class="px-4 py-3">
                                            <div class="flex items-start gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <x-ui.searchable-select
                                                        wire:key="inline-trab-{{ $editandoLineaPersonalId }}"
                                                        wire-model="modalTrabajadorUserId"
                                                        :value="$modalTrabajadorUserId"
                                                        :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim(($u->numero_empleado ? $u->numero_empleado.' · ' : '').trim($u->nombre.' '.$u->apellidos))])"
                                                        placeholder="— Selecciona trabajador —"
                                                    />
                                                    @error('modalTrabajadorUserId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                                <div class="w-28 shrink-0">
                                                    <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="modalTrabajadorHoras" />
                                                    @error('modalTrabajadorHoras') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                                <div class="w-28 shrink-0">
                                                    <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="modalTrabajadorHorasExtra" />
                                                </div>
                                                <div class="flex shrink-0 items-center gap-1">
                                                    <x-ui.icon-button wire:click="guardarTrabajador" wire:loading.attr="disabled" wire:target="guardarTrabajador" icon="heroicon-o-check" variant="success" tooltip="Guardar" />
                                                    <x-ui.icon-button wire:click="cerrarModalTrabajador" icon="heroicon-o-x-mark" variant="neutral" tooltip="Cancelar" />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    {{-- Fila en modo lectura --}}
                                    <tr wire:key="linea-personal-{{ $linea->id }}" class="hover:bg-slate-50">
                                        <td class="px-6 py-3 font-medium text-slate-800">
                                            {{ trim(($linea->trabajador?->numero_empleado ? $linea->trabajador->numero_empleado.' · ' : '').trim(($linea->trabajador?->nombre ?? '').' '.($linea->trabajador?->apellidos ?? ''))) ?: '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $linea->horas, 2) }} h</td>
                                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $linea->horas_extra, 2) }} h</td>
                                        <td class="px-4 py-3 text-right">
                                            @if (! $parte->tieneAlbaran())
                                                @can('update', $parte)
                                                    <div class="flex items-center justify-end gap-1">
                                                        <x-ui.icon-button wire:click="abrirModalTrabajador({{ $linea->id }})" icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                                        <x-ui.icon-button wire:click="confirmarEliminarTrabajador({{ $linea->id }})" icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                                    </div>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            {{-- Fila nueva --}}
                            @if ($editandoLineaPersonalId === 0)
                                <tr wire:key="linea-personal-new" class="bg-blue-50">
                                    <td colspan="4" class="px-4 py-3">
                                        <div class="flex items-start gap-3">
                                            <div class="min-w-0 flex-1">
                                                <x-ui.searchable-select
                                                    wire:key="inline-trab-new"
                                                    wire-model="modalTrabajadorUserId"
                                                    :value="$modalTrabajadorUserId"
                                                    :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim(($u->numero_empleado ? $u->numero_empleado.' · ' : '').trim($u->nombre.' '.$u->apellidos))])"
                                                    placeholder="— Selecciona trabajador —"
                                                />
                                                @error('modalTrabajadorUserId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="w-28 shrink-0">
                                                <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="modalTrabajadorHoras" />
                                                @error('modalTrabajadorHoras') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="w-28 shrink-0">
                                                <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="modalTrabajadorHorasExtra" />
                                            </div>
                                            <div class="flex shrink-0 items-center gap-1">
                                                <x-ui.icon-button wire:click="guardarTrabajador" wire:loading.attr="disabled" wire:target="guardarTrabajador" icon="heroicon-o-check" variant="success" tooltip="Guardar" />
                                                <x-ui.icon-button wire:click="cerrarModalTrabajador" icon="heroicon-o-x-mark" variant="neutral" tooltip="Cancelar" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @else
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    No hay trabajadores en este parte. Pulsa «Añadir» para incluir participantes.
                </div>
            @endif
        </div>
    @endif

    {{-- ═══ Tab: Materiales ═══ --}}
    @if (! $modoCrear && $parte)
        <div x-show="tab === 'materiales'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-900">Materiales</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $parte->lineasMaterial->count() }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400">Materiales del proyecto utilizados en este parte</p>
                </div>
                @if (! $parte->tieneAlbaran() && $editandoLineaMaterialId === null)
                    @can('update', $parte)
                        <x-ui.button type="button" variant="success" wire:click="abrirModalMaterial" icon="heroicon-o-plus">
                            Añadir
                        </x-ui.button>
                    @endcan
                @endif
            </div>

            @if ($parte->lineasMaterial->isNotEmpty() || $editandoLineaMaterialId === 0)
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Material</th>
                                <th class="w-28 px-4 py-2.5 text-right">Cantidad</th>
                                <th class="w-20 px-4 py-2.5">Unidad</th>
                                <th class="w-28 px-4 py-2.5 text-right">Stock</th>
                                <th class="w-24 px-4 py-2.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($parte->lineasMaterial as $linea)
                                @if ($editandoLineaMaterialId === $linea->id)
                                    {{-- Fila en modo edición --}}
                                    <tr wire:key="linea-material-edit-{{ $linea->id }}" class="bg-blue-50">
                                        <td class="px-4 py-2">
                                            <x-ui.searchable-select
                                                wire:key="inline-mat-{{ $editandoLineaMaterialId }}"
                                                wire-model="modalMaterialId"
                                                :value="$modalMaterialId"
                                                :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => ($m->numeroPedido?->numero ? $m->numeroPedido->numero.' · ' : '').$m->descripcion.' · '.$m->stock.' '.$m->unidad_medida])"
                                                placeholder="— Selecciona material —"
                                            />
                                            @error('modalMaterialId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-4 py-2">
                                            <x-ui.input type="number" min="0.01" step="0.01" wire:model="modalMaterialCantidad" />
                                            @error('modalMaterialCantidad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td class="px-4 py-2 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <x-ui.icon-button wire:click="guardarMaterial" wire:loading.attr="disabled" wire:target="guardarMaterial" icon="heroicon-o-check" variant="success" tooltip="Guardar" />
                                                <x-ui.icon-button wire:click="cerrarModalMaterial" icon="heroicon-o-x-mark" variant="neutral" tooltip="Cancelar" />
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    {{-- Fila en modo lectura --}}
                                    <tr wire:key="linea-material-{{ $linea->id }}" class="hover:bg-slate-50">
                                        <td class="px-6 py-3 font-medium text-slate-800">{{ $linea->material?->descripcion ?? $linea->material_descripcion_snapshot ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $linea->cantidad, 2) }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $linea->material?->unidad_medida ?? $linea->material_unidad_medida_snapshot ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-500">
                                            {{ $linea->material ? number_format((float) $linea->material->stock, 2) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if (! $parte->tieneAlbaran())
                                                @can('update', $parte)
                                                    <div class="flex items-center justify-end gap-1">
                                                        <x-ui.icon-button wire:click="abrirModalMaterial({{ $linea->id }})" icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                                        <x-ui.icon-button wire:click="confirmarEliminarMaterial({{ $linea->id }})" icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                                    </div>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            {{-- Fila nueva --}}
                            @if ($editandoLineaMaterialId === 0)
                                <tr wire:key="linea-material-new" class="bg-blue-50">
                                    <td class="px-4 py-2">
                                        <x-ui.searchable-select
                                            wire:key="inline-mat-new"
                                            wire-model="modalMaterialId"
                                            :value="$modalMaterialId"
                                            :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => ($m->numeroPedido?->numero ? $m->numeroPedido->numero.' · ' : '').$m->descripcion.' · '.$m->stock.' '.$m->unidad_medida])"
                                            placeholder="— Selecciona material —"
                                        />
                                        @error('modalMaterialId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <x-ui.input type="number" min="0.01" step="0.01" wire:model="modalMaterialCantidad" />
                                        @error('modalMaterialCantidad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td class="px-4 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-ui.icon-button wire:click="guardarMaterial" wire:loading.attr="disabled" wire:target="guardarMaterial" icon="heroicon-o-check" variant="success" tooltip="Guardar" />
                                            <x-ui.icon-button wire:click="cerrarModalMaterial" icon="heroicon-o-x-mark" variant="neutral" tooltip="Cancelar" />
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @else
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    No hay materiales en este parte. Pulsa «Añadir» para registrar consumos.
                </div>
            @endif
        </div>
    @endif
    {{-- ═══ Tab: Costes/Gastos ═══ --}}
    @if (! $modoCrear && $parte)
        @php
            $fmtE = function ($v): string {
                $v = (float) $v;
                return number_format($v, 2, ',', '.');
            };
            $plusReten  = (bool) $parte->tiene_plus_retencion;
            $totalFact  = $parte->lineasPersonal->sum(fn ($l) => (float) $l->facturacion_snapshot + ($plusReten ? (float) $l->tarifa_plus_retencion_snapshot : 0));
            $totalCoste = $parte->lineasPersonal->sum(fn ($l) => (float) $l->coste_snapshot      + ($plusReten ? (float) $l->trabajador_tasa_plus_retencion_snapshot : 0));
            $totalMat      = \App\Support\Modulos::materialesAvanzado()
                ? $parte->lineasMaterial->sum(fn ($l) => (float) $l->cantidad * (float) $l->material_precio_venta_snapshot)
                : 0;
            $totalMatCoste = \App\Support\Modulos::materialesAvanzado()
                ? $parte->lineasMaterial->sum(fn ($l) => (float) $l->cantidad * (float) $l->material_precio_coste_snapshot)
                : 0;
            $granTotal  = $totalFact + $totalMat;
            $granCoste  = $totalCoste + $totalMatCoste;
        @endphp
        <div x-show="tab === 'costes'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Costes y Gastos</div>
                <p class="mt-0.5 text-xs text-slate-400">Resumen financiero: facturación al cliente y coste de personal y materiales.</p>
            </div>

            {{-- Sub-sección: Personal --}}
            <div class="border-t border-slate-200">
                <div class="bg-slate-50 px-6 py-2.5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Personal</span>
                </div>
                @if ($parte->lineasPersonal->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                                <tr>
                                    <th class="px-6 py-2.5">Trabajador</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap">Horas / Extra</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Tarifa hora / extra (€/h) que se cobra al cliente">Tarifa/h · Extra/h</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Plus de retención cobrado al cliente">Plus ret.</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Total facturado al cliente para esta línea">Facturación</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Tasa hora / extra (€/h) que se paga al trabajador">Tasa/h · Extra/h</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Plus de retención pagado al trabajador">Plus ret.</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap" title="Total pagado al trabajador para esta línea">Coste</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap">Margen</th>
                                    <th class="w-14 px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($parte->lineasPersonal as $linea)
                                    @php
                                        $fact      = (float) $linea->facturacion_snapshot + ($plusReten ? (float) $linea->tarifa_plus_retencion_snapshot : 0);
                                        $gasto     = (float) $linea->coste_snapshot       + ($plusReten ? (float) $linea->trabajador_tasa_plus_retencion_snapshot : 0);
                                        $margen    = $fact - $gasto;
                                    @endphp
                                    <tr wire:key="costes-{{ $linea->id }}" class="hover:bg-slate-50">
                                        <td class="px-6 py-3 font-medium text-slate-800 whitespace-nowrap">
                                            {{ trim(($linea->trabajador_numero_empleado_snapshot ? $linea->trabajador_numero_empleado_snapshot.' · ' : '').trim(($linea->trabajador_apellidos_snapshot ?? '').' '.($linea->trabajador_nombre_snapshot ?? ''))) ?: '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-right text-slate-600 tabular-nums whitespace-nowrap">
                                            {{ $fmtE($linea->horas) }} / {{ $fmtE($linea->horas_extra) }}
                                        </td>
                                        <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap">
                                            <span class="text-emerald-700">{{ $fmtE($linea->tarifa_hora_snapshot) }}</span>
                                            <span class="text-slate-400 mx-0.5">·</span>
                                            <span class="text-emerald-600">{{ $fmtE($linea->tarifa_extra_snapshot) }}</span>
                                        </td>
                                        <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap @if($plusReten) text-emerald-700 font-semibold @else text-slate-300 @endif">
                                            {{ $plusReten ? $fmtE($linea->tarifa_plus_retencion_snapshot).' €' : '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-right font-semibold tabular-nums text-emerald-700 whitespace-nowrap">
                                            {{ $fmtE($fact) }} €
                                        </td>
                                        <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap">
                                            <span class="text-rose-700">{{ $fmtE($linea->trabajador_tasa_hora_snapshot) }}</span>
                                            <span class="text-slate-400 mx-0.5">·</span>
                                            <span class="text-rose-600">{{ $fmtE($linea->trabajador_tasa_extra_snapshot) }}</span>
                                        </td>
                                        <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap @if($plusReten) text-rose-700 font-semibold @else text-slate-300 @endif">
                                            {{ $plusReten ? $fmtE($linea->trabajador_tasa_plus_retencion_snapshot).' €' : '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-right font-semibold tabular-nums text-rose-700 whitespace-nowrap">
                                            {{ $fmtE($gasto) }} €
                                        </td>
                                        <td class="px-3 py-3 text-right font-semibold tabular-nums whitespace-nowrap @if($margen >= 0) text-slate-800 @else text-red-600 @endif">
                                            {{ $fmtE($margen) }} €
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if (! $parte->tieneAlbaran())
                                                @can('update', $parte)
                                                    <x-ui.icon-button wire:click="abrirModalCostes({{ $linea->id }})" icon="heroicon-o-pencil-square" variant="info" tooltip="Editar tarifas/tasas" />
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right uppercase tracking-wider text-slate-500">Totales personal</td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3 text-right tabular-nums text-emerald-700 text-sm">{{ $fmtE($totalFact) }} €</td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3 text-right tabular-nums text-rose-700 text-sm">{{ $fmtE($totalCoste) }} €</td>
                                    <td class="px-3 py-3 text-right tabular-nums text-sm @if($totalFact - $totalCoste >= 0) text-slate-800 @else text-red-600 @endif">
                                        {{ $fmtE($totalFact - $totalCoste) }} €
                                    </td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-sm text-slate-400">
                        Añade trabajadores en la pestaña «Trabajadores» para ver los costes de personal.
                    </div>
                @endif
            </div>

            {{-- Sub-sección: Materiales --}}
            @if(\App\Support\Modulos::materialesAvanzado())
            <div class="border-t border-slate-200">
                <div class="bg-slate-50 px-6 py-2.5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Materiales</span>
                </div>
                @if ($parte->lineasMaterial->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                                <tr>
                                    <th class="px-6 py-2.5">Material</th>
                                    <th class="w-24 px-4 py-2.5 text-right">Cantidad</th>
                                    <th class="w-16 px-4 py-2.5">Unidad</th>
                                    <th class="w-28 px-4 py-2.5 text-right whitespace-nowrap" title="Precio de venta al cliente">Venta/ud</th>
                                    <th class="w-28 px-4 py-2.5 text-right whitespace-nowrap">Total venta</th>
                                    <th class="w-28 px-4 py-2.5 text-right whitespace-nowrap" title="Precio de coste">Coste/ud</th>
                                    <th class="w-28 px-4 py-2.5 text-right whitespace-nowrap">Total coste</th>
                                    <th class="w-24 px-4 py-2.5 text-right whitespace-nowrap">Margen</th>
                                    <th class="w-14 px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($parte->lineasMaterial as $linea)
                                    @php
                                        $totalVentaLinea = (float) $linea->cantidad * (float) $linea->material_precio_venta_snapshot;
                                        $totalCosteLinea = (float) $linea->cantidad * (float) $linea->material_precio_coste_snapshot;
                                        $margenLinea     = $totalVentaLinea - $totalCosteLinea;
                                    @endphp
                                    <tr wire:key="costes-mat-{{ $linea->id }}" class="hover:bg-slate-50">
                                        <td class="px-6 py-3 font-medium text-slate-800">{{ $linea->material?->descripcion ?? $linea->material_descripcion_snapshot ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 tabular-nums">{{ number_format((float) $linea->cantidad, 2) }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $linea->material?->unidad_medida ?? $linea->material_unidad_medida_snapshot ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right text-emerald-700 tabular-nums font-semibold">
                                            {{ $linea->material_precio_venta_snapshot !== null ? $fmtE($linea->material_precio_venta_snapshot).' €' : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-emerald-700 tabular-nums font-semibold">{{ $fmtE($totalVentaLinea) }} €</td>
                                        <td class="px-4 py-3 text-right text-rose-700 tabular-nums font-semibold">
                                            {{ $linea->material_precio_coste_snapshot !== null ? $fmtE($linea->material_precio_coste_snapshot).' €' : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-rose-700 tabular-nums font-semibold">{{ $fmtE($totalCosteLinea) }} €</td>
                                        <td class="px-4 py-3 text-right tabular-nums font-semibold whitespace-nowrap @if($margenLinea >= 0) text-slate-800 @else text-red-600 @endif">
                                            {{ $fmtE($margenLinea) }} €
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if (! $parte->tieneAlbaran())
                                                @can('update', $parte)
                                                    <x-ui.icon-button wire:click="abrirEditarPrecioMaterial({{ $linea->id }})" icon="heroicon-o-pencil-square" variant="info" tooltip="Editar precios" />
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700">
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right uppercase tracking-wider text-slate-500">Totales materiales</td>
                                    <td class="px-4 py-3 text-right tabular-nums text-emerald-700 text-sm">{{ $fmtE($totalMat) }} €</td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3 text-right tabular-nums text-rose-700 text-sm">{{ $fmtE($totalMatCoste) }} €</td>
                                    <td class="px-4 py-3 text-right tabular-nums text-sm @if($totalMat - $totalMatCoste >= 0) text-slate-800 @else text-red-600 @endif">
                                        {{ $fmtE($totalMat - $totalMatCoste) }} €
                                    </td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-sm text-slate-400">
                        Añade materiales en la pestaña «Materiales» para ver los costes de materiales.
                    </div>
                @endif
            </div>
            @endif

            {{-- Resumen global --}}
            <div class="border-t-2 border-slate-300 bg-slate-100 px-6 py-4">
                <div class="flex justify-end gap-8">
                    <div class="text-center">
                        <div class="text-xs uppercase tracking-wider text-slate-500 mb-1">Facturación total</div>
                        <div class="text-xl font-bold text-emerald-700 tabular-nums">{{ $fmtE($granTotal) }} €</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs uppercase tracking-wider text-slate-500 mb-1">Coste total</div>
                        <div class="text-xl font-bold text-rose-700 tabular-nums">{{ $fmtE($granCoste) }} €</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs uppercase tracking-wider text-slate-500 mb-1">Margen</div>
                        <div class="text-xl font-bold tabular-nums @if($granTotal - $granCoste >= 0) text-slate-800 @else text-red-600 @endif">
                            {{ $fmtE($granTotal - $granCoste) }} €
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    </div>

    {{-- ═══ Modal: editar tarifas/tasas de una línea (Costes/Gastos) ═══ --}}
    <x-ui.modal :show="$editandoCostesLineaId !== null" title="Editar tarifas y tasas" close-action="cerrarModalCostes" size="sm">
        <div class="space-y-4">
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3">
                <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider mb-2">Cobro al cliente (€)</p>
                <div class="grid grid-cols-3 gap-3">
                    <x-ui.field label="Hora normal" :error="$errors->first('modalCosteTarifaHora')">
                        <x-ui.input type="number" step="0.0001" min="0" wire:model="modalCosteTarifaHora" />
                    </x-ui.field>
                    <x-ui.field label="Hora extra" :error="$errors->first('modalCosteTarifaExtra')">
                        <x-ui.input type="number" step="0.0001" min="0" wire:model="modalCosteTarifaExtra" />
                    </x-ui.field>
                    <x-ui.field label="Plus retención" :error="$errors->first('modalCosteTarifaPlusReten')">
                        <x-ui.input type="number" step="0.0001" min="0" wire:model="modalCosteTarifaPlusReten" />
                    </x-ui.field>
                </div>
            </div>
            <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3">
                <p class="text-xs font-semibold text-rose-800 uppercase tracking-wider mb-2">Pago al trabajador (€)</p>
                <div class="grid grid-cols-3 gap-3">
                    <x-ui.field label="Hora normal" :error="$errors->first('modalCosteTasaHora')">
                        <x-ui.input type="number" step="0.001" min="0" wire:model="modalCosteTasaHora" />
                    </x-ui.field>
                    <x-ui.field label="Hora extra" :error="$errors->first('modalCosteTasaExtra')">
                        <x-ui.input type="number" step="0.001" min="0" wire:model="modalCosteTasaExtra" />
                    </x-ui.field>
                    <x-ui.field label="Plus retención" :error="$errors->first('modalCosteTasaPlusReten')">
                        <x-ui.input type="number" step="0.001" min="0" wire:model="modalCosteTasaPlusReten" />
                    </x-ui.field>
                </div>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button wire:click="cerrarModalCostes" variant="neutral">Cancelar</x-ui.button>
            <x-ui.button wire:click="guardarCostesLinea" variant="info">Guardar</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- ═══ Modal: editar precios de material (Costes/Gastos) ═══ --}}
    <x-ui.modal :show="$editandoPrecioMaterialId !== null" title="Editar precios de material" close-action="cerrarEditarPrecioMaterial" size="sm">
        <div class="space-y-4">
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3">
                <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider mb-2">Precio de venta al cliente (€/ud)</p>
                <x-ui.field label="Venta/ud" :error="$errors->first('modalPrecioMaterial')">
                    <x-ui.input type="number" step="0.0001" min="0" wire:model="modalPrecioMaterial" />
                </x-ui.field>
            </div>
            <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3">
                <p class="text-xs font-semibold text-rose-800 uppercase tracking-wider mb-2">Precio de coste (€/ud)</p>
                <x-ui.field label="Coste/ud" :error="$errors->first('modalPrecioCoste')">
                    <x-ui.input type="number" step="0.0001" min="0" wire:model="modalPrecioCoste" />
                </x-ui.field>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button wire:click="cerrarEditarPrecioMaterial" variant="neutral">Cancelar</x-ui.button>
            <x-ui.button wire:click="guardarPrecioMaterial" variant="info">Guardar</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- ═══ Modal: confirmar eliminar trabajador ═══ --}}
    <x-ui.modal :show="$confirmarEliminarLineaPersonalId !== null"
        title="Eliminar trabajador" close-action="cancelarEliminarTrabajador" size="sm">
        <p class="text-sm text-slate-700">¿Eliminar esta línea de trabajador?</p>
        <p class="mt-1 text-xs text-slate-500">Esta acción no se puede deshacer.</p>
        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminarTrabajador">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminarTrabajador">Eliminar</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- ═══ Modal: confirmar eliminar material ═══ --}}
    <x-ui.modal :show="$confirmarEliminarLineaMaterialId !== null"
        title="Eliminar material" close-action="cancelarEliminarMaterial" size="sm">
        <p class="text-sm text-slate-700">¿Eliminar esta línea de material?</p>
        <p class="mt-1 text-xs text-slate-500">Esta acción no se puede deshacer.</p>
        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminarMaterial">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminarMaterial">Eliminar</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- ═══ Modal: eliminar parte ═══ --}}
    @if ($parte)
        <x-ui.modal :show="$confirmarEliminarId !== null" title="Eliminar parte" close-action="cancelarEliminar" size="sm">
            <p class="text-sm text-slate-700">¿Eliminar el parte <strong class="font-mono">{{ $parte->numero }}</strong>?</p>
            <p class="mt-1 text-xs text-slate-500">Esta acción se puede revertir desde papelera.</p>
            <x-slot:footer>
                <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
                <x-ui.button variant="danger" wire:click="eliminar">Eliminar</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
