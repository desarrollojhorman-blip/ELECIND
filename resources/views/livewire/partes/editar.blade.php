<div class="space-y-4" x-data="{ tab: 'parte' }">
    <x-ui.page-header :title="$titulo" :id-badge="$parte?->id" subtitle="Datos del parte y líneas de personal.">
        @if ($parte)
            <x-slot:actions>
                <div class="text-right">
                    <div class="text-xl font-semibold text-slate-900 font-mono">{{ $parte->codigo }}</div>
                    <div class="text-sm text-slate-500">
                        {{ ucfirst($parte->estado) }}
                        @if ($parte->es_albaran)
                            · <span class="text-blue-600">Albarán</span>
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
                    <x-ui.button variant="danger" icon="heroicon-o-trash"
                        wire:click="$parent.confirmarEliminar({{ $parte->id }})"
                        wire:confirm="¿Eliminar el parte {{ $parte->codigo }}?">
                        Eliminar
                    </x-ui.button>
                @endcan
            @endif
        </x-slot:actionsLeft>

        <x-slot:actionsRight>
            <x-ui.button variant="neutral" wire:click="deshacer" wire:loading.attr="disabled" wire:target="deshacer">
                <x-heroicon-o-arrow-uturn-left wire:loading.remove wire:target="deshacer" class="size-4" />
                <svg wire:loading wire:target="deshacer" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="deshacer">Deshacer</span>
                <span wire:loading wire:target="deshacer">Deshaciendo…</span>
            </x-ui.button>
            <x-ui.button variant="info" type="submit" form="form-parte" wire:loading.attr="disabled" wire:target="guardar">
                <x-heroicon-o-arrow-down-tray wire:loading.remove wire:target="guardar" class="size-4" />
                <svg wire:loading wire:target="guardar" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actionsRight>
    </x-ui.page-header>

    <x-ui.flash />

    {{-- Tabs --}}
    <div>
    <div class="flex items-end border-b border-slate-200 px-2 pt-1.5">
        <button type="button" @click="tab = 'parte'"
                :class="tab === 'parte'
                    ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                    : 'text-slate-500 hover:text-slate-700'"
                class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
            Parte
            @if ($parte)
                <span class="font-mono text-xs font-normal text-slate-400">#{{ $parte->id }}</span>
            @endif
        </button>

        @if ($modoCrear)
            <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                title="Guarda primero el parte para acceder a esta sección">
                <x-heroicon-o-lock-closed class="size-3" />
                Líneas
            </span>
        @else
            <button type="button" @click="tab = 'lineas'"
                    :class="tab === 'lineas'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                Líneas
                @if (count($form->lineasPersonal) > 0)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600">
                        {{ count($form->lineasPersonal) }}
                    </span>
                @endif
            </button>
        @endif
    </div>

    {{-- ═══ Tab: Parte ═══ ─────────────────────────────────────── --}}
    <form wire:submit="guardar" id="form-parte" autocomplete="off">
        <div x-show="tab === 'parte'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Cabecera</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Operario" required :error="$errors->first('form.user_id')">
                    <x-ui.select wire:model.live="form.user_id">
                        <option value="">— Seleccionar —</option>
                        @foreach ($this->operariosDisponibles as $u)
                            <option value="{{ $u->id }}">
                                {{ trim($u->apellidos.' '.$u->nombre) }}
                                @if ($u->numero_empleado) (#{{ $u->numero_empleado }}) @endif
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Proyecto" required :error="$errors->first('form.proyecto_id')">
                    <x-ui.select wire:model.live="form.proyecto_id">
                        <option value="">— Seleccionar —</option>
                        @foreach ($this->proyectosDisponibles as $p)
                            <option value="{{ $p->id }}">{{ $p->codigo }} · {{ $p->nombre }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Fecha" required :error="$errors->first('form.fecha')">
                    <x-ui.date-input wireModel="form.fecha" :value="$form->fecha" placeholder="dd/mm/aaaa" />
                </x-ui.field>

                <x-ui.field label="¿Genera albarán?" hint="Por defecto se autorrellena desde el tipo de proyecto.">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model="form.es_albaran"
                            class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
                        <span class="text-sm text-slate-700">Sí, este parte genera albarán facturable</span>
                    </label>
                </x-ui.field>

                <x-ui.field label="Hora inicio" :error="$errors->first('form.hora_inicio')">
                    <x-ui.input type="time" wire:model="form.hora_inicio" />
                </x-ui.field>

                <x-ui.field label="Hora fin" :error="$errors->first('form.hora_fin')">
                    <x-ui.input type="time" wire:model="form.hora_fin" />
                </x-ui.field>
            </div>

            <h3 class="mt-6 mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Observaciones</h3>
            <x-ui.field :error="$errors->first('form.observaciones')">
                <x-ui.textarea wire:model="form.observaciones" rows="3"
                    placeholder="Notas, descuentos manuales (ej.: Juan se fue al médico 1h)…" />
            </x-ui.field>
        </div>
    </form>

    {{-- ═══ Tab: Líneas ═══ ────────────────────────────────────── --}}
    @if (! $modoCrear)
        <div x-show="tab === 'lineas'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Líneas de personal</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Una fila por (trabajador × atributo). Un mismo trabajador puede tener varias líneas si imputa más de un atributo.
                    </p>
                </div>
                @can('update', $parte)
                    <x-ui.button wire:click="addLinea" variant="success" icon="heroicon-o-plus" size="sm">
                        Añadir línea
                    </x-ui.button>
                @endcan
            </div>

            @if (count($form->lineasPersonal) === 0)
                <div class="rounded-lg border border-dashed border-slate-200 px-6 py-8 text-center text-sm text-slate-400">
                    Sin líneas todavía. Pulsa «Añadir línea» para imputar horas.
                </div>
            @else
                <div class="overflow-x-auto rounded-md border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-600">
                            <tr>
                                <th class="px-3 py-2 text-left">Trabajador</th>
                                <th class="px-3 py-2 text-left">Atributo</th>
                                <th class="px-3 py-2 text-right w-28">Cantidad</th>
                                <th class="px-3 py-2 text-left">Motivo / Notas</th>
                                <th class="px-3 py-2 text-right w-16">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($form->lineasPersonal as $i => $linea)
                                <tr wire:key="linea-{{ $i }}">
                                    <td class="px-3 py-2">
                                        <select wire:model="form.lineasPersonal.{{ $i }}.user_id"
                                            class="w-full rounded border-slate-300 py-1 pl-2 pr-7 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="">— Trabajador —</option>
                                            @foreach ($this->operariosDisponibles as $u)
                                                <option value="{{ $u->id }}">
                                                    {{ trim($u->apellidos.' '.$u->nombre) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select wire:model="form.lineasPersonal.{{ $i }}.atributo_id"
                                            class="w-full rounded border-slate-300 py-1 pl-2 pr-7 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="">— Atributo —</option>
                                            @foreach ($this->atributosDisponibles as $a)
                                                <option value="{{ $a->id }}">{{ $a->nombre_corto }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" max="99.99"
                                            wire:model="form.lineasPersonal.{{ $i }}.cantidad"
                                            placeholder="0"
                                            class="w-full rounded border-slate-300 px-2 py-1 text-right text-sm focus:border-primary-500 focus:ring-primary-500" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text"
                                            wire:model="form.lineasPersonal.{{ $i }}.motivo_ajuste"
                                            placeholder="médico, plus retén, asuntos…"
                                            class="w-full rounded border-slate-300 px-2 py-1 text-sm focus:border-primary-500 focus:ring-primary-500" />
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <button type="button" wire:click="removeLinea({{ $i }})"
                                            class="rounded p-1 text-red-600 hover:bg-red-50 transition-colors"
                                            title="Quitar línea">
                                            <x-heroicon-o-trash class="size-4" />
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-xs text-slate-400">
                    Los snapshots económicos (tarifa, tasa, facturación, coste) se recalculan al guardar.
                </div>
            @endif
        </div>
    @endif
    </div>
</div>
