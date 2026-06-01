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
                @if ($borrador->estado !== 'convertido')
                    @can('convertir', $borrador)
                        <x-ui.button as="a" href="{{ route('borradores.convertir', $borrador) }}" wire:navigate
                                     variant="primary" icon="heroicon-o-arrow-right-circle">
                            Convertir a albarán
                        </x-ui.button>
                    @endcan
                @endif
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
            <x-ui.button type="button" variant="neutral" wire:click="deshacer" wire:loading.attr="disabled" wire:target="deshacer">
                <x-heroicon-o-arrow-uturn-left wire:loading.remove wire:target="deshacer" class="size-4" />
                <svg wire:loading wire:target="deshacer" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="deshacer">Deshacer</span>
                <span wire:loading wire:target="deshacer">Deshaciendo…</span>
            </x-ui.button>
            <x-ui.button type="submit" form="form-borrador" variant="primary" wire:loading.attr="disabled" wire:target="guardar">
                <x-heroicon-o-check wire:loading.remove wire:target="guardar" class="size-4" />
                <svg wire:loading wire:target="guardar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
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

            @php $modoCrear = $borrador === null; @endphp
            @foreach (array_values(array_filter([
                ['key' => 'trabajadores', 'label' => 'Trabajadores', 'count' => count($form->lineasPersonal)],
                \App\Support\Modulos::materialesAvanzado() ? ['key' => 'materiales', 'label' => 'Materiales', 'count' => count($form->lineasMaterial)] : false,
                ['key' => 'firmas', 'label' => 'Firmas', 'count' => null],
            ])) as $t)
                @if ($modoCrear)
                    <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                          title="Guarda primero el borrador para acceder a esta sección">
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
                                :value="$form->proyecto_id"
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
                                :value="$form->cliente_id"
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
                            @can('create', App\Models\Concepto::class)
                                <span class="text-slate-300">|</span>
                                <button type="button" wire:click="abrirNuevoConcepto" @click="modo = true"
                                        class="inline-flex items-center gap-0.5 text-xs font-medium text-emerald-600 hover:text-emerald-800">
                                    <x-heroicon-m-plus class="size-3" /> Crear nuevo
                                </button>
                            @endcan
                        </div>
                        <div x-show="modo">
                            <x-ui.searchable-select
                                wire:key="concepto-select"
                                wire-model="form.concepto_id"
                                :value="$form->concepto_id"
                                :options="$this->conceptosDisponibles->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                                placeholder="— Sin concepto —"
                            />
                        </div>
                        <div x-show="!modo">
                            <x-ui.input wire:model="form.concepto_texto" placeholder="Escribe el concepto…" />
                        </div>
                    </x-ui.field>

                    {{-- Responsable: select existente o texto libre --}}
                    <x-ui.field label="Responsable" :error="$errors->first('form.responsable_id') ?: $errors->first('form.responsable_texto')"
                        x-data="{ modo: {{ $form->responsable_id ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-2 mb-1.5">
                            <button type="button" @click="modo = true" :class="modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Seleccionar existente</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="modo = false" :class="!modo ? 'text-primary-700 font-semibold' : 'text-slate-400'" class="text-xs">Texto libre</button>
                        </div>
                        <div x-show="modo">
                            <x-ui.searchable-select
                                wire:key="responsable-select"
                                wire-model="form.responsable_id"
                                :value="$form->responsable_id"
                                :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                                placeholder="— Sin responsable —"
                            />
                        </div>
                        <div x-show="!modo">
                            <x-ui.input wire:model="form.responsable_texto" placeholder="Escribe el nombre del responsable…" />
                        </div>
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
                                                :value="$linea['trabajador_id'] ?? null"
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
                                                :value="$linea['material_id'] ?? null"
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
        {{-- ═══ Tab: Firmas ═══ --}}
        <div x-show="tab === 'firmas'"
             x-data="{ notificarTrab: false, notificarResp: false }">

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

                @error('firma')
                    <div class="mx-6 mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div>
                @enderror

                <div class="grid gap-px border-t border-slate-100 bg-slate-100 md:grid-cols-2">

                    {{-- ── Firmante: Empleado ── --}}
                    <div class="bg-white p-6"
                         x-data="{ esOtro: {{ $form->firma_trabajador_otro_nombre ? 'true' : 'false' }} }">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800">Empleado</h4>
                                <p class="text-xs text-slate-500">Quien firma por parte del trabajador</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <label class="flex cursor-pointer items-center gap-1.5 text-xs text-slate-600">
                                    <input type="checkbox" x-model="notificarTrab" class="size-3.5 rounded border-slate-300" />
                                    Notificar
                                </label>
                                @if ($borrador?->tokensFirma->where('tipo_firmante.value', 'trabajador')->isNotEmpty())
                                    @php $t = $borrador->tokensFirma->where('tipo_firmante.value', 'trabajador')->sortByDesc('created_at')->first(); @endphp
                                    <span class="text-xs text-slate-400">Último: {{ $t->created_at->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="text-xs text-slate-300">Sin envíos</span>
                                @endif
                            </div>
                        </div>

                        <div x-show="!esOtro" class="space-y-2">
                            <x-ui.field label="Empleado firmante">
                                <x-ui.searchable-select
                                    wire:key="firma-trab-borrador"
                                    wire-model="form.firma_trabajador_user_id"
                                    :value="$form->firma_trabajador_user_id"
                                    :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
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
                            <x-ui.field label="Nombre">
                                <x-ui.input wire:model.defer="form.firma_trabajador_otro_nombre" placeholder="Nombre completo" />
                            </x-ui.field>
                            <x-ui.field label="Correo">
                                <x-ui.input type="email" wire:model.defer="form.firma_trabajador_otro_correo" placeholder="correo@ejemplo.com" />
                            </x-ui.field>
                            <button type="button"
                                    @click="esOtro = false; $wire.set('form.firma_trabajador_otro_nombre', null); $wire.set('form.firma_trabajador_otro_correo', null)"
                                    class="text-xs text-slate-400 underline hover:text-slate-600">
                                ← Usar usuario del sistema
                            </button>
                        </div>

                        <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5">
                            @if ($firmaTrabajador)
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 text-xs text-green-700">
                                        <x-heroicon-o-check-circle class="size-4" />
                                        Firmado el {{ $firmaTrabajador->firmado_at->format('d/m/Y H:i') }}
                                    </div>
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($firmaTrabajador->firma_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                        <x-heroicon-o-arrow-down-tray class="size-3.5" />
                                        Descargar
                                    </a>
                                </div>
                            @else
                                <p class="text-xs text-slate-400">Sin firma registrada</p>
                            @endif
                        </div>

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
                                <p class="text-xs text-slate-500">Quien firma por parte del cliente / empresa</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <label class="flex cursor-pointer items-center gap-1.5 text-xs text-slate-600">
                                    <input type="checkbox" x-model="notificarResp" class="size-3.5 rounded border-slate-300" />
                                    Notificar
                                </label>
                                @if ($borrador?->tokensFirma->where('tipo_firmante.value', 'responsable')->isNotEmpty())
                                    @php $t = $borrador->tokensFirma->where('tipo_firmante.value', 'responsable')->sortByDesc('created_at')->first(); @endphp
                                    <span class="text-xs text-slate-400">Último: {{ $t->created_at->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="text-xs text-slate-300">Sin envíos</span>
                                @endif
                            </div>
                        </div>

                        <div x-show="!esOtro" class="space-y-2">
                            <x-ui.field label="Responsable">
                                <x-ui.searchable-select
                                    wire:key="firma-resp-borrador"
                                    wire-model="form.responsable_id"
                                    :value="$form->responsable_id"
                                    :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
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
                            <x-ui.field label="Nombre">
                                <x-ui.input wire:model.defer="form.firma_responsable_otro_nombre" placeholder="Nombre completo" />
                            </x-ui.field>
                            <x-ui.field label="Correo">
                                <x-ui.input type="email" wire:model.defer="form.firma_responsable_otro_correo" placeholder="correo@ejemplo.com" />
                            </x-ui.field>
                            <button type="button"
                                    @click="esOtro = false; $wire.set('form.firma_responsable_otro_nombre', null); $wire.set('form.firma_responsable_otro_correo', null)"
                                    class="text-xs text-slate-400 underline hover:text-slate-600">
                                ← Usar usuario del sistema
                            </button>
                        </div>

                        <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5">
                            @if ($firmaResponsable)
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 text-xs text-green-700">
                                        <x-heroicon-o-check-circle class="size-4" />
                                        Firmado el {{ $firmaResponsable->firmado_at->format('d/m/Y H:i') }}
                                    </div>
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($firmaResponsable->firma_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                        <x-heroicon-o-arrow-down-tray class="size-3.5" />
                                        Descargar
                                    </a>
                                </div>
                            @else
                                <p class="text-xs text-slate-400">Sin firma registrada</p>
                            @endif
                        </div>

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

    {{-- ═══ Modal: Crear nuevo concepto (inline) ═══ --}}
    <x-ui.modal
        :show="$modalNuevoConceptoAbierto"
        title="Nuevo concepto"
        close-action="cerrarNuevoConcepto"
        size="md">

        <form wire:submit="crearConcepto" id="form-nuevo-concepto" class="space-y-4">
            <x-ui.field label="Nombre" required :error="$errors->first('nuevoConceptoNombre')">
                <x-ui.input wire:model="nuevoConceptoNombre" maxlength="150" autofocus placeholder="Ej: Mantenimiento eléctrico" />
            </x-ui.field>

            <x-ui.field label="Descripción" :error="$errors->first('nuevoConceptoDescripcion')">
                <x-ui.textarea wire:model="nuevoConceptoDescripcion" rows="3" maxlength="500"
                               placeholder="Descripción opcional…" />
            </x-ui.field>
        </form>

        <x-slot:footer>
            <x-ui.button type="button" variant="neutral" wire:click="cerrarNuevoConcepto">
                Cancelar
            </x-ui.button>
            <x-ui.button type="submit" form="form-nuevo-concepto" variant="success" icon="heroicon-o-plus"
                         wire:loading.attr="disabled" wire:target="crearConcepto">
                <span wire:loading.remove wire:target="crearConcepto">Crear y asignar</span>
                <span wire:loading wire:target="crearConcepto">Creando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
