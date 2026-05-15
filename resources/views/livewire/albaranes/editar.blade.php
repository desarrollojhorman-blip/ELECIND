<div class="space-y-4">
    <x-ui.page-header :title="$titulo" subtitle="Cabecera y líneas del albarán.">
        <x-slot:actions>
            @if ($albaran)
                <x-ui.button as="a" href="{{ route('albaranes.index') }}" wire:navigate variant="ghost" icon="heroicon-o-arrow-left">
                    Albaranes
                </x-ui.button>
            @else
                <x-ui.button as="a" href="{{ route('albaranes.index') }}" wire:navigate variant="ghost" icon="heroicon-o-x-mark">
                    Cancelar
                </x-ui.button>
            @endif
            @if ($albaran)
                @can('delete', $albaran)
                    <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                        Eliminar
                    </x-ui.button>
                @endcan
            @endif
            <x-ui.button variant="success" type="submit" form="form-albaran" wire:loading.attr="disabled" icon="heroicon-o-check">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Cabecera --}}
    <form wire:submit="guardar" id="form-albaran" autocomplete="off">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Proyecto" required :error="$errors->first('form.proyecto_id')" class="md:col-span-2">
                    <x-ui.searchable-select
                        wire:key="proyecto-select"
                        wire-model="form.proyecto_id"
                        :options="$this->proyectosDisponibles->map(fn($p) => ['value' => $p->id, 'label' => $p->nombre.($p->codigo ? ' ('.$p->codigo.')' : '')])"
                        placeholder="— Selecciona proyecto —"
                    />
                </x-ui.field>

                <x-ui.field label="Fecha" required :error="$errors->first('form.fecha')">
                    <x-ui.input type="date" wire:model="form.fecha" />
                </x-ui.field>

                <x-ui.field label="Tipo de jornada" required :error="$errors->first('form.tipo_hora')">
                    <x-ui.select wire:model="form.tipo_hora">
                        @foreach ($tiposHora as $tipo)
                            <option value="{{ $tipo->value }}">{{ $tipo->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Concepto" :error="$errors->first('form.concepto_id')">
                    <x-ui.searchable-select
                        wire:key="concepto-select-{{ $form->proyecto_id }}"
                        wire-model="form.concepto_id"
                        :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                        placeholder="— Sin concepto —"
                    />
                </x-ui.field>

                <x-ui.field label="Responsable" :error="$errors->first('form.responsable_id')">
                    <x-ui.searchable-select
                        wire:key="responsable-select"
                        wire-model="form.responsable_id"
                        :options="$this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                        placeholder="— Sin responsable —"
                    />
                </x-ui.field>

                <x-ui.field label="Observaciones" class="md:col-span-2" :error="$errors->first('form.observaciones')">
                    <x-ui.textarea wire:model="form.observaciones" rows="3" placeholder="Notas adicionales del parte…" />
                </x-ui.field>
            </div>
        </div>
    </form>

    {{-- Mis horas --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-slate-900">Mis horas</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field label="Horas normales" required :error="$errors->first('form.mi_horas')">
                <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="form.mi_horas" />
            </x-ui.field>
            <x-ui.field label="Horas extra" :error="$errors->first('form.mi_horas_extra')">
                <x-ui.input type="number" min="0" max="24" step="0.25" wire:model="form.mi_horas_extra" />
            </x-ui.field>
        </div>
    </div>

    {{-- Compañeros --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Compañeros</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ count($form->companeros) }}</span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Otros trabajadores que participaron en este parte</p>
            </div>
            <x-ui.button type="button" variant="info" wire:click="agregarCompanero" icon="heroicon-o-plus">
                Añadir compañero
            </x-ui.button>
        </div>

        @if (count($form->companeros) > 0)
            <div class="border-t border-slate-100">
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Trabajador</th>
                            <th class="px-4 py-2.5 w-32">Horas</th>
                            <th class="px-4 py-2.5 w-32">H. extra</th>
                            <th class="px-4 py-2.5 text-right w-20">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($form->companeros as $i => $companero)
                            <tr wire:key="companero-{{ $i }}">
                                <td class="px-6 py-3">
                                    <x-ui.searchable-select
                                        wire:key="companero-sel-{{ $companeroSelectKey }}-{{ $i }}"
                                        wire-model="form.companeros.{{ $i }}.trabajador_id"
                                        :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                                        placeholder="— Selecciona —"
                                    />
                                    @error("form.companeros.{$i}.trabajador_id")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.input type="number" min="0" max="24" step="0.25"
                                                wire:model="form.companeros.{{ $i }}.horas" />
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.input type="number" min="0" max="24" step="0.25"
                                                wire:model="form.companeros.{{ $i }}.horas_extra" />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-ui.icon-button
                                        wire:click="quitarCompanero({{ $i }})"
                                        icon="heroicon-o-x-mark"
                                        variant="danger"
                                        tooltip="Quitar" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Materiales --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Materiales</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ count($form->materiales) }}</span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Materiales utilizados en este parte</p>
            </div>
            <x-ui.button type="button" variant="info" wire:click="agregarMaterial" icon="heroicon-o-plus">
                Añadir material
            </x-ui.button>
        </div>

        @if (count($form->materiales) > 0)
            <div class="border-t border-slate-100">
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Material</th>
                            <th class="px-4 py-2.5 w-36">Cantidad</th>
                            <th class="px-4 py-2.5 text-right w-20">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($form->materiales as $i => $linea)
                            <tr wire:key="material-{{ $i }}">
                                <td class="px-6 py-3">
                                    <x-ui.searchable-select
                                        wire:key="material-sel-{{ $materialSelectKey }}-{{ $i }}"
                                        wire-model="form.materiales.{{ $i }}.material_id"
                                        :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | '.$m->stock.' '.$m->unidad_medida])"
                                        placeholder="— Selecciona —"
                                    />
                                    @error("form.materiales.{$i}.material_id")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.input type="number" min="0.01" step="0.01"
                                                wire:model="form.materiales.{{ $i }}.cantidad" />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-ui.icon-button
                                        wire:click="quitarMaterial({{ $i }})"
                                        icon="heroicon-o-x-mark"
                                        variant="danger"
                                        tooltip="Quitar" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

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
            <x-ui.button variant="ghost" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
