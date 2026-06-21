<div>
    {{-- ── Cabecera ──────────────────────────────────────────────── --}}
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">
                Convertir borrador {{ $borrador->numero_borrador }}
                {{ $borrador->crear_albaran ? 'a albarán' : 'a parte' }}
            </h2>
            <p class="text-sm text-slate-500">
                Revisa cada dato. Nada se guarda hasta que confirmes en el último paso.
            </p>
        </div>
        <a href="{{ route('borradores.ver', $borrador) }}" wire:navigate
           class="text-sm text-slate-500 hover:text-slate-700">← Volver al borrador</a>
    </div>

    {{-- ── Stepper (5 pasos) ─── --}}
    @php
        $pasos = [
            1 => 'Cliente',
            2 => 'Proyecto',
            3 => 'Concepto',
            4 => 'Responsable',
            5 => 'Confirmar',
        ];
    @endphp
    <div class="mb-6 flex flex-wrap items-center gap-2 text-xs">
        @foreach ($pasos as $i => $label)
            <button type="button"
                    wire:click="irAPaso({{ $i }})"
                    @disabled($i > $paso)
                    @class([
                        'rounded-full px-3 py-1 font-semibold transition-colors whitespace-nowrap',
                        'bg-primary-600 text-white' => $i === $paso,
                        'bg-slate-200 text-slate-700 hover:bg-slate-300' => $i < $paso,
                        'bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-200' => $i > $paso,
                    ])>
                {{ $i }} · {{ $label }}
            </button>
            @if ($i !== 5)
                <span class="text-slate-300">─</span>
            @endif
        @endforeach
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- ══════ PASO 1 · CLIENTE ══════ --}}
    @if ($paso === 1)
        <x-ui.card>
            <h3 class="mb-2 text-sm font-semibold text-slate-900">Paso 1 — Cliente</h3>

            <div class="mb-4 rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600">
                <span class="font-semibold">En el borrador:</span>
                @if ($borrador->cliente)
                    {{ $borrador->cliente->nombre }}
                    <span class="text-slate-400">(ya enlazado)</span>
                @elseif ($borrador->cliente_texto)
                    "{{ $borrador->cliente_texto }}"
                    <span class="text-slate-400">(texto libre)</span>
                @else
                    <span class="italic text-slate-400">sin cliente</span>
                @endif
            </div>

            <div class="space-y-3">
                <label class="flex cursor-pointer items-start gap-2">
                    <input type="radio" wire:model.live="clienteModo" value="elegir" class="mt-1">
                    <span class="text-sm font-medium text-slate-700">Elegir un cliente existente</span>
                </label>
                @if ($clienteModo === 'elegir')
                    <div class="ml-6">
                        <x-ui.searchable-select
                            wire-model="clienteIdElegido"
                            :value="$clienteIdElegido"
                            :options="$this->clientesDisponibles->map(fn($c) => ['value' => $c->id, 'label' => ($c->codigo_cliente ? $c->codigo_cliente.' · ' : '').$c->nombre])"
                            placeholder="— Selecciona cliente —"
                        />
                        @error('clienteIdElegido') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                @can('create', App\Models\Cliente::class)
                    <label class="flex cursor-pointer items-start gap-2">
                        <input type="radio" wire:model.live="clienteModo" value="crear" class="mt-1">
                        <span class="text-sm font-medium text-slate-700">Crear cliente nuevo</span>
                    </label>
                    @if ($clienteModo === 'crear')
                        <div class="ml-6 space-y-1">
                            <x-ui.field label="Nombre del cliente">
                                <x-ui.input wire:model="clienteNombreNuevo" maxlength="255" placeholder="Ej. Construcciones López SL" />
                            </x-ui.field>
                            @error('clienteNombreNuevo') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <p class="text-xs text-slate-400">Código correlativo automático.</p>
                        </div>
                    @endif
                @endcan
            </div>
        </x-ui.card>
    @endif

    {{-- ══════ PASO 2 · PROYECTO ══════ --}}
    @if ($paso === 2)
        <x-ui.card>
            <h3 class="mb-2 text-sm font-semibold text-slate-900">Paso 2 — Proyecto</h3>

            <div class="mb-4 rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600">
                <span class="font-semibold">En el borrador:</span>
                @if ($borrador->proyecto)
                    {{ $borrador->proyecto->nombre }}
                    <span class="text-slate-400">(ya enlazado)</span>
                @elseif ($borrador->proyecto_texto)
                    "{{ $borrador->proyecto_texto }}"
                    <span class="text-slate-400">(texto libre)</span>
                @else
                    <span class="italic text-slate-400">sin proyecto</span>
                @endif
            </div>

            @if ($clienteModo === 'crear')
                {{-- Cliente nuevo → no hay proyectos previos posibles. Solo crear. --}}
                <div class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    Vas a crear un cliente nuevo, así que aún no tiene proyectos. Toca crear el proyecto también.
                </div>

                @can('create', App\Models\Proyecto::class)
                    <div class="space-y-1">
                        <x-ui.field label="Nombre del proyecto nuevo">
                            <x-ui.input wire:model="proyectoNombreNuevo" maxlength="255" />
                        </x-ui.field>
                        @error('proyectoNombreNuevo') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-400">Código automático. Podrás ajustar tipo de proyecto luego en /proyectos.</p>
                    </div>
                @endcan
            @else
                {{-- Cliente existente → 2 opciones: elegir entre sus proyectos o crear uno nuevo. --}}
                <div class="space-y-3">
                    <label class="flex cursor-pointer items-start gap-2">
                        <input type="radio" wire:model.live="proyectoModo" value="elegir" class="mt-1">
                        <span class="text-sm font-medium text-slate-700">Elegir un proyecto del cliente</span>
                    </label>
                    @if ($proyectoModo === 'elegir')
                        <div class="ml-6">
                            @if ($this->proyectosDelCliente->isEmpty())
                                <p class="text-xs italic text-slate-400">Este cliente no tiene proyectos activos. Crea uno nuevo abajo.</p>
                            @else
                                <x-ui.searchable-select
                                    wire-model="proyectoIdElegido"
                                    :value="$proyectoIdElegido"
                                    :options="$this->proyectosDelCliente->map(fn($p) => ['value' => $p->id, 'label' => ($p->codigo ? '('.$p->codigo.') ' : '').$p->nombre])"
                                    placeholder="— Selecciona proyecto —"
                                />
                            @endif
                            @error('proyectoIdElegido') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @can('create', App\Models\Proyecto::class)
                        <label class="flex cursor-pointer items-start gap-2">
                            <input type="radio" wire:model.live="proyectoModo" value="crear" class="mt-1">
                            <span class="text-sm font-medium text-slate-700">Crear proyecto nuevo</span>
                        </label>
                        @if ($proyectoModo === 'crear')
                            <div class="ml-6 space-y-1">
                                <x-ui.field label="Nombre del proyecto">
                                    <x-ui.input wire:model="proyectoNombreNuevo" maxlength="255" />
                                </x-ui.field>
                                @error('proyectoNombreNuevo') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                <p class="text-xs text-slate-400">Código automático. Podrás ajustar tipo de proyecto luego en /proyectos.</p>
                            </div>
                        @endif
                    @endcan
                </div>
            @endif
        </x-ui.card>
    @endif

    {{-- ══════ PASO 3 · CONCEPTO ══════ --}}
    @if ($paso === 3)
        <x-ui.card>
            <h3 class="mb-2 text-sm font-semibold text-slate-900">Paso 3 — Concepto</h3>

            <div class="mb-4 rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600">
                <span class="font-semibold">En el borrador:</span>
                @if ($borrador->concepto)
                    {{ $borrador->concepto->nombre }}
                    <span class="text-slate-400">(ya enlazado)</span>
                @elseif ($borrador->concepto_texto)
                    "{{ $borrador->concepto_texto }}"
                    <span class="text-slate-400">(texto libre)</span>
                @else
                    <span class="italic text-slate-400">sin concepto</span>
                @endif
            </div>

            <div class="space-y-3">
                <label class="flex cursor-pointer items-start gap-2">
                    <input type="radio" wire:model.live="conceptoModo" value="elegir" class="mt-1">
                    <span class="text-sm font-medium text-slate-700">Elegir un concepto existente</span>
                </label>
                @if ($conceptoModo === 'elegir')
                    <div class="ml-6 space-y-3">
                        {{-- Grupo 1: conceptos del proyecto (si existe) --}}
                        @if ($this->conceptosDelProyecto->isNotEmpty())
                            <div>
                                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    Conceptos ya asociados al proyecto
                                </p>
                                <x-ui.searchable-select
                                    wire-model="conceptoIdElegido"
                                    :value="$conceptoIdElegido"
                                    :options="$this->conceptosDelProyecto->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                                    placeholder="— Selecciona concepto —"
                                />
                            </div>
                        @endif

                        {{-- Grupo 2: el resto del catálogo (sin asociar al proyecto, o todos si el proyecto es nuevo) --}}
                        @if ($this->otrosConceptos->isNotEmpty())
                            <div>
                                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    @if ($proyectoModo === 'crear')
                                        Todos los conceptos del catálogo
                                    @else
                                        Otros conceptos del catálogo
                                    @endif
                                </p>
                                <x-ui.searchable-select
                                    wire-model="conceptoIdElegido"
                                    :value="$conceptoIdElegido"
                                    :options="$this->otrosConceptos->map(fn($c) => ['value' => $c->id, 'label' => $c->nombre])"
                                    placeholder="— Selecciona concepto —"
                                />
                            </div>
                        @endif

                        @error('conceptoIdElegido') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                @can('create', App\Models\Concepto::class)
                    <label class="flex cursor-pointer items-start gap-2">
                        <input type="radio" wire:model.live="conceptoModo" value="crear" class="mt-1">
                        <span class="text-sm font-medium text-slate-700">Crear concepto nuevo</span>
                    </label>
                    @if ($conceptoModo === 'crear')
                        <div class="ml-6 space-y-1">
                            <x-ui.field label="Nombre del concepto">
                                <x-ui.input wire:model="conceptoNombreNuevo" maxlength="255" />
                            </x-ui.field>
                            @error('conceptoNombreNuevo') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                @endcan
            </div>
        </x-ui.card>
    @endif

    {{-- ══════ PASO 4 · RESPONSABLE ══════ --}}
    @if ($paso === 4)
        <x-ui.card>
            <h3 class="mb-2 text-sm font-semibold text-slate-900">
                Paso 4 — Responsable <span class="text-xs font-normal text-slate-400">(opcional)</span>
            </h3>

            <div class="mb-4 rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600">
                <span class="font-semibold">En el borrador:</span>
                @if ($borrador->responsable)
                    {{ trim($borrador->responsable->nombre.' '.$borrador->responsable->apellidos) }}
                    <span class="text-slate-400">(ya enlazado)</span>
                @elseif ($borrador->responsable_texto)
                    "{{ $borrador->responsable_texto }}"
                    <span class="text-slate-400">(texto libre)</span>
                @else
                    <span class="italic text-slate-400">sin responsable</span>
                @endif
            </div>

            <div class="space-y-3">
                <label class="flex cursor-pointer items-start gap-2">
                    <input type="radio" wire:model.live="responsableModo" value="ninguno" class="mt-1">
                    <span class="text-sm font-medium text-slate-700">Sin responsable por ahora</span>
                </label>

                @if ($clienteModo === 'elegir' && ($this->responsablesDelClienteEnElProyecto->isNotEmpty() || $this->responsablesDelCliente->isNotEmpty()))
                    <label class="flex cursor-pointer items-start gap-2">
                        <input type="radio" wire:model.live="responsableModo" value="elegir" class="mt-1">
                        <span class="text-sm font-medium text-slate-700">Elegir un responsable del cliente</span>
                    </label>
                    @if ($responsableModo === 'elegir')
                        <div class="ml-6 space-y-3">
                            {{-- Grupo 1: responsables del cliente que ya están en el proyecto (solo si el proyecto existe) --}}
                            @if ($this->responsablesDelClienteEnElProyecto->isNotEmpty())
                                <div>
                                    <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        Responsables ya en este proyecto
                                    </p>
                                    <x-ui.searchable-select
                                        wire-model="responsableIdElegido"
                                        :value="$responsableIdElegido"
                                        :options="$this->responsablesDelClienteEnElProyecto->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                                        placeholder="— Selecciona responsable —"
                                    />
                                </div>
                            @endif

                            {{-- Grupo 2: el resto de responsables del cliente (no en el proyecto, o todos si el proyecto es nuevo) --}}
                            @if ($this->responsablesDelCliente->isNotEmpty())
                                <div>
                                    <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        @if ($proyectoModo === 'crear')
                                            Todos los responsables del cliente
                                        @else
                                            Otros responsables del cliente (no asociados al proyecto)
                                        @endif
                                    </p>
                                    <x-ui.searchable-select
                                        wire-model="responsableIdElegido"
                                        :value="$responsableIdElegido"
                                        :options="$this->responsablesDelCliente->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                                        placeholder="— Selecciona responsable —"
                                    />
                                </div>
                            @endif

                            @error('responsableIdElegido') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                @endif

                <label class="flex cursor-pointer items-start gap-2">
                    <input type="radio" wire:model.live="responsableModo" value="crear" class="mt-1">
                    <span class="text-sm font-medium text-slate-700">Crear responsable nuevo</span>
                </label>
                @if ($responsableModo === 'crear')
                    <div class="ml-6 grid gap-3 sm:grid-cols-2">
                        <div>
                            <x-ui.field label="Usuario">
                                <x-ui.input wire:model="responsableUsuarioNuevo" maxlength="50" placeholder="jlopez" autocomplete="off" />
                            </x-ui.field>
                            @error('responsableUsuarioNuevo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-ui.field label="Contraseña">
                                <x-ui.input type="password" wire:model="responsablePasswordNuevo" autocomplete="new-password" />
                            </x-ui.field>
                            @error('responsablePasswordNuevo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-ui.field label="Nombre">
                                <x-ui.input wire:model="responsableNombreNuevo" maxlength="100" />
                            </x-ui.field>
                            @error('responsableNombreNuevo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-ui.field label="Email (opcional)">
                                <x-ui.input type="email" wire:model="responsableEmailNuevo" maxlength="150" />
                            </x-ui.field>
                            @error('responsableEmailNuevo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <p class="sm:col-span-2 text-xs text-slate-400">
                            Quedará ligado al cliente seleccionado, como usuario externo con rol "Responsable".
                        </p>
                    </div>
                @endif
            </div>
        </x-ui.card>
    @endif

    {{-- ══════ PASO 5 · CONFIRMAR ══════ --}}
    @if ($paso === 5)
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Paso 5 — Resumen y confirmación</h3>

            <div class="space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm">
                {{-- Cliente --}}
                <div class="flex gap-3">
                    <span class="w-28 shrink-0 font-semibold text-slate-600">Cliente:</span>
                    <span class="text-slate-800">
                        @if ($clienteModo === 'crear')
                            <span class="font-semibold text-emerald-700">✚ CREAR</span> "{{ $clienteNombreNuevo }}"
                        @else
                            @php $c = $this->clientesDisponibles->firstWhere('id', (int) $clienteIdElegido); @endphp
                            ✓ {{ $c?->nombre ?? '—' }}
                        @endif
                    </span>
                </div>

                {{-- Proyecto --}}
                <div class="flex gap-3">
                    <span class="w-28 shrink-0 font-semibold text-slate-600">Proyecto:</span>
                    <span class="text-slate-800">
                        @if ($proyectoModo === 'crear')
                            <span class="font-semibold text-emerald-700">✚ CREAR</span> "{{ $proyectoNombreNuevo }}"
                            <span class="text-xs text-slate-400">(bajo el cliente anterior)</span>
                        @else
                            @php $p = $this->proyectosDelCliente->firstWhere('id', (int) $proyectoIdElegido); @endphp
                            ✓ {{ $p?->nombre ?? '—' }}
                        @endif
                    </span>
                </div>

                {{-- Concepto --}}
                <div class="flex gap-3">
                    <span class="w-28 shrink-0 font-semibold text-slate-600">Concepto:</span>
                    <span class="text-slate-800">
                        @if ($conceptoModo === 'crear')
                            <span class="font-semibold text-emerald-700">✚ CREAR</span> "{{ $conceptoNombreNuevo }}"
                        @else
                            @php $co = $this->conceptosDisponibles->firstWhere('id', (int) $conceptoIdElegido); @endphp
                            ✓ {{ $co?->nombre ?? '—' }}
                        @endif
                    </span>
                </div>

                {{-- Responsable --}}
                <div class="flex gap-3">
                    <span class="w-28 shrink-0 font-semibold text-slate-600">Responsable:</span>
                    <span class="text-slate-800">
                        @if ($responsableModo === 'ninguno')
                            <span class="italic text-slate-400">Sin responsable</span>
                        @elseif ($responsableModo === 'crear')
                            <span class="font-semibold text-emerald-700">✚ CREAR</span>
                            {{ $responsableNombreNuevo }} <span class="text-xs text-slate-400">(usuario "{{ $responsableUsuarioNuevo }}")</span>
                        @else
                            @php $r = $this->responsablesDelCliente->firstWhere('id', (int) $responsableIdElegido)
                                    ?? $this->responsablesDelClienteEnElProyecto->firstWhere('id', (int) $responsableIdElegido); @endphp
                            ✓ {{ $r ? trim($r->nombre.' '.$r->apellidos) : '—' }}
                        @endif
                    </span>
                </div>

                {{-- Líneas --}}
                <div class="flex gap-3 border-t border-slate-200 pt-2">
                    <span class="w-28 shrink-0 font-semibold text-slate-600">Líneas:</span>
                    <span class="text-slate-800">
                        {{ $borrador->lineasPersonal->count() }} de personal · {{ $borrador->lineasMaterial->count() }} de material
                        <span class="text-xs text-slate-400">(se copian solo las que tengan trabajador/material reales)</span>
                    </span>
                </div>
            </div>

            <p class="mt-3 text-xs text-slate-500">
                Las líneas en texto libre (sin trabajador/material asignado) se reflejarán como nota en las observaciones del albarán. El administrador podrá completarlas después desde Albaranes.
            </p>

            <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                ⚠ Nada se ha guardado todavía. Al pulsar "Convertir a albarán" se creará todo de una vez.
            </div>
        </x-ui.card>
    @endif

    {{-- ── Navegación ────────────────────────────────────────────── --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            @if ($paso > 1)
                <x-ui.button variant="neutral" type="button" wire:click="atras" icon="heroicon-o-arrow-left">
                    Atrás
                </x-ui.button>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('borradores.ver', $borrador) }}" wire:navigate
               class="text-sm text-slate-500 hover:text-slate-700 px-3 py-1.5">
                Cancelar
            </a>
            @if ($paso < $this->pasoFinal)
                <x-ui.button variant="info" type="button" wire:click="siguiente">
                    Continuar
                    <x-heroicon-o-arrow-right class="size-4" />
                </x-ui.button>
            @else
                <x-ui.button variant="success" type="button"
                             wire:click="confirmar"
                             wire:loading.attr="disabled"
                             wire:target="confirmar">
                    <x-heroicon-o-check wire:loading.remove wire:target="confirmar" class="size-4" />
                    <svg wire:loading wire:target="confirmar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    <span wire:loading.remove wire:target="confirmar">Convertir a albarán</span>
                    <span wire:loading wire:target="confirmar">Convirtiendo…</span>
                </x-ui.button>
            @endif
        </div>
    </div>
</div>
