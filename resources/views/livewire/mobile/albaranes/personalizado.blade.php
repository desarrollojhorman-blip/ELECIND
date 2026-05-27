<div>
    <form wire:submit="guardar" id="form-personalizado" class="px-4 pb-4 pt-3">

        {{-- Badge informativo --}}
        <div class="mb-4 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5">
            <x-heroicon-o-information-circle class="size-4 shrink-0 text-amber-600" />
            <p class="text-xs text-amber-700">
                Usa este formulario cuando el proyecto o los datos no estén dados de alta. El administrador lo normalizará después.
            </p>
        </div>

        {{-- ─── Datos generales ──────────────────────────────── --}}
        <x-mobile.section-title>Datos generales</x-mobile.section-title>

        <div class="space-y-3">

            {{-- Cliente --}}
            @if (! $form->clienteOtro)
                <x-mobile.field label="Cliente" required :error="$errors->first('form.cliente_id')">
                    <div wire:key="cli-fixed">
                        <x-ui.searchable-select
                            wire-model="form.cliente_id"
                            :options="$this->clientesDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->id.' · '.$c->nombre])"
                            placeholder="— Selecciona cliente —"
                        />
                    </div>
                </x-mobile.field>
            @else
                <x-mobile.field label="Cliente (texto libre)" required :error="$errors->first('form.clienteTexto')">
                    <x-ui.input wire:model="form.clienteTexto" placeholder="Nombre del cliente" />
                </x-mobile.field>
            @endif

            <button type="button"
                    wire:click="toggleClienteOtro"
                    class="flex items-center gap-1.5 text-xs font-medium text-primary-600 hover:text-primary-800">
                <x-heroicon-o-arrow-path class="size-3.5" />
                {{ $form->clienteOtro ? '← Seleccionar cliente del sistema' : '¿No está en el sistema? Escribirlo →' }}
            </button>

            {{-- Proyecto --}}
            @if ($form->clienteOtro)
                {{-- Cliente libre → proyecto siempre libre --}}
                <x-mobile.field label="Proyecto (texto libre)" required :error="$errors->first('form.proyectoTexto')">
                    <x-ui.input wire:model="form.proyectoTexto" placeholder="Nombre del proyecto / obra" />
                </x-mobile.field>
            @elseif (! $form->proyectoOtro)
                <x-mobile.field label="Proyecto" required :error="$errors->first('form.proyecto_id')">
                    <div wire:key="proj-{{ $selectKey }}">
                        <x-ui.searchable-select
                            wire-model="form.proyecto_id"
                            :options="$this->proyectosPorCliente->map(fn($p) => ['value' => $p->id, 'label' => $p->nombre.($p->codigo ? ' ('.$p->codigo.')' : '')])"
                            placeholder="{{ $form->cliente_id ? '— Selecciona proyecto —' : '— Selecciona cliente primero —' }}"
                        />
                    </div>
                </x-mobile.field>
            @else
                <x-mobile.field label="Proyecto (texto libre)" required :error="$errors->first('form.proyectoTexto')">
                    <x-ui.input wire:model="form.proyectoTexto" placeholder="Nombre del proyecto / obra" />
                </x-mobile.field>
            @endif

            @if (! $form->clienteOtro)
                <button type="button"
                        wire:click="toggleProyectoOtro"
                        class="flex items-center gap-1.5 text-xs font-medium text-primary-600 hover:text-primary-800">
                    <x-heroicon-o-arrow-path class="size-3.5" />
                    {{ $form->proyectoOtro ? '← Seleccionar proyecto del sistema' : '¿No está en el sistema? Escribirlo →' }}
                </button>
            @endif

            {{-- Concepto --}}
            @if (! $form->conceptoOtro)
                <x-mobile.field label="Concepto" :error="$errors->first('form.concepto_id')">
                    <div wire:key="conc-{{ $selectKey }}">
                        <x-ui.searchable-select
                            wire-model="form.concepto_id"
                            :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                            placeholder="— Sin concepto —"
                        />
                    </div>
                </x-mobile.field>
            @else
                <x-mobile.field label="Tipo de trabajo (texto libre)" required :error="$errors->first('form.conceptoTexto')">
                    <x-ui.input wire:model="form.conceptoTexto" placeholder="Ej: Instalación eléctrica, Mantenimiento…" />
                </x-mobile.field>
            @endif

            <button type="button"
                    wire:click="toggleConceptoOtro"
                    class="flex items-center gap-1.5 text-xs font-medium text-primary-600 hover:text-primary-800">
                <x-heroicon-o-arrow-path class="size-3.5" />
                {{ $form->conceptoOtro ? '← Seleccionar concepto del sistema' : '¿No está en el sistema? Escribirlo →' }}
            </button>

            {{-- Responsable --}}
            @if (! $form->responsableOtro)
                <x-mobile.field label="Responsable" :error="$errors->first('form.responsable_id')">
                    <div wire:key="resp-{{ $selectKey }}">
                        <x-ui.searchable-select
                            wire-model="form.responsable_id"
                            :options="$this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                            placeholder="— Sin asignar —"
                        />
                    </div>
                </x-mobile.field>
            @else
                <x-mobile.field label="Responsable (texto libre)" required :error="$errors->first('form.responsableTexto')">
                    <x-ui.input wire:model="form.responsableTexto" placeholder="Nombre y apellidos del responsable" />
                </x-mobile.field>
            @endif

            <button type="button"
                    wire:click="toggleResponsableOtro"
                    class="flex items-center gap-1.5 text-xs font-medium text-primary-600 hover:text-primary-800">
                <x-heroicon-o-arrow-path class="size-3.5" />
                {{ $form->responsableOtro ? '← Seleccionar responsable del sistema' : '¿No está en el sistema? Escribirlo →' }}
            </button>

            {{-- Fecha y tipo hora --}}
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

        {{-- ─── Mis horas ────────────────────────────────────── --}}
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

        {{-- ─── Compañeros ───────────────────────────────────── --}}
        <x-mobile.section-title :hint="count($form->companeros).' añadidos'">Compañeros</x-mobile.section-title>

        <div class="space-y-3">
            @foreach ($form->companeros as $index => $companero)
                @php
                    $compOcupados = collect($form->companeros)->forget($index)->pluck('trabajador_id')->filter()->flip()->all();
                @endphp
                <x-mobile.line-card
                    :title="'Compañero #'.($index + 1)"
                    :remove-action="'removeCompanero('.$index.')'"
                    wire:key="comp-{{ $index }}">
                    <div class="space-y-3">
                        <x-mobile.field label="Trabajador" required :error="$errors->first('form.companeros.'.$index.'.trabajador_id')">
                            <div wire:key="comp-sel-{{ $selectKey }}-{{ $index }}">
                                <x-ui.searchable-select
                                    wire-model="form.companeros.{{ $index }}.trabajador_id"
                                    :options="$this->companerosDisponibles->reject(fn($u) => isset($compOcupados[$u->id]))->map(fn($u) => ['value' => $u->id, 'label' => ($u->numero_empleado ? $u->numero_empleado.' · ' : '').trim($u->nombre.' '.$u->apellidos)])->values()"
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
        {{-- ─── Materiales ───────────────────────────────────── --}}
        <x-mobile.section-title :hint="count($form->materiales).' añadidos'">Materiales</x-mobile.section-title>

        <div class="space-y-3">
            @foreach ($form->materiales as $index => $material)
                @php
                    $matSeleccionado = $this->materialesDisponibles->firstWhere('id', $material['material_id'] ?? null);
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
                                    :options="$this->materialesDisponibles->reject(fn($m) => isset($matOcupados[$m->id]))->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | '.rtrim(rtrim(number_format((float)$m->stock,2,',',''),'0'),',').' '.$m->unidad_medida])->values()"
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
                form="form-personalizado"
                wire:loading.attr="disabled"
                class="w-full rounded-md bg-emerald-600 px-3 py-3 text-base font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 active:scale-[0.99] active:transition-transform disabled:opacity-50">
            <span wire:loading.remove>Guardar</span>
            <span wire:loading>Guardando…</span>
        </button>
    </x-mobile.bottom-bar>

    {{-- Modal post-creación --}}
    @if ($borradorCreadoId !== null)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 px-0 pb-0 sm:items-center sm:px-4 sm:pb-4"
             x-data x-init="$el.scrollIntoView({ behavior: 'smooth' })">
            <div class="w-full max-w-sm overflow-hidden rounded-t-2xl bg-white shadow-xl sm:rounded-2xl">
                <div class="flex flex-col items-center gap-2 bg-slate-50 px-6 pt-6 pb-4 text-center">
                    <div class="flex size-12 items-center justify-center rounded-full bg-green-100 text-green-600">
                        <x-heroicon-o-document-check class="size-6" />
                    </div>
                    <h2 class="text-base font-semibold text-slate-900">Borrador enviado</h2>
                    <p class="text-sm text-slate-500">El parte ha quedado guardado como borrador. El administrador lo revisará y lo convertirá en albarán.</p>
                </div>
                <div class="px-6 py-5">
                    <button type="button" wire:click="irAlDashboard" wire:loading.attr="disabled"
                            class="flex w-full items-center gap-4 rounded-xl border-2 border-primary-500 bg-primary-50 px-4 py-3.5 text-left transition hover:bg-primary-100 active:scale-[0.99]">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white">
                            <x-heroicon-o-home class="size-5" />
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-primary-800">Volver al inicio</span>
                            <span class="block text-xs text-primary-600">El borrador queda pendiente de revisión por el administrador.</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
