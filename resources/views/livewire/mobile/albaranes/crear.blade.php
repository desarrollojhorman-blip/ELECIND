<div>
    <form wire:submit="guardar" id="form-albaran" class="px-4 pb-4 pt-3">
        {{-- ─── Cabecera ─────────────────────────────────────────── --}}
        <x-mobile.section-title>Datos generales</x-mobile.section-title>

        <div class="space-y-3">
            <x-mobile.field label="Proyecto" required :error="$errors->first('form.proyecto_id')">
                <x-ui.searchable-select
                    wire-model="form.proyecto_id"
                    :options="$this->proyectosDisponibles->map(fn($p) => ['value' => $p->id, 'label' => $p->cliente->nombre.' · '.$p->nombre.($p->codigo ? ' ('.$p->codigo.')' : '')])"
                    placeholder="— Selecciona proyecto —"
                />
            </x-mobile.field>

            <x-mobile.field label="Concepto" :error="$errors->first('form.concepto_id')">
                <select wire:model="form.concepto_id"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-white">
                    <option value="">— Sin concepto —</option>
                    @foreach($this->conceptosDisponibles as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </x-mobile.field>

            <x-mobile.field label="Responsable" :error="$errors->first('form.responsable_id')">
                <div wire:key="resp-{{ $selectKey }}">
                    <x-ui.searchable-select
                        wire-model="form.responsable_id"
                        :options="$this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                        placeholder="— Sin asignar —"
                    />
                </div>
            </x-mobile.field>

            <div class="grid grid-cols-2 gap-3">
                <x-mobile.field label="Fecha" required :error="$errors->first('form.fecha')">
                    <x-ui.input type="date" wire:model="form.fecha" />
                </x-mobile.field>

                <x-mobile.field label="Tipo de hora" required :error="$errors->first('form.tipo_hora')">
                    <x-ui.select wire:model="form.tipo_hora">
                        @foreach ($tiposHora as $tipo)
                            <option value="{{ $tipo->value }}">{{ $tipo->etiqueta() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-mobile.field>
            </div>

            <x-mobile.field label="Observaciones" :error="$errors->first('form.observaciones')">
                <x-ui.textarea wire:model="form.observaciones" rows="2" />
            </x-mobile.field>
        </div>

        {{-- ─── Mis horas ────────────────────────────────────────── --}}
        <x-mobile.section-title hint="Tu línea como creador">Mis horas</x-mobile.section-title>

        <x-mobile.line-card>
            <div class="space-y-3">
                <div class="rounded-md bg-primary-50 px-3 py-2 text-sm text-primary-800">
                    <strong>{{ trim(auth()->user()->nombre.' '.auth()->user()->apellidos) }}</strong>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-mobile.field label="Horas" required :error="$errors->first('form.mi_horas')">
                        <x-ui.input type="number" step="0.25" min="0" max="24" wire:model="form.mi_horas" />
                    </x-mobile.field>

                    <x-mobile.field label="Horas extra" :error="$errors->first('form.mi_horas_extra')">
                        <x-ui.input type="number" step="0.25" min="0" max="24" wire:model="form.mi_horas_extra" />
                    </x-mobile.field>
                </div>
            </div>
        </x-mobile.line-card>

        {{-- ─── Compañeros ───────────────────────────────────────── --}}
        <x-mobile.section-title :hint="count($form->companeros).' añadidos'">Compañeros</x-mobile.section-title>

        <div class="space-y-3">
            @foreach ($form->companeros as $index => $companero)
                <x-mobile.line-card
                    :title="'Compañero #'.($index + 1)"
                    :remove-action="'removeCompanero('.$index.')'"
                    wire:key="comp-{{ $index }}">
                    <div class="space-y-3">
                        @php
                            $compOcupados = collect($form->companeros)->forget($index)->pluck('trabajador_id')->filter()->flip()->all();
                        @endphp
                        <x-mobile.field label="Trabajador" required :error="$errors->first('form.companeros.'.$index.'.trabajador_id')">
                            <div wire:key="comp-sel-{{ $selectKey }}-{{ $index }}">
                                <x-ui.searchable-select
                                    wire-model="form.companeros.{{ $index }}.trabajador_id"
                                    :options="$this->companerosDisponibles->reject(fn($u) => isset($compOcupados[$u->id]))->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])->values()"
                                    placeholder="— Selecciona —"
                                />
                            </div>
                        </x-mobile.field>

                        <div class="grid grid-cols-2 gap-3">
                            <x-mobile.field label="Horas" required :error="$errors->first('form.companeros.'.$index.'.horas')">
                                <x-ui.input type="number" step="0.25" min="0" max="24"
                                            wire:model="form.companeros.{{ $index }}.horas" />
                            </x-mobile.field>

                            <x-mobile.field label="Horas extra" :error="$errors->first('form.companeros.'.$index.'.horas_extra')">
                                <x-ui.input type="number" step="0.25" min="0" max="24"
                                            wire:model="form.companeros.{{ $index }}.horas_extra" />
                            </x-mobile.field>
                        </div>
                    </div>
                </x-mobile.line-card>
            @endforeach
        </div>

        <button type="button"
                wire:click="addCompanero"
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">
            <x-heroicon-o-plus class="size-4" />
            Añadir compañero
        </button>

        @if(\App\Support\Modulos::materialesAvanzado())
        {{-- ─── Materiales ───────────────────────────────────────── --}}
        <x-mobile.section-title :hint="count($form->materiales).' añadidos'">Materiales</x-mobile.section-title>

        <div class="space-y-3">
            @foreach ($form->materiales as $index => $material)
                @php
                    $matSeleccionado = $this->materialesProyecto->firstWhere('id', $material['material_id'] ?? null);
                    $matOcupados = collect($form->materiales)->forget($index)->pluck('material_id')->filter()->flip()->all();
                @endphp
                <x-mobile.line-card
                    :title="'Material #'.($index + 1)"
                    :remove-action="'removeMaterial('.$index.')'"
                    wire:key="mat-{{ $index }}">
                    <div class="space-y-3">
                        <x-mobile.field label="Material" required :error="$errors->first('form.materiales.'.$index.'.material_id')">
                            <div wire:key="mat-sel-{{ $selectKey }}-{{ $index }}">
                                <x-ui.searchable-select
                                    wire-model="form.materiales.{{ $index }}.material_id"
                                    :options="$this->materialesProyecto->reject(fn($m) => isset($matOcupados[$m->id]))->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | '.rtrim(rtrim(number_format((float)$m->stock,2,',',''),'0'),',').' '.$m->unidad_medida])->values()"
                                    placeholder="— Selecciona —"
                                />
                            </div>
                        </x-mobile.field>

                        <x-mobile.field label="Cantidad" required :error="$errors->first('form.materiales.'.$index.'.cantidad')">
                            <div class="flex items-stretch gap-2">
                                <div class="min-w-0 flex-1">
                                    <x-ui.input type="number" step="0.01" min="0.01"
                                                wire:model="form.materiales.{{ $index }}.cantidad" />
                                </div>
                                <span class="inline-flex shrink-0 items-center rounded-md bg-slate-100 px-3 text-sm font-medium text-slate-600">
                                    {{ $matSeleccionado?->unidad_medida ?? '—' }}
                                </span>
                            </div>
                        </x-mobile.field>
                    </div>
                </x-mobile.line-card>
            @endforeach
        </div>

        <button type="button"
                wire:click="addMaterial"
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">
            <x-heroicon-o-plus class="size-4" />
            Añadir material
        </button>
        @endif
    </form>

    {{-- ─── Bottom bar ───────────────────────────────────────── --}}
    <x-mobile.bottom-bar>
        <button type="submit"
                form="form-albaran"
                wire:loading.attr="disabled"
                class="w-full rounded-md bg-emerald-600 px-3 py-3 text-base font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 active:scale-[0.99] active:transition-transform disabled:opacity-50">
            <span wire:loading.remove>Guardar</span>
            <span wire:loading>Guardando…</span>
        </button>
    </x-mobile.bottom-bar>

    {{-- Modal post-creación: ¿firmar ahora o luego? --}}
    @if ($albaranCreadoId !== null)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 px-0 pb-0 sm:items-center sm:px-4 sm:pb-4"
             x-data x-init="$el.scrollIntoView({ behavior: 'smooth' })">
            <div class="w-full max-w-sm overflow-hidden rounded-t-2xl bg-white shadow-xl sm:rounded-2xl">

                {{-- Cabecera --}}
                <div class="flex flex-col items-center gap-2 bg-slate-50 px-6 pt-6 pb-4 text-center">
                    <div class="flex size-12 items-center justify-center rounded-full bg-green-100 text-green-600">
                        <x-heroicon-o-document-check class="size-6" />
                    </div>
                    <h2 class="text-base font-semibold text-slate-900">Parte creado</h2>
                    <p class="text-sm text-slate-500">
                        ¿Quieres firma el parte ahora mismo o prefieres hacerlo después?
                    </p>
                </div>

                {{-- Opciones --}}
                <div class="space-y-3 px-6 py-5">
                    {{-- Firmar ahora --}}
                    <button
                        type="button"
                        wire:click="irAFirmar"
                        wire:loading.attr="disabled"
                        class="flex w-full items-center gap-4 rounded-xl border-2 border-primary-500 bg-primary-50 px-4 py-3.5 text-left transition hover:bg-primary-100 active:scale-[0.99]"
                    >
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white">
                            <x-heroicon-o-pencil class="size-5" />
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-primary-800">Firmar ahora</span>
                            <span class="block text-xs text-primary-600">El responsable puede firmar también en este momento.</span>
                        </span>
                    </button>

                    {{-- Firmar después --}}
                    <button
                        type="button"
                        wire:click="irAlDashboard"
                        wire:loading.attr="disabled"
                        class="flex w-full items-center gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-left transition hover:bg-slate-50 active:scale-[0.99]"
                    >
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <x-heroicon-o-clock class="size-5" />
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-700">Firmar más tarde</span>
                            <span class="block text-xs text-slate-500">Vuelve al inicio. El parte queda guardado como borrador.</span>
                        </span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
