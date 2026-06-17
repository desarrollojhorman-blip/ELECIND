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
                                Albarán generado
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
                @if (! $parte->tieneAlbaran() && ($parte->lineasPersonal->isNotEmpty() || $parte->lineasMaterial->isNotEmpty()))
                    @can('update', $parte)
                        <x-ui.button variant="info" icon="heroicon-o-document-text"
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
                @can('delete', $parte)
                    <x-ui.button variant="danger" icon="heroicon-o-trash" wire:click="confirmarEliminar">
                        Eliminar
                    </x-ui.button>
                @endcan
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
            ['key' => 'trabajadores', 'label' => 'Trabajadores', 'count' => $parte?->lineasPersonal->count()],
            ['key' => 'materiales',   'label' => 'Materiales',   'count' => $parte?->lineasMaterial->count()],
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
                                                    <p class="mb-1 text-xs text-slate-500">Horas</p>
                                                    <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="modalTrabajadorHoras" />
                                                    @error('modalTrabajadorHoras') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                                <div class="w-28 shrink-0">
                                                    <p class="mb-1 text-xs text-slate-500">H. extra</p>
                                                    <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="modalTrabajadorHorasExtra" />
                                                </div>
                                                <div class="flex shrink-0 items-center gap-1 pt-5">
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
                                                <p class="mb-1 text-xs text-slate-500">Horas</p>
                                                <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="modalTrabajadorHoras" />
                                                @error('modalTrabajadorHoras') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="w-28 shrink-0">
                                                <p class="mb-1 text-xs text-slate-500">H. extra</p>
                                                <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="modalTrabajadorHorasExtra" />
                                            </div>
                                            <div class="flex shrink-0 items-center gap-1 pt-5">
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
                                <th class="w-32 px-4 py-2.5 text-right">Cantidad</th>
                                <th class="w-24 px-4 py-2.5">Unidad</th>
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
    </div>

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
