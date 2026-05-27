<div class="space-y-4" x-data="{ tab: 'albaran' }">

    {{-- Page Header --}}
    <x-ui.page-header :title="$titulo" subtitle="Cabecera y líneas del albarán.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('albaranes.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($albaran)
                <div class="relative" x-data="{ abierto: false }" @click.outside="abierto = false">
                    <x-ui.button type="button" @click="abierto = !abierto" variant="neutral" icon="heroicon-o-printer">
                        Imprimir
                        <x-heroicon-o-chevron-down class="size-3.5 ml-0.5" />
                    </x-ui.button>
                    <div x-show="abierto" x-transition
                         class="absolute left-0 z-20 mt-1 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                        <a href="{{ route('albaranes.pdf', ['albaran' => $albaran, 'materiales' => 1]) }}" target="_blank"
                           @click="abierto = false"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <x-heroicon-o-clipboard-document-list class="size-4 text-slate-400" />
                            Con materiales
                        </a>
                        <a href="{{ route('albaranes.pdf', ['albaran' => $albaran, 'materiales' => 0]) }}" target="_blank"
                           @click="abierto = false"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <x-heroicon-o-clipboard-document class="size-4 text-slate-400" />
                            Sin materiales
                        </a>
                    </div>
                </div>
                @can('albaranes.crear_web')
                    <x-ui.button as="a" href="{{ route('albaranes.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
                @can('delete', $albaran)
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
            <x-ui.button variant="info" type="submit" form="form-albaran" wire:loading.attr="disabled" wire:target="guardar">
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

    <x-ui.flash />

    {{-- Tabs + contenido como unidad visual --}}
    @php $modoCrear = $albaran === null; @endphp
    <div>
    <div class="flex items-end overflow-x-auto border-b border-slate-200 px-2 pt-1.5">
        <button type="button"
                @click="tab = 'albaran'"
                :class="tab === 'albaran'
                    ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                    : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
            Albarán
        </button>

        @foreach (array_values(array_filter([
            ['key' => 'trabajadores', 'label' => 'Trabajadores', 'count' => $albaran?->lineasPersonal->count()],
            \App\Support\Modulos::materialesAvanzado() ? ['key' => 'materiales', 'label' => 'Materiales', 'count' => $albaran?->lineasMaterial->count()] : false,
            ['key' => 'firmas',       'label' => 'Firmas',       'count' => null],
            ['key' => 'archivos',     'label' => 'Archivos',     'count' => $albaran?->archivos->count()],
        ])) as $t)
            @if ($modoCrear)
                <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                      title="Guarda primero el albarán para acceder a esta sección">
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

    {{-- ═══ Tab: Albarán ═══ --}}
    <form wire:submit="guardar" id="form-albaran" autocomplete="off">
        <div x-show="tab === 'albaran'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">

                {{-- Fila 1: Nº Albarán · Proyecto --}}
                <x-ui.field label="Nº Albarán">
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

                {{-- Fila 2: Concepto --}}
                <x-ui.field label="Concepto" :error="$errors->first('form.concepto_id')">
                    <x-ui.searchable-select
                        wire:key="concepto-select-{{ $form->proyecto_id }}"
                        wire-model="form.concepto_id"
                        :value="$form->concepto_id"
                        :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->id.' · '.$c->nombre])"
                        placeholder="— Sin concepto —"
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

                {{-- Fila 4: Estado (media línea) --}}
                <x-ui.field label="Estado" required :error="$errors->first('form.estado')">
                    <x-ui.select wire:model="form.estado">
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->value }}">{{ $estado->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                {{-- Fila 5: Observaciones (línea completa) --}}
                <x-ui.field label="Observaciones" class="md:col-span-2" :error="$errors->first('form.observaciones')">
                    <x-ui.textarea wire:model="form.observaciones" rows="3" placeholder="Notas adicionales del parte…" />
                </x-ui.field>
            </div>

            @if ($form->proyecto_id === null)
                <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    Selecciona un proyecto para poder añadir trabajadores, materiales y firmantes.
                </p>
            @endif

        </div>
    </form>

    {{-- ═══ Tab: Trabajadores ═══ --}}
    <div x-show="tab === 'trabajadores'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                    @if ($albaran)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $albaran->lineasPersonal->count() }}
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Trabajadores que participan en este parte</p>
            </div>
            @if ($albaran && $editandoLineaPersonalId === null)
                <x-ui.button type="button" variant="success" wire:click="abrirModalTrabajador" icon="heroicon-o-plus">
                    Añadir
                </x-ui.button>
            @endif
        </div>

        @if ($albaran && ($albaran->lineasPersonal->isNotEmpty() || $editandoLineaPersonalId === 0))
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
                        @foreach ($albaran->lineasPersonal as $linea)
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
                                        {{ trim(($linea->trabajador->numero_empleado ? $linea->trabajador->numero_empleado.' · ' : '').trim(($linea->trabajador->nombre ?? '').' '.($linea->trabajador->apellidos ?? ''))) ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $linea->horas, 2) }} h</td>
                                    <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $linea->horas_extra, 2) }} h</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-ui.icon-button wire:click="abrirModalTrabajador({{ $linea->id }})" icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                            <x-ui.icon-button wire:click="confirmarEliminarTrabajador({{ $linea->id }})" icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                        </div>
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
                @if (!$albaran)
                    Guarda primero la cabecera del parte para poder añadir trabajadores.
                @else
                    No hay trabajadores en este parte. Pulsa «Añadir» para incluir participantes.
                @endif
            </div>
        @endif
    </div>

    {{-- ═══ Tab: Materiales ═══ --}}
    @if(\App\Support\Modulos::materialesAvanzado())
    <div x-show="tab === 'materiales'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Materiales</span>
                    @if ($albaran)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $albaran->lineasMaterial->count() }}
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Materiales del proyecto utilizados en este parte</p>
            </div>
            @if ($albaran && $editandoLineaMaterialId === null)
                <x-ui.button type="button" variant="success" wire:click="abrirModalMaterial" icon="heroicon-o-plus">
                    Añadir
                </x-ui.button>
            @endif
        </div>

        @if ($albaran && ($albaran->lineasMaterial->isNotEmpty() || $editandoLineaMaterialId === 0))
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
                        @foreach ($albaran->lineasMaterial as $linea)
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
                                    <td class="px-6 py-3 font-medium text-slate-800">{{ $linea->material?->descripcion ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $linea->cantidad, 2) }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $linea->material?->unidad_medida ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-slate-500">
                                        {{ $linea->material ? number_format((float) $linea->material->stock, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-ui.icon-button wire:click="abrirModalMaterial({{ $linea->id }})" icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                            <x-ui.icon-button wire:click="confirmarEliminarMaterial({{ $linea->id }})" icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                        </div>
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
                @if (!$albaran)
                    Guarda primero la cabecera del parte para poder añadir materiales.
                @else
                    No hay materiales en este parte. Pulsa «Añadir» para registrar consumos.
                @endif
            </div>
        @endif
    </div>
    @endif

    {{-- ═══ Tab: Firmas ═══ --}}
    <div x-show="tab === 'firmas'"
         x-data="{ notificarTrab: false, notificarResp: false }">

        {{-- Cabecera sección firmas --}}
        <div class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                <div>
                    <span class="text-sm font-semibold text-slate-900">Firmantes</span>
                    <p class="mt-0.5 text-xs text-slate-400">Configura quién debe firmar y envía notificaciones</p>
                </div>
                <button type="button"
                        @click="$wire.notificarFirmantes(notificarTrab, notificarResp)"
                        x-bind:disabled="(!notificarTrab && !notificarResp)"
                        wire:loading.attr="disabled"
                        wire:target="notificarFirmantes"
                        :class="(!notificarTrab && !notificarResp) ? 'opacity-50 cursor-not-allowed' : ''"
                        class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold text-white transition-opacity"
                        style="background-color: {{ \App\Support\Branding::colorPrimario() }}">
                    <x-heroicon-o-paper-airplane wire:loading.remove wire:target="notificarFirmantes" class="size-4" />
                    <svg wire:loading wire:target="notificarFirmantes" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="notificarFirmantes">Notificar seleccionados</span>
                    <span wire:loading wire:target="notificarFirmantes">Enviando…</span>
                </button>
            </div>

            {{-- Dos bloques de firmante --}}
            <div class="grid gap-px border-t border-slate-100 bg-slate-100 md:grid-cols-2">

                {{-- ── Firmante: Empleado ── --}}
                <div class="bg-white p-6"
                     x-data="{ esOtro: {{ $form->firma_trabajador_otro_nombre ? 'true' : 'false' }} }">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Empleado</h4>
                            <p class="text-xs text-slate-500">Quien firma por parte de la empresa</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <label class="flex cursor-pointer items-center gap-1.5 text-xs text-slate-600">
                                <input type="checkbox" x-model="notificarTrab"
                                       class="size-3.5 rounded border-slate-300" />
                                Notificar
                            </label>
                            @if ($albaran?->tokensFirma->where('tipo_firmante.value', 'trabajador')->isNotEmpty())
                                @php $t = $albaran->tokensFirma->where('tipo_firmante.value', 'trabajador')->sortByDesc('created_at')->first(); @endphp
                                <span class="text-xs text-slate-400">
                                    Último: {{ $t->created_at->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">Sin envíos</span>
                            @endif
                        </div>
                    </div>

                    {{-- Select usuario o campos Otro --}}
                    <div x-show="!esOtro" class="space-y-2">
                        <x-ui.field label="Empleado firmante" :error="$errors->first('form.firma_trabajador_user_id')">
                            <x-ui.searchable-select
                                wire:key="firma-trab-{{ $form->proyecto_id }}"
                                wire-model="form.firma_trabajador_user_id"
                                :value="$form->firma_trabajador_user_id"
                                :options="$this->firmantesInternosDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim(($u->numero_empleado ? $u->numero_empleado.' · ' : '').trim($u->nombre.' '.$u->apellidos))])"
                                placeholder="— Sin firmante —"
                            />
                        </x-ui.field>
                        <button type="button"
                                @click="esOtro = true; $wire.set('form.firma_trabajador_user_id', null)"
                                class="text-xs text-slate-400 underline hover:text-slate-600">
                            Usar otra persona…
                        </button>
                    </div>

                    <div x-show="esOtro" class="space-y-3">
                        <x-ui.field label="Nombre" :error="$errors->first('form.firma_trabajador_otro_nombre')">
                            <x-ui.input wire:model.defer="form.firma_trabajador_otro_nombre"
                                        placeholder="Nombre completo" />
                        </x-ui.field>
                        <x-ui.field label="Correo" :error="$errors->first('form.firma_trabajador_otro_correo')">
                            <x-ui.input type="email" wire:model.defer="form.firma_trabajador_otro_correo"
                                        placeholder="correo@ejemplo.com" />
                        </x-ui.field>
                        <button type="button"
                                @click="esOtro = false; $wire.set('form.firma_trabajador_otro_nombre', null); $wire.set('form.firma_trabajador_otro_correo', null)"
                                class="text-xs text-slate-400 underline hover:text-slate-600">
                            ← Usar usuario del proyecto
                        </button>
                    </div>

                    {{-- Estado firma --}}
                    <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5">
                        @if ($firmaTrabajador)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 text-xs text-green-700">
                                    <x-heroicon-o-check-circle class="size-4" />
                                    Firmado el {{ $firmaTrabajador->firmado_at->format('d/m/Y H:i') }}
                                </div>
                                <a href="{{ Storage::disk('public')->url($firmaTrabajador->firma_path) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                    <x-heroicon-o-arrow-down-tray class="size-3.5" />
                                    Descargar
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-slate-400">Sin firma registrada</p>
                        @endif
                    </div>

                    {{-- URL enlace firma --}}
                    @if ($tokenTrabajador && $tokenTrabajador->esValido())
                        <div class="mt-3" x-data="{ copiado: false }">
                            <p class="mb-1 text-xs font-medium text-slate-500">Enlace de firma</p>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly
                                       value="{{ route('albaranes.firmar', ['token' => $tokenTrabajador->token]) }}"
                                       class="min-w-0 flex-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 font-mono text-xs text-slate-600 focus:outline-none" />
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ route('albaranes.firmar', ['token' => $tokenTrabajador->token]) }}'); copiado = true; setTimeout(() => copiado = false, 2000)"
                                        class="shrink-0 rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                    <span x-show="!copiado">Copiar</span>
                                    <span x-show="copiado" class="text-green-600">✓ Copiado</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ── Firmante: Responsable ── --}}
                <div class="bg-white p-6"
                     x-data="{ esOtro: {{ $form->firma_responsable_otro_nombre ? 'true' : 'false' }} }">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Responsable</h4>
                            <p class="text-xs text-slate-500">Quien firma por parte de la empresa / cliente</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <label class="flex cursor-pointer items-center gap-1.5 text-xs text-slate-600">
                                <input type="checkbox" x-model="notificarResp"
                                       class="size-3.5 rounded border-slate-300" />
                                Notificar
                            </label>
                            @if ($albaran?->tokensFirma->where('tipo_firmante.value', 'responsable')->isNotEmpty())
                                @php $t = $albaran->tokensFirma->where('tipo_firmante.value', 'responsable')->sortByDesc('created_at')->first(); @endphp
                                <span class="text-xs text-slate-400">
                                    Último: {{ $t->created_at->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">Sin envíos</span>
                            @endif
                        </div>
                    </div>

                    {{-- Select usuario o campos Otro --}}
                    <div x-show="!esOtro" class="space-y-2">
                        <x-ui.field label="Usuario del proyecto" :error="$errors->first('form.responsable_id')">
                            <x-ui.searchable-select
                                wire:key="firma-resp-{{ $form->proyecto_id }}"
                                wire-model="form.responsable_id"
                                :value="$form->responsable_id"
                                :options="$this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => $u->id.' · '.trim($u->nombre.' '.$u->apellidos)])"
                                placeholder="— Sin firmante —"
                            />
                        </x-ui.field>
                        <button type="button"
                                @click="esOtro = true; $wire.set('form.responsable_id', null)"
                                class="text-xs text-slate-400 underline hover:text-slate-600">
                            Usar otra persona…
                        </button>
                    </div>

                    <div x-show="esOtro" class="space-y-3">
                        <x-ui.field label="Nombre" :error="$errors->first('form.firma_responsable_otro_nombre')">
                            <x-ui.input wire:model.defer="form.firma_responsable_otro_nombre"
                                        placeholder="Nombre completo" />
                        </x-ui.field>
                        <x-ui.field label="Correo" :error="$errors->first('form.firma_responsable_otro_correo')">
                            <x-ui.input type="email" wire:model.defer="form.firma_responsable_otro_correo"
                                        placeholder="correo@ejemplo.com" />
                        </x-ui.field>
                        <button type="button"
                                @click="esOtro = false; $wire.set('form.firma_responsable_otro_nombre', null); $wire.set('form.firma_responsable_otro_correo', null)"
                                class="text-xs text-slate-400 underline hover:text-slate-600">
                            ← Usar usuario del proyecto
                        </button>
                    </div>

                    {{-- Estado firma --}}
                    <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5">
                        @if ($firmaResponsable)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 text-xs text-green-700">
                                    <x-heroicon-o-check-circle class="size-4" />
                                    Firmado el {{ $firmaResponsable->firmado_at->format('d/m/Y H:i') }}
                                </div>
                                <a href="{{ Storage::disk('public')->url($firmaResponsable->firma_path) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                    <x-heroicon-o-arrow-down-tray class="size-3.5" />
                                    Descargar
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-slate-400">Sin firma registrada</p>
                        @endif
                    </div>

                    {{-- URL enlace firma --}}
                    @if ($tokenResponsable && $tokenResponsable->esValido())
                        <div class="mt-3" x-data="{ copiado: false }">
                            <p class="mb-1 text-xs font-medium text-slate-500">Enlace de firma</p>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly
                                       value="{{ route('albaranes.firmar', ['token' => $tokenResponsable->token]) }}"
                                       class="min-w-0 flex-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 font-mono text-xs text-slate-600 focus:outline-none" />
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ route('albaranes.firmar', ['token' => $tokenResponsable->token]) }}'); copiado = true; setTimeout(() => copiado = false, 2000)"
                                        class="shrink-0 rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                    <span x-show="!copiado">Copiar</span>
                                    <span x-show="copiado" class="text-green-600">✓ Copiado</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Tab: Archivos ═══ --}}
    <div x-show="tab === 'archivos'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Archivos adjuntos</span>
                    @if ($albaran)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ $albaran->archivos->count() }}
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Documentos relacionados con este parte (máx. 10 MB por archivo)</p>
            </div>
            @if ($albaran && !$subiendoArchivo)
                <x-ui.button type="button" variant="success" wire:click="abrirModalArchivo" icon="heroicon-o-plus">
                    Añadir
                </x-ui.button>
            @endif
        </div>

        @if ($albaran && ($albaran->archivos->isNotEmpty() || $subiendoArchivo))
            <div class="border-t border-slate-100">
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Nombre</th>
                            <th class="w-48 px-4 py-2.5">Archivo original</th>
                            <th class="w-24 px-4 py-2.5 text-right">Tamaño</th>
                            <th class="w-36 px-4 py-2.5">Fecha</th>
                            <th class="w-24 px-4 py-2.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($albaran->archivos as $archivo)
                            <tr wire:key="archivo-{{ $archivo->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-medium text-slate-800">
                                    {{ $archivo->nombre }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 truncate max-w-[180px]">
                                    {{ $archivo->nombre_original }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-slate-500">
                                    {{ $archivo->tamanoFormateado() }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    {{ $archivo->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ $archivo->url() }}" target="_blank"
                                           class="inline-flex items-center justify-center rounded-md p-1.5 text-blue-600 hover:bg-blue-50"
                                           title="Descargar">
                                            <x-heroicon-o-arrow-down-tray class="size-4" />
                                        </a>
                                        <x-ui.icon-button
                                            wire:click="confirmarEliminarArchivo({{ $archivo->id }})"
                                            icon="heroicon-o-trash"
                                            variant="danger"
                                            tooltip="Eliminar" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Fila nueva --}}
                        @if ($subiendoArchivo)
                            <tr wire:key="archivo-new" class="bg-blue-50">
                                {{-- Columna: Nombre --}}
                                <td class="px-6 py-3">
                                    <x-ui.input wire:model="modalArchivoNombre" placeholder="Nombre descriptivo (opcional)" />
                                    @error('modalArchivoNombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                {{-- Columna: Archivo original --}}
                                <td class="px-4 py-3" colspan="3">
                                    <input type="file"
                                           wire:model="modalArchivoFichero"
                                           class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-primary-700 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-primary-800" />
                                    <div wire:loading wire:target="modalArchivoFichero" class="mt-1 text-xs text-slate-500">Procesando…</div>
                                    @error('modalArchivoFichero') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                {{-- Columna: Acciones --}}
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui.icon-button wire:click="guardarArchivo" wire:loading.attr="disabled" wire:target="guardarArchivo,modalArchivoFichero" icon="heroicon-o-check" variant="success" tooltip="Subir" />
                                        <x-ui.icon-button wire:click="cerrarModalArchivo" icon="heroicon-o-x-mark" variant="neutral" tooltip="Cancelar" />
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @else
            <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                @if (!$albaran)
                    Guarda primero la cabecera del parte para poder adjuntar archivos.
                @else
                    No hay archivos adjuntos. Pulsa «Añadir» para subir documentos.
                @endif
            </div>
        @endif
    </div>
    </div>{{-- /tabs + contenido --}}


    {{-- Modal confirmar eliminar trabajador --}}
    <x-ui.modal
        :show="$confirmarEliminarLineaPersonalId !== null"
        title="Eliminar trabajador"
        close-action="cancelarEliminarTrabajador"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <p class="text-sm text-slate-700">
                ¿Seguro que quieres quitar este trabajador del parte? Esta acción no se puede deshacer.
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminarTrabajador">Cancelar</x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminarTrabajador"
                         wire:loading.attr="disabled"
                         wire:target="eliminarTrabajador">
                <x-heroicon-o-trash wire:loading.remove wire:target="eliminarTrabajador" class="size-4" />
                <svg wire:loading wire:target="eliminarTrabajador" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="eliminarTrabajador">Eliminar</span>
                <span wire:loading wire:target="eliminarTrabajador">Eliminando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    @if(\App\Support\Modulos::materialesAvanzado())

    {{-- Modal confirmar eliminar material --}}
    <x-ui.modal
        :show="$confirmarEliminarLineaMaterialId !== null"
        title="Eliminar material"
        close-action="cancelarEliminarMaterial"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <p class="text-sm text-slate-700">
                ¿Seguro que quieres quitar este material del parte? El stock volverá a su valor anterior.
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminarMaterial">Cancelar</x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminarMaterial"
                         wire:loading.attr="disabled"
                         wire:target="eliminarMaterial">
                <x-heroicon-o-trash wire:loading.remove wire:target="eliminarMaterial" class="size-4" />
                <svg wire:loading wire:target="eliminarMaterial" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="eliminarMaterial">Eliminar</span>
                <span wire:loading wire:target="eliminarMaterial">Eliminando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
    @endif

    {{-- Modal confirmar eliminar archivo --}}
    <x-ui.modal
        :show="$confirmarEliminarArchivoId !== null"
        title="Eliminar archivo"
        close-action="cancelarEliminarArchivo"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <p class="text-sm text-slate-700">
                ¿Seguro que quieres eliminar este archivo? Se borrará del servidor y no se puede recuperar.
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminarArchivo">Cancelar</x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminarArchivo"
                         wire:loading.attr="disabled"
                         wire:target="eliminarArchivo">
                <x-heroicon-o-trash wire:loading.remove wire:target="eliminarArchivo" class="size-4" />
                <svg wire:loading wire:target="eliminarArchivo" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="eliminarArchivo">Eliminar</span>
                <span wire:loading wire:target="eliminarArchivo">Eliminando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar albarán"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el albarán <strong>{{ $albaran?->numero }}</strong> a la <strong>papelera</strong>.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Podrás restaurarlo desde el filtro <em>«En papelera»</em>.
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
