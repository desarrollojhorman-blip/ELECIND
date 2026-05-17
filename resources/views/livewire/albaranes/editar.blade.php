<div class="space-y-4">
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
            <x-ui.button variant="info" type="submit" form="form-albaran" wire:loading.attr="disabled" icon="heroicon-o-check">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actionsRight>
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

                <x-ui.field label="Responsable" :error="$errors->first('form.responsable_id')">
                    <x-ui.searchable-select
                        wire:key="responsable-select-{{ $form->proyecto_id }}"
                        wire-model="form.responsable_id"
                        :options="$this->responsablesDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                        placeholder="— Sin responsable —"
                    />
                </x-ui.field>

                <x-ui.field label="Observaciones" class="md:col-span-2" :error="$errors->first('form.observaciones')">
                    <x-ui.textarea wire:model="form.observaciones" rows="3" placeholder="Notas adicionales del parte…" />
                </x-ui.field>
            </div>

            @if ($form->proyecto_id === null)
                <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    Selecciona un proyecto para poder añadir trabajadores y materiales.
                </p>
            @endif
        </div>
    </form>

    {{-- Trabajadores --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ count($form->companeros) }}</span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Trabajadores vinculados al proyecto que participan en este parte</p>
            </div>
            <x-ui.button type="button" variant="success" wire:click="agregarTrabajador"
                         icon="heroicon-o-plus">
                Añadir trabajador
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
                        @foreach ($form->companeros as $i => $trabajador)
                            <tr wire:key="trabajador-{{ $i }}">
                                <td class="px-6 py-3">
                                    <x-ui.searchable-select
                                        wire:key="trab-sel-{{ $trabajadorSelectKey }}-{{ $i }}"
                                        wire-model="form.companeros.{{ $i }}.trabajador_id"
                                        :options="$this->trabajadoresDisponibles->map(fn($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])"
                                        placeholder="— Selecciona trabajador —"
                                    />
                                    @error("form.companeros.{$i}.trabajador_id")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.input type="number" min="0" max="24" step="0.25"
                                                wire:model="form.companeros.{{ $i }}.horas" />
                                    @error("form.companeros.{$i}.horas")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.input type="number" min="0" max="24" step="0.25"
                                                wire:model="form.companeros.{{ $i }}.horas_extra" />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-ui.icon-button
                                        wire:click="quitarTrabajador({{ $i }})"
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
                <p class="mt-0.5 text-xs text-slate-400">Materiales del proyecto utilizados en este parte</p>
            </div>
            <x-ui.button type="button" variant="success" wire:click="agregarMaterial"
                         icon="heroicon-o-plus">
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
                            <th class="px-4 py-2.5 w-24">Unidad</th>
                            <th class="px-4 py-2.5 text-right w-20">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($form->materiales as $i => $linea)
                            @php
                                $matSel = $this->materialesDisponibles->firstWhere('id', $linea['material_id'] ?? null);
                            @endphp
                            <tr wire:key="material-{{ $i }}">
                                <td class="px-6 py-3">
                                    <x-ui.searchable-select
                                        wire:key="mat-sel-{{ $materialSelectKey }}-{{ $i }}"
                                        wire-model="form.materiales.{{ $i }}.material_id"
                                        :options="$this->materialesDisponibles->map(fn($m) => ['value' => $m->id, 'label' => $m->descripcion.' | '.$m->stock.' '.$m->unidad_medida])"
                                        placeholder="— Selecciona material —"
                                    />
                                    @error("form.materiales.{$i}.material_id")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.input type="number" min="0.01" step="0.01"
                                                wire:model="form.materiales.{{ $i }}.cantidad" />
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-xs">
                                    {{ $matSel?->unidad_medida ?? '—' }}
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

    {{-- Firmas --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm"
         x-data="{
             firmas: [],
             agregarFirma() {
                 this.firmas.push({ usuario: '', rol: '', enviar: false, correo_alternativo: '', firmado: false });
             },
             quitarFirma(i) { this.firmas.splice(i, 1); }
         }">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Firmas</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                          x-text="firmas.length">0</span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Firmantes del albarán</p>
            </div>
            <x-ui.button type="button" variant="success" @click="agregarFirma()" icon="heroicon-o-plus">
                Añadir firmante
            </x-ui.button>
        </div>

        <template x-if="firmas.length > 0">
            <div class="border-t border-slate-100 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-4 py-2.5">Usuario</th>
                            <th class="px-4 py-2.5 w-32">Rol</th>
                            <th class="px-4 py-2.5 w-20 text-center">Enviar</th>
                            <th class="px-4 py-2.5 w-48">Correo alternativo</th>
                            <th class="px-4 py-2.5 w-28 text-center">Enlace</th>
                            <th class="px-4 py-2.5 w-28 text-center">Firma</th>
                            <th class="px-4 py-2.5 w-16 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(firma, i) in firmas" :key="i">
                            <tr>
                                <td class="px-4 py-3">
                                    <input type="text" x-model="firma.usuario"
                                           placeholder="Nombre del usuario"
                                           class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-slate-400" />
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" x-model="firma.rol"
                                           placeholder="Rol"
                                           class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-slate-400" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" x-model="firma.enviar"
                                           class="size-4 rounded border-slate-300" />
                                </td>
                                <td class="px-4 py-3">
                                    <input type="email" x-model="firma.correo_alternativo"
                                           placeholder="correo@ejemplo.com"
                                           class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-slate-400" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                            :disabled="!firma.enviar"
                                            :class="firma.enviar
                                                ? 'text-blue-600 hover:text-blue-800'
                                                : 'cursor-not-allowed text-slate-300'"
                                            title="Enviar enlace de firma al correo">
                                        <x-heroicon-o-envelope class="mx-auto size-5" />
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <template x-if="firma.firmado">
                                            <div class="flex items-center gap-1">
                                                <button type="button" title="Descargar firma"
                                                        class="rounded p-1 text-green-600 hover:bg-green-50 hover:text-green-800">
                                                    <x-heroicon-o-arrow-down-tray class="size-4" />
                                                </button>
                                                <button type="button" @click="firma.firmado = false"
                                                        title="Eliminar archivo de firma"
                                                        class="rounded p-1 text-red-500 hover:bg-red-50 hover:text-red-700">
                                                    <x-heroicon-o-trash class="size-4" />
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="!firma.firmado">
                                            <label title="Subir archivo de firma"
                                                   class="cursor-pointer rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                                <x-heroicon-o-paper-clip class="size-5" />
                                                <input type="file" class="hidden" @change="firma.firmado = true" />
                                            </label>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" @click="quitarFirma(i)"
                                            title="Eliminar firmante"
                                            class="rounded-md p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700">
                                        <x-heroicon-o-x-mark class="size-4" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    {{-- Archivos adjuntos --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm"
         x-data="{
             archivos: [],
             agregarArchivo() {
                 this.archivos.push({ nombre: '', archivo: null, fecha: '' });
             },
             quitarArchivo(i) { this.archivos.splice(i, 1); },
             onFileChange(i, event) {
                 const f = event.target.files[0];
                 if (f) {
                     this.archivos[i].archivo = f.name;
                     if (!this.archivos[i].nombre) this.archivos[i].nombre = f.name;
                     this.archivos[i].fecha = new Date().toLocaleDateString('es-ES');
                 }
             }
         }">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Archivos adjuntos</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                          x-text="archivos.length">0</span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Documentos y archivos relacionados con este parte</p>
            </div>
            <x-ui.button type="button" variant="success" @click="agregarArchivo()" icon="heroicon-o-plus">
                Añadir archivo
            </x-ui.button>
        </div>

        <template x-if="archivos.length > 0">
            <div class="border-t border-slate-100 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-4 py-2.5">Nombre</th>
                            <th class="px-4 py-2.5 w-56">Archivo</th>
                            <th class="px-4 py-2.5 w-36">Fecha subida</th>
                            <th class="px-4 py-2.5 w-16 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(archivo, i) in archivos" :key="i">
                            <tr>
                                <td class="px-4 py-3">
                                    <input type="text" x-model="archivo.nombre"
                                           placeholder="Nombre descriptivo"
                                           class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-slate-400" />
                                </td>
                                <td class="px-4 py-3">
                                    <template x-if="!archivo.archivo">
                                        <label class="cursor-pointer">
                                            <span class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                                <x-heroicon-o-arrow-up-tray class="size-3.5" />
                                                Seleccionar archivo
                                            </span>
                                            <input type="file" class="hidden" @change="onFileChange(i, $event)" />
                                        </label>
                                    </template>
                                    <template x-if="archivo.archivo">
                                        <div class="flex items-center gap-2">
                                            <span class="max-w-[140px] truncate text-xs text-slate-600"
                                                  x-text="archivo.archivo"></span>
                                            <button type="button"
                                                    @click="archivo.archivo = null; archivo.fecha = ''"
                                                    title="Quitar archivo"
                                                    class="shrink-0 text-red-500 hover:text-red-700">
                                                <x-heroicon-o-x-mark class="size-4" />
                                            </button>
                                        </div>
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500" x-text="archivo.fecha || '—'"></td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" @click="quitarArchivo(i)"
                                            title="Eliminar fila"
                                            class="rounded-md p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700">
                                        <x-heroicon-o-x-mark class="size-4" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
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
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
