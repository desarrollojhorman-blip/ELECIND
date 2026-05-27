<div class="space-y-4">
    <x-ui.page-header title="Importar usuarios"
                       subtitle="Sube un archivo Excel o CSV y asigna cada columna a un campo de usuario.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('usuarios.index') }}" wire:navigate
                         variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            <x-ui.button variant="success" wire:click="reiniciar" icon="heroicon-o-plus">
                Nuevo
            </x-ui.button>
        </x-slot:actionsLeft>
    </x-ui.page-header>

    {{-- ───────────────────────── Paso 1: subir archivo ───────────────────────── --}}
    <x-ui.card>
        <div class="mb-4 flex items-center gap-2">
            <span class="flex size-6 items-center justify-center rounded-full bg-primary-600 text-xs font-semibold text-white">1</span>
            <h3 class="text-sm font-semibold text-slate-800">Subir archivo</h3>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            {{-- Archivo (tamaño máx. junto al label) --}}
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between gap-2">
                    <label class="text-xs font-medium text-slate-600">Archivo (.xlsx, .xls, .csv)</label>
                    <span class="text-xs text-slate-400">máx. {{ $maxMb }} MB</span>
                </div>
                <input type="file"
                       wire:model="archivo"
                       accept=".xlsx,.xls,.csv,.txt"
                       class="block w-full rounded-md border border-slate-300 text-sm text-slate-600 file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                <div wire:loading wire:target="archivo" class="text-xs text-slate-500">Subiendo archivo…</div>
                @if ($archivo)
                    <div class="text-xs text-emerald-600">✓ {{ $archivo->getClientOriginalName() }}</div>
                @endif
                @error('archivo')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fila de inicio + toggle (al lado, centrado en altura) --}}
            <div class="grid grid-cols-2 gap-3">
                <x-ui.field label="Empieza en la fila Nº"
                            :error="$errors->first('filaInicio')">
                    <x-ui.input type="number" min="1" wire:model="filaInicio" />
                </x-ui.field>
                <div class="flex items-center">
                    <x-ui.checkbox wire:model="tieneEncabezados"
                                   label="La primera fila son los títulos" />
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">
            <x-ui.button variant="info" wire:click="procesarArchivo"
                         icon="heroicon-o-table-cells"
                         wire:loading.attr="disabled" wire:target="procesarArchivo,archivo">
                <span wire:loading.remove wire:target="procesarArchivo">Procesar archivo</span>
                <span wire:loading wire:target="procesarArchivo">Procesando…</span>
            </x-ui.button>
            @if ($procesado)
                <span class="text-xs text-slate-500">
                    {{ count($columnas) }} columnas · {{ $totalFilas }} {{ $totalFilas === 1 ? 'fila' : 'filas' }} de datos detectadas.
                </span>
            @endif
        </div>
    </x-ui.card>

    {{-- ───────────────────────── Paso 2: mapear columnas ───────────────────────── --}}
    @if ($procesado)
        <x-ui.card>
            <div class="mb-1 flex items-center gap-2">
                <span class="flex size-6 items-center justify-center rounded-full bg-primary-600 text-xs font-semibold text-white">2</span>
                <h3 class="text-sm font-semibold text-slate-800">Asignar columnas</h3>
            </div>
            <p class="mb-4 text-xs text-slate-500">
                Obligatorios: <strong>Usuario</strong>, <strong>Contraseña</strong>, <strong>Nombre</strong>,
                <strong>Tipo</strong> (interno/externo) y <strong>Rol</strong>. Si el tipo es «externo» también es obligatorio el código de empresa.
            </p>

            @error('mapeo')
                <div class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ $message }}
                </div>
            @enderror

            <div class="overflow-x-auto rounded-md border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Columna</th>
                            <th class="px-3 py-2">Valor 1</th>
                            <th class="px-3 py-2">Valor 2</th>
                            <th class="px-3 py-2">Valor 3</th>
                            <th class="px-3 py-2 w-72">Usar como</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($columnas as $col)
                            @php $idx = $col['indice']; @endphp
                            <tr class="align-middle">
                                <td class="px-3 py-2 font-medium text-slate-800">{{ $col['titulo'] }}</td>
                                <td class="px-3 py-2 text-slate-500">{{ $muestras[$idx][0] ?? '' }}</td>
                                <td class="px-3 py-2 text-slate-500">{{ $muestras[$idx][1] ?? '' }}</td>
                                <td class="px-3 py-2 text-slate-500">{{ $muestras[$idx][2] ?? '' }}</td>
                                <td class="px-3 py-2">
                                    <x-ui.select wire:model="mapeo.{{ $idx }}" wire:key="map-{{ $idx }}">
                                        <option value="">— No usar —</option>
                                        @foreach ($this->camposDisponibles() as $valor => $etiqueta)
                                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                <x-ui.button variant="success" wire:click="importar"
                             wire:loading.attr="disabled" wire:target="importar">
                    <x-heroicon-o-arrow-up-tray wire:loading.remove wire:target="importar" class="size-4" />
                    <svg wire:loading wire:target="importar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="importar">Importar {{ $totalFilas }} {{ $totalFilas === 1 ? 'fila' : 'filas' }}</span>
                    <span wire:loading wire:target="importar">Importando…</span>
                </x-ui.button>
                <x-ui.button variant="outline" wire:click="reiniciar" icon="heroicon-o-x-mark">
                    Cancelar
                </x-ui.button>
                <span class="text-xs text-slate-500">
                    No se guardará nada si alguna fila tiene errores.
                </span>
            </div>
        </x-ui.card>
    @endif

    {{-- ───────────────────────── Errores de validación ───────────────────────── --}}
    @if (! empty($errores))
        <x-ui.card>
            <div class="mb-3 flex items-start gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <x-heroicon-o-exclamation-triangle class="mt-0.5 size-5 shrink-0" />
                <div>
                    <p class="font-semibold">No se ha importado nada.</p>
                    <p class="text-red-700">
                        Se han encontrado {{ count($errores) }}
                        {{ count($errores) === 1 ? 'incidencia' : 'incidencias' }}.
                        Corrige el archivo y vuelve a subirlo: no se ha creado ningún usuario.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-md border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 w-24">Fila</th>
                            <th class="px-3 py-2 w-56">Campo</th>
                            <th class="px-3 py-2">Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($errores as $e)
                            <tr>
                                <td class="px-3 py-2 font-medium text-slate-800">Fila {{ $e['fila'] }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $e['columna'] }}</td>
                                <td class="px-3 py-2 text-red-700">{{ $e['motivo'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif
</div>
