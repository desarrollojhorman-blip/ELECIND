<div class="space-y-4" x-data="{ tab: 'albaran' }">

    {{-- Page Header --}}
    <x-ui.page-header :title="$titulo" subtitle="Cabecera y líneas del albarán.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('albaranes.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($albaran)
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
            <x-ui.button variant="neutral" wire:click="deshacer" icon="heroicon-o-arrow-uturn-left">
                Deshacer
            </x-ui.button>
            <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="submit" form="form-albaran"
                         wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actionsRight>
    </x-ui.page-header>

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

                <x-ui.field label="Estado" required :error="$errors->first('form.estado')">
                    <x-ui.select wire:model="form.estado">
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->value }}">{{ $estado->etiqueta() }}</option>
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
            <x-ui.button type="button" variant="success" wire:click="abrirModalTrabajador" icon="heroicon-o-plus">
                Añadir
            </x-ui.button>
        </div>

        @if ($albaran && $albaran->lineasPersonal->isNotEmpty())
            <div class="border-t border-slate-100">
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Trabajador</th>
                            <th class="w-28 px-4 py-2.5 text-right">Horas</th>
                            <th class="w-28 px-4 py-2.5 text-right">H. extra</th>
                            <th class="w-24 px-4 py-2.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($albaran->lineasPersonal as $linea)
                            <tr wire:key="linea-personal-{{ $linea->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-medium text-slate-800">
                                    {{ trim(($linea->trabajador->nombre ?? '') . ' ' . ($linea->trabajador->apellidos ?? '')) ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">
                                    {{ number_format((float) $linea->horas, 2) }} h
                                </td>
                                <td class="px-4 py-3 text-right text-slate-500">
                                    {{ number_format((float) $linea->horas_extra, 2) }} h
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui.icon-button
                                            wire:click="abrirModalTrabajador({{ $linea->id }})"
                                            icon="heroicon-o-pencil-square"
                                            variant="info"
                                            tooltip="Editar" />
                                        <x-ui.icon-button
                                            wire:click="confirmarEliminarTrabajador({{ $linea->id }})"
                                            icon="heroicon-o-trash"
                                            variant="danger"
                                            tooltip="Eliminar" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                No hay trabajadores en este parte. Pulsa «Añadir» para incluir participantes.
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
            <x-ui.button type="button" variant="success" wire:click="abrirModalMaterial" icon="heroicon-o-plus">
                Añadir
            </x-ui.button>
        </div>

        @if ($albaran && $albaran->lineasMaterial->isNotEmpty())
            <div class="overflow-x-auto border-t border-slate-100">
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Material</th>
                            <th class="w-28 px-4 py-2.5 text-right">Cantidad</th>
                            <th class="w-24 px-4 py-2.5">Unidad</th>
                            <th class="w-28 px-4 py-2.5 text-right">Stock actual</th>
                            <th class="w-24 px-4 py-2.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($albaran->lineasMaterial as $linea)
                            <tr wire:key="linea-material-{{ $linea->id }}" class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-medium text-slate-800">
                                    {{ $linea->material?->descripcion ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">
                                    {{ number_format((float) $linea->cantidad, 2) }}
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ $linea->material?->unidad_medida ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-500">
                                    {{ $linea->material ? number_format((float) $linea->material->stock, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui.icon-button
                                            wire:click="abrirModalMaterial({{ $linea->id }})"
                                            icon="heroicon-o-pencil-square"
                                            variant="info"
                                            tooltip="Editar" />
                                        <x-ui.icon-button
                                            wire:click="confirmarEliminarMaterial({{ $linea->id }})"
                                            icon="heroicon-o-trash"
                                            variant="danger"
                                            tooltip="Eliminar" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
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
                <x-ui.button type="button" variant="info" icon="heroicon-o-paper-airplane"
                             @click="$wire.notificarFirmantes(notificarTrab, notificarResp)"
                             x-bind:disabled="!notificarTrab && !notificarResp">
                    Notificar seleccionados
                </x-ui.button>
            </div>

            {{-- Dos bloques de firmante --}}
            <div class="grid gap-px border-t border-slate-100 bg-slate-100 md:grid-cols-2">

                {{-- ── Firmante: Trabajador ── --}}
                <div class="bg-white p-6"
                     x-data="{ esOtro: {{ $form->firma_trabajador_otro_nombre ? 'true' : 'false' }} }">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Trabajador</h4>
                            <p class="text-xs text-slate-500">Quien firma por parte del trabajador</p>
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
                        <x-ui.field label="Usuario del proyecto" :error="$errors->first('form.firma_trabajador_user_id')">
                            <x-ui.searchable-select
                                wire:key="firma-trab-{{ $form->proyecto_id }}"
                                wire-model="form.firma_trabajador_user_id"
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
                                <a href="{{ Storage::url($firmaTrabajador->firma_path) }}" target="_blank"
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
                                :options="$this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
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
                                <a href="{{ Storage::url($firmaResponsable->firma_path) }}" target="_blank"
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
            <x-ui.button type="button" variant="success" wire:click="abrirModalArchivo" icon="heroicon-o-plus">
                Añadir
            </x-ui.button>
        </div>

        @if ($albaran && $albaran->archivos->isNotEmpty())
            <div class="overflow-x-auto border-t border-slate-100">
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
                    </tbody>
                </table>
            </div>
        @else
            <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                No hay archivos adjuntos. Pulsa «Añadir» para subir documentos.
            </div>
        @endif
    </div>
    </div>{{-- /tabs + contenido --}}

    {{-- Modal añadir / editar trabajador --}}
    <x-ui.modal
        :show="$modalTrabajadorAbierto"
        :title="$editandoLineaPersonalId ? 'Editar trabajador' : 'Añadir trabajador'"
        close-action="cerrarModalTrabajador"
        size="sm">

        <div class="space-y-4">
            <x-ui.field label="Trabajador" required :error="$errors->first('modalTrabajadorUserId')">
                <x-ui.searchable-select
                    wire:key="modal-trab-{{ $modalTrabajadorAbierto }}"
                    wire-model="modalTrabajadorUserId"
                    :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                    placeholder="— Selecciona trabajador —"
                />
            </x-ui.field>

            <div class="grid grid-cols-2 gap-3">
                <x-ui.field label="Horas" required :error="$errors->first('modalTrabajadorHoras')">
                    <x-ui.input type="number" min="0" max="24" step="0.25"
                                wire:model="modalTrabajadorHoras" />
                </x-ui.field>
                <x-ui.field label="Horas extra" :error="$errors->first('modalTrabajadorHorasExtra')">
                    <x-ui.input type="number" min="0" max="24" step="0.25"
                                wire:model="modalTrabajadorHorasExtra" />
                </x-ui.field>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModalTrabajador">Cancelar</x-ui.button>
            <x-ui.button variant="info" wire:click="guardarTrabajador"
                         wire:loading.attr="disabled" wire:target="guardarTrabajador"
                        >
                Guardar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

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
            <x-ui.button variant="danger" wire:click="eliminarTrabajador" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    @if(\App\Support\Modulos::materialesAvanzado())
    {{-- Modal añadir / editar material --}}
    <x-ui.modal
        :show="$modalMaterialAbierto"
        :title="$editandoLineaMaterialId ? 'Editar material' : 'Añadir material'"
        close-action="cerrarModalMaterial"
        size="sm">

        <div class="space-y-4">
            <x-ui.field label="Material" required :error="$errors->first('modalMaterialId')">
                <x-ui.searchable-select
                    wire:key="modal-mat-{{ $modalMaterialAbierto }}"
                    wire-model="modalMaterialId"
                    :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | '.$m->unidad_medida.' | stock: '.$m->stock])"
                    placeholder="— Selecciona material —"
                />
            </x-ui.field>

            <x-ui.field label="Cantidad" required :error="$errors->first('modalMaterialCantidad')">
                <x-ui.input type="number" min="0.01" step="0.01"
                            wire:model="modalMaterialCantidad" />
            </x-ui.field>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModalMaterial">Cancelar</x-ui.button>
            <x-ui.button variant="info" wire:click="guardarMaterial"
                         wire:loading.attr="disabled" wire:target="guardarMaterial"
                        >
                Guardar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

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
            <x-ui.button variant="danger" wire:click="eliminarMaterial" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
    @endif

    {{-- Modal subir archivo --}}
    <x-ui.modal
        :show="$modalArchivoAbierto"
        title="Añadir archivo"
        close-action="cerrarModalArchivo"
        size="sm">

        <div class="space-y-4">
            <x-ui.field label="Archivo" required :error="$errors->first('modalArchivoFichero')">
                <input type="file"
                       wire:model="modalArchivoFichero"
                       class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-primary-700 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-primary-800" />
                <div wire:loading wire:target="modalArchivoFichero" class="mt-1 text-xs text-slate-500">
                    Subiendo…
                </div>
            </x-ui.field>

            <x-ui.field label="Nombre descriptivo" :error="$errors->first('modalArchivoNombre')">
                <x-ui.input wire:model="modalArchivoNombre"
                            placeholder="Opcional — si lo dejas vacío se usará el nombre del archivo" />
            </x-ui.field>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarModalArchivo">Cancelar</x-ui.button>
            <x-ui.button variant="info" wire:click="guardarArchivo"
                         wire:loading.attr="disabled" wire:target="guardarArchivo,modalArchivoFichero"
                         icon="heroicon-o-arrow-up-tray">
                Subir
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

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
            <x-ui.button variant="danger" wire:click="eliminarArchivo" icon="heroicon-o-trash">
                Eliminar
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
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

</div>
