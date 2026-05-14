<div>
    <form wire:submit="guardar" id="form-albaran" class="px-4 pb-4 pt-3">
        {{-- ─── Cabecera ─────────────────────────────────────────── --}}
        <x-mobile.section-title>Datos generales</x-mobile.section-title>

        <div class="space-y-3">
            <x-mobile.field label="Proyecto" required :error="$errors->first('form.proyecto_id')">
                <x-ui.select wire:model.live="form.proyecto_id">
                    <option value="">— Selecciona proyecto —</option>
                    @foreach ($this->proyectosDisponibles as $proyecto)
                        <option value="{{ $proyecto->id }}">{{ $proyecto->nombre }}</option>
                    @endforeach
                </x-ui.select>
            </x-mobile.field>

            @if ($form->cliente_id)
                <x-mobile.field label="Cliente">
                    <p class="rounded-md bg-slate-100 px-3 py-2 text-sm text-slate-700">
                        {{ \App\Models\Cliente::find($form->cliente_id)?->nombre ?? '—' }}
                    </p>
                </x-mobile.field>
            @endif

            <x-mobile.field label="Concepto" :error="$errors->first('form.concepto_id')">
                <x-ui.select wire:model="form.concepto_id" :disabled="$form->proyecto_id === null">
                    <option value="">— Sin concepto —</option>
                    @foreach ($this->conceptosDisponibles as $concepto)
                        <option value="{{ $concepto->id }}">{{ $concepto->nombre }}</option>
                    @endforeach
                </x-ui.select>
            </x-mobile.field>

            <x-mobile.field label="Responsable del proyecto" :error="$errors->first('form.responsable_id')">
                <x-ui.select wire:model="form.responsable_id" :disabled="$form->proyecto_id === null">
                    <option value="">— Sin asignar —</option>
                    @foreach ($this->usuariosProyecto as $usuario)
                        <option value="{{ $usuario->id }}">{{ trim($usuario->nombre.' '.$usuario->apellidos) }}</option>
                    @endforeach
                </x-ui.select>
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
                        <x-mobile.field label="Trabajador" required :error="$errors->first('form.companeros.'.$index.'.trabajador_id')">
                            <x-ui.select wire:model="form.companeros.{{ $index }}.trabajador_id"
                                         :disabled="$form->proyecto_id === null">
                                <option value="">— Selecciona —</option>
                                @foreach ($this->usuariosProyecto as $usuario)
                                    @if ($usuario->id !== auth()->id())
                                        <option value="{{ $usuario->id }}">{{ trim($usuario->nombre.' '.$usuario->apellidos) }}</option>
                                    @endif
                                @endforeach
                            </x-ui.select>
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
                @disabled($form->proyecto_id === null)
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700 disabled:cursor-not-allowed disabled:opacity-50">
            <x-heroicon-o-plus class="size-4" />
            Añadir compañero
        </button>

        {{-- ─── Materiales ───────────────────────────────────────── --}}
        <x-mobile.section-title :hint="count($form->materiales).' añadidos'">Materiales</x-mobile.section-title>

        <div class="space-y-3">
            @foreach ($form->materiales as $index => $material)
                @php
                    $matSeleccionado = $this->materialesProyecto->firstWhere('id', $material['material_id'] ?? null);
                @endphp
                <x-mobile.line-card
                    :title="'Material #'.($index + 1)"
                    :remove-action="'removeMaterial('.$index.')'"
                    wire:key="mat-{{ $index }}">
                    <div class="space-y-3">
                        <x-mobile.field label="Material" required :error="$errors->first('form.materiales.'.$index.'.material_id')">
                            <x-ui.select wire:model.live="form.materiales.{{ $index }}.material_id"
                                         :disabled="$form->proyecto_id === null">
                                <option value="">— Selecciona —</option>
                                @foreach ($this->materialesProyecto as $mat)
                                    @php
                                        $stockFmt = rtrim(rtrim(number_format((float) $mat->stock, 2, ',', ''), '0'), ',');
                                    @endphp
                                    <option value="{{ $mat->id }}">
                                        {{ $mat->descripcion }} | {{ $stockFmt }} {{ $mat->unidad_medida }}
                                    </option>
                                @endforeach
                            </x-ui.select>
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
                @disabled($form->proyecto_id === null)
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-600 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700 disabled:cursor-not-allowed disabled:opacity-50">
            <x-heroicon-o-plus class="size-4" />
            Añadir material
        </button>
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
</div>
