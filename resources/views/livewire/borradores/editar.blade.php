<div class="space-y-4" x-data="{ tab: 'borrador' }">
    <x-ui.page-header :title="$titulo" subtitle="Editar borrador">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('borradores.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($borrador)
                <x-ui.button as="a" href="{{ route('borradores.ver', $borrador) }}" wire:navigate variant="neutral" icon="heroicon-o-eye">
                    Ver
                </x-ui.button>
            @endif
            @can('create', App\Models\Borrador::class)
                <x-ui.button as="a" href="{{ route('borradores.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                    Nuevo
                </x-ui.button>
            @endcan
            @if ($borrador)
                @can('delete', $borrador)
                    <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                        Eliminar
                    </x-ui.button>
                @endcan
            @endif
        </x-slot:actionsLeft>
        <x-slot:actionsRight>
            <x-ui.button type="button" variant="neutral" wire:click="deshacer" icon="heroicon-o-arrow-uturn-left">
                Deshacer
            </x-ui.button>
            <x-ui.button type="submit" form="form-borrador" variant="primary" icon="heroicon-o-check">
                Guardar
            </x-ui.button>
        </x-slot:actionsRight>
    </x-ui.page-header>

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

            @foreach (array_values(array_filter([
                ['key' => 'trabajadores', 'label' => 'Trabajadores', 'count' => count($form->lineasPersonal)],
                \App\Support\Modulos::materialesAvanzado() ? ['key' => 'materiales', 'label' => 'Materiales', 'count' => count($form->lineasMaterial)] : false,
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

        {{-- ═══ Tab: Borrador ═══ --}}
        <form wire:submit="guardar" id="form-borrador" autocomplete="off">
            <div x-show="tab === 'borrador'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2">

                    {{-- Número borrador (solo lectura en edición) --}}
                    @if ($borrador)
                        <x-ui.field label="Nº Borrador">
                            <x-ui.input value="{{ $borrador->numero_borrador }}" class="font-mono" readonly />
                        </x-ui.field>
                        <div></div>
                    @endif

                    {{-- Proyecto: select existente o texto libre --}}
                    <x-ui.field label="Proyecto" :error="$errors->first('form.proyecto_id') ?: $errors->first('form.proyecto_texto')" class="md:col-span-2"
                        x-data="{ modo: {{ $form->proyecto_id ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-2 mb-1.5">
                            <button type="button" @click="modo = true" :class="modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Seleccionar existente</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="modo = false" :class="!modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Texto libre</button>
                        </div>
                        <div x-show="modo">
                            <x-ui.searchable-select
                                wire:key="proyecto-select"
                                wire-model="form.proyecto_id"
                                :options="$this->proyectosDisponibles->map(fn($p) => ['value' => $p->id, 'label' => $p->nombre.($p->codigo ? ' ('.$p->codigo.')' : '')])"
                                placeholder="— Selecciona proyecto —"
                            />
                        </div>
                        <div x-show="!modo">
                            <x-ui.input wire:model="form.proyecto_texto" placeholder="Escribe el nombre del proyecto…" />
                        </div>
                    </x-ui.field>

                    {{-- Cliente: select existente o texto libre --}}
                    <x-ui.field label="Cliente" :error="$errors->first('form.cliente_id') ?: $errors->first('form.cliente_texto')" class="md:col-span-2"
                        x-data="{ modo: {{ $form->cliente_id ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-2 mb-1.5">
                            <button type="button" @click="modo = true" :class="modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Seleccionar existente</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="modo = false" :class="!modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Texto libre</button>
                        </div>
                        <div x-show="modo">
                            <x-ui.searchable-select
                                wire:key="cliente-select"
                                wire-model="form.cliente_id"
                                :options="$this->clientesDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                                placeholder="— Selecciona cliente —"
                            />
                        </div>
                        <div x-show="!modo">
                            <x-ui.input wire:model="form.cliente_texto" placeholder="Escribe el nombre del cliente…" />
                        </div>
                    </x-ui.field>

                    {{-- Concepto: select existente o texto libre --}}
                    <x-ui.field label="Concepto" :error="$errors->first('form.concepto_id') ?: $errors->first('form.concepto_texto')"
                        x-data="{ modo: {{ $form->concepto_id ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-2 mb-1.5">
                            <button type="button" @click="modo = true" :class="modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Seleccionar existente</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="modo = false" :class="!modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Texto libre</button>
                        </div>
                        <div x-show="modo">
                            <x-ui.searchable-select
                                wire:key="concepto-select"
                                wire-model="form.concepto_id"
                                :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                                placeholder="— Sin concepto —"
                            />
                        </div>
                        <div x-show="!modo">
                            <x-ui.input wire:model="form.concepto_texto" placeholder="Escribe el concepto…" />
                        </div>
                    </x-ui.field>

                    {{-- Responsable --}}
                    <x-ui.field label="Responsable" :error="$errors->first('form.responsable_id')">
                        <x-ui.searchable-select
                            wire:key="responsable-select"
                            wire-model="form.responsable_id"
                            :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                            placeholder="— Sin responsable —"
                        />
                    </x-ui.field>

                    {{-- Fecha --}}
                    <x-ui.field label="Fecha" required :error="$errors->first('form.fecha')">
                        <x-ui.input type="date" wire:model="form.fecha" />
                    </x-ui.field>

                    {{-- Tipo de jornada --}}
                    <x-ui.field label="Tipo de jornada" required :error="$errors->first('form.tipo_hora')">
                        <x-ui.select wire:model="form.tipo_hora">
                            <option value="laboral">Laboral</option>
                            <option value="laboral_noche">Laboral (noche)</option>
                            <option value="festivo">Festivo</option>
                            <option value="festivo_noche">Festivo (noche)</option>
                        </x-ui.select>
                    </x-ui.field>

                    {{-- Observaciones --}}
                    <x-ui.field label="Observaciones" class="md:col-span-2" :error="$errors->first('form.observaciones')">
                        <x-ui.textarea wire:model="form.observaciones" rows="3" placeholder="Notas adicionales…" />
                    </x-ui.field>
                </div>
            </div>
        </form>

        {{-- ═══ Tab: Trabajadores ═══ --}}
        <div x-show="tab === 'trabajadores'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                    <p class="mt-0.5 text-xs text-slate-400">Trabajadores que participan en este parte</p>
                </div>
                <x-ui.button type="button" variant="success" wire:click="$call('form.addLineaPersonal')" icon="heroicon-o-plus">
                    Añadir
                </x-ui.button>
            </div>

            @if (count($form->lineasPersonal) === 0)
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin trabajadores. Pulsa «Añadir» para agregar uno.
                </div>
            @else
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Trabajador</th>
                                <th class="w-28 px-4 py-2.5 text-right">Horas</th>
                                <th class="w-28 px-4 py-2.5 text-right">H. Extra</th>
                                <th class="w-16 px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($form->lineasPersonal as $i => $linea)
                                <tr wire:key="linea-personal-{{ $i }}">
                                    <td class="px-6 py-2" x-data="{ modo: {{ isset($linea['trabajador_id']) && $linea['trabajador_id'] ? 'true' : 'false' }} }">
                                        <div class="flex items-center gap-2 mb-1">
                                            <button type="button" @click="modo = true" :class="modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Existente</button>
                                            <span class="text-slate-300">|</span>
                                            <button type="button" @click="modo = false" :class="!modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Texto libre</button>
                                        </div>
                                        <div x-show="modo">
                                            <x-ui.searchable-select
                                                wire:key="trab-select-{{ $i }}"
                                                wire-model="form.lineasPersonal.{{ $i }}.trabajador_id"
                                                :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                                                placeholder="— Selecciona —"
                                            />
                                        </div>
                                        <div x-show="!modo">
                                            <x-ui.input wire:model="form.lineasPersonal.{{ $i }}.trabajador_texto" placeholder="Nombre del trabajador…" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <x-ui.input type="number" step="0.5" min="0" max="24" wire:model="form.lineasPersonal.{{ $i }}.horas" class="text-right" />
                                    </td>
                                    <td class="px-4 py-2">
                                        <x-ui.input type="number" step="0.5" min="0" max="24" wire:model="form.lineasPersonal.{{ $i }}.horas_extra" class="text-right" />
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <x-ui.icon-button type="button" wire:click="$call('form.removeLineaPersonal', {{ $i }})" icon="heroicon-o-trash" variant="ghost-danger" tooltip="Eliminar" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ═══ Tab: Materiales ═══ --}}
        @if(\App\Support\Modulos::materialesAvanzado())
        <div x-show="tab === 'materiales'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <span class="text-sm font-semibold text-slate-900">Materiales</span>
                    <p class="mt-0.5 text-xs text-slate-400">Materiales utilizados en este parte</p>
                </div>
                <x-ui.button type="button" variant="success" wire:click="$call('form.addLineaMaterial')" icon="heroicon-o-plus">
                    Añadir
                </x-ui.button>
            </div>

            @if (count($form->lineasMaterial) === 0)
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin materiales. Pulsa «Añadir» para agregar uno.
                </div>
            @else
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Material</th>
                                <th class="w-32 px-4 py-2.5 text-right">Cantidad</th>
                                <th class="w-16 px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($form->lineasMaterial as $i => $linea)
                                <tr wire:key="linea-material-{{ $i }}">
                                    <td class="px-6 py-2" x-data="{ modo: {{ isset($linea['material_id']) && $linea['material_id'] ? 'true' : 'false' }} }">
                                        <div class="flex items-center gap-2 mb-1">
                                            <button type="button" @click="modo = true" :class="modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Existente</button>
                                            <span class="text-slate-300">|</span>
                                            <button type="button" @click="modo = false" :class="!modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Texto libre</button>
                                        </div>
                                        <div x-show="modo">
                                            <x-ui.searchable-select
                                                wire:key="mat-select-{{ $i }}"
                                                wire-model="form.lineasMaterial.{{ $i }}.material_id"
                                                :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion])"
                                                placeholder="— Selecciona —"
                                            />
                                        </div>
                                        <div x-show="!modo">
                                            <x-ui.input wire:model="form.lineasMaterial.{{ $i }}.material_texto" placeholder="Nombre del material…" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <x-ui.input type="number" step="0.01" min="0" wire:model="form.lineasMaterial.{{ $i }}.cantidad" class="text-right" />
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <x-ui.icon-button type="button" wire:click="$call('form.removeLineaMaterial', {{ $i }})" icon="heroicon-o-trash" variant="ghost-danger" tooltip="Eliminar" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif
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
                ¿Eliminar este borrador? Se enviará a la papelera.
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">Eliminar</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
