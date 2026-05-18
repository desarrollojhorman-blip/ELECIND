<div class="space-y-4">
    <x-ui.page-header title="Ajustes" subtitle="Identidad visual, numeración y configuración general." />

    <div x-data="{ tab: 'identidad' }">
    <div class="flex items-end overflow-x-auto border-b border-slate-200 px-2 pt-1.5">
        @foreach ([
            ['key' => 'identidad',   'label' => 'Identidad visual'],
            ['key' => 'numeracion',  'label' => 'Numeración'],
            ['key' => 'firma',       'label' => 'Firma y archivos'],
        ] as $t)
            <button type="button"
                    @click="tab = '{{ $t['key'] }}'"
                    :class="tab === '{{ $t['key'] }}'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                {{ $t['label'] }}
            </button>
        @endforeach
    </div>

    {{-- ═══ Tab: Identidad visual ═══ --}}
    <div x-show="tab === 'identidad'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">

        {{-- Logo de la aplicación --}}
        <div class="mb-6">
            <h3 class="mb-1 text-sm font-semibold text-slate-800">Logo de la aplicación</h3>
            <p class="mb-4 text-xs text-slate-500">
                Aparece en login, barra lateral y cabecera móvil. Tiene <strong>prioridad absoluta</strong> sobre el logo de empresa.
            </p>

            @php
                $logoAppUrlActual = $nuevoLogoApp
                    ? $nuevoLogoApp->temporaryUrl()
                    : (! $eliminarLogoApp && $logo_app_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($logo_app_path)
                        : null);
                $zoomOpciones = [80, 90, 100, 110, 120, 130];
            @endphp

            <div class="grid gap-5 md:grid-cols-[auto_1fr_auto]">
                <div class="flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                    @if ($logoAppUrlActual)
                        <img src="{{ $logoAppUrlActual }}" alt="Previsualización" class="max-h-full max-w-full object-contain">
                    @else
                        <x-heroicon-o-photo class="size-8 text-slate-300" />
                    @endif
                </div>

                <div class="space-y-2">
                    @if (! $eliminarLogoApp)
                        <input type="file"
                               wire:model="nuevoLogoApp"
                               accept="image/png,image/jpeg,image/svg+xml,image/webp"
                               class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                        <p class="text-xs text-slate-500">PNG/JPG/SVG/WebP, máx. 2 MB.</p>
                        <div wire:loading wire:target="nuevoLogoApp" class="text-xs text-slate-500">Subiendo…</div>
                        @error('nuevoLogoApp')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        @if ($nuevoLogoApp)
                            <x-ui.button variant="neutral" size="sm" type="button" wire:click="descartarNuevoLogoApp">
                                Descartar imagen seleccionada
                            </x-ui.button>
                        @elseif ($logo_app_path)
                            <x-ui.button variant="danger" size="sm" type="button" wire:click="quitarLogoApp">
                                Quitar logo actual
                            </x-ui.button>
                        @endif
                    @else
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3">
                            <p class="text-sm text-amber-800">El logo se eliminará al guardar.</p>
                            <x-ui.button variant="neutral" size="sm" type="button" wire:click="cancelarQuitarLogoApp" class="mt-2">
                                Cancelar
                            </x-ui.button>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col items-center gap-2">
                    <p class="text-xs text-slate-500">Zoom</p>
                    <x-ui.select wire:model="logo_app_zoom" class="w-20 text-center">
                        @foreach ($zoomOpciones as $z)
                            <option value="{{ $z }}">{{ $z }}%</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </div>

        {{-- Colores --}}
        <div class="border-t border-slate-100 pt-6">
            <h3 class="mb-1 text-sm font-semibold text-slate-800">Colores de la aplicación</h3>
            <p class="mb-4 text-xs text-slate-500">Afectan a botones, encabezados de tablas y elementos de marca.</p>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Color primario" required :error="$errors->first('color_primario')">
                    <div class="flex items-center gap-2" x-data="{ color: '{{ $color_primario }}' }">
                        <input type="color"
                               x-bind:value="/^#[0-9a-fA-F]{6}$/.test(color) ? color : '{{ $color_primario }}'"
                               x-on:change="color = $event.target.value; $wire.set('color_primario', $event.target.value)"
                               class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                        <x-ui.input x-model="color"
                                    x-on:change="/^#[0-9a-fA-F]{6}$/.test(color) && $wire.set('color_primario', color)"
                                    placeholder="#334155" class="font-mono" />
                        <button type="button"
                                x-on:click="color = '#334155'; $wire.set('color_primario', '#334155')"
                                title="Restablecer"
                                class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <x-heroicon-o-arrow-path class="size-4" />
                        </button>
                    </div>
                </x-ui.field>

                <x-ui.field label="Color secundario" required :error="$errors->first('color_secundario')">
                    <div class="flex items-center gap-2" x-data="{ color: '{{ $color_secundario }}' }">
                        <input type="color"
                               x-bind:value="/^#[0-9a-fA-F]{6}$/.test(color) ? color : '{{ $color_secundario }}'"
                               x-on:change="color = $event.target.value; $wire.set('color_secundario', $event.target.value)"
                               class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                        <x-ui.input x-model="color"
                                    x-on:change="/^#[0-9a-fA-F]{6}$/.test(color) && $wire.set('color_secundario', color)"
                                    placeholder="#f1f5f9" class="font-mono" />
                        <button type="button"
                                x-on:click="color = '#f1f5f9'; $wire.set('color_secundario', '#f1f5f9')"
                                title="Restablecer"
                                class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <x-heroicon-o-arrow-path class="size-4" />
                        </button>
                    </div>
                </x-ui.field>

                <x-ui.field label="Color texto encabezado tabla" required :error="$errors->first('color_texto_encabezado')">
                    <div class="flex items-center gap-2" x-data="{ color: '{{ $color_texto_encabezado }}' }">
                        <input type="color"
                               x-bind:value="/^#[0-9a-fA-F]{6}$/.test(color) ? color : '{{ $color_texto_encabezado }}'"
                               x-on:change="color = $event.target.value; $wire.set('color_texto_encabezado', $event.target.value)"
                               class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                        <x-ui.input x-model="color"
                                    x-on:change="/^#[0-9a-fA-F]{6}$/.test(color) && $wire.set('color_texto_encabezado', color)"
                                    placeholder="#ffffff" class="font-mono" />
                        <button type="button"
                                x-on:click="color = '#ffffff'; $wire.set('color_texto_encabezado', '#ffffff')"
                                title="Restablecer"
                                class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <x-heroicon-o-arrow-path class="size-4" />
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Color de las letras en los encabezados de las tablas.</p>
                </x-ui.field>

                <div class="flex items-center justify-center rounded-md border border-slate-200 p-4"
                     style="background-color: {{ $color_secundario }};">
                    <div>
                        <p class="text-xs uppercase tracking-wide" style="color: {{ $color_primario }};">Previsualización</p>
                        <p class="mt-1 text-sm font-semibold" style="color: {{ $color_primario }};">
                            {{ \App\Support\Branding::nombre() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.flash class="mt-6" />

        @if ($errors->any())
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-3">
                <ul class="space-y-1 text-xs text-red-700">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
            <x-ui.button variant="neutral" x-on:click="$wire.deshacer()" icon="heroicon-o-arrow-uturn-left" type="button">Deshacer</x-ui.button>
            <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="button" x-on:click="$wire.guardar()" wire:loading.attr="disabled" wire:target="guardar">Guardar</x-ui.button>
        </div>
    </div>

    {{-- ═══ Tab: Numeración ═══ --}}
    <div x-show="tab === 'numeracion'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-1 text-sm font-semibold text-slate-900">Plantillas de numeración</h3>
        <ul class="mb-4 space-y-1 text-xs text-slate-500">
            <li><code class="rounded bg-slate-100 px-1 font-mono">{YYYY}</code> → año completo <span class="text-slate-400">(ej. 2025)</span></li>
            <li><code class="rounded bg-slate-100 px-1 font-mono">{YY}</code> → año 2 dígitos <span class="text-slate-400">(ej. 25)</span></li>
            <li><code class="rounded bg-slate-100 px-1 font-mono">{MM}</code> → mes <span class="text-slate-400">(ej. 05)</span></li>
            <li><code class="rounded bg-slate-100 px-1 font-mono">{NNNN}</code> → secuencial 4 dígitos <span class="text-slate-400">(ej. 0042)</span></li>
            <li><code class="rounded bg-slate-100 px-1 font-mono">{NNN}</code> → secuencial 3 dígitos <span class="text-slate-400">(ej. 042)</span></li>
            <li><code class="rounded bg-slate-100 px-1 font-mono">{NN}</code> → secuencial 2 dígitos <span class="text-slate-400">(ej. 42)</span></li>
            <li><code class="rounded bg-slate-100 px-1 font-mono">{N}</code> → secuencial sin ceros <span class="text-slate-400">(ej. 42)</span></li>
        </ul>

        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field label="Código cliente" required :error="$errors->first('plantilla_numeracion_cliente')">
                <x-ui.input wire:model="plantilla_numeracion_cliente" class="font-mono" />
            </x-ui.field>

            <x-ui.field label="Número de albarán" required :error="$errors->first('plantilla_numeracion_albaran')">
                <x-ui.input wire:model="plantilla_numeracion_albaran" class="font-mono" />
            </x-ui.field>

            <x-ui.field label="Nº pedido" required :error="$errors->first('plantilla_numeracion_pedido')">
                <x-ui.input wire:model="plantilla_numeracion_pedido" class="font-mono" />
            </x-ui.field>

            <x-ui.field label="Código proyecto" required :error="$errors->first('plantilla_numeracion_proyecto')">
                <x-ui.input wire:model="plantilla_numeracion_proyecto" class="font-mono" />
            </x-ui.field>
        </div>

        <x-ui.flash class="mt-6" />

        @if ($errors->any())
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-3">
                <ul class="space-y-1 text-xs text-red-700">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
            <x-ui.button variant="neutral" x-on:click="$wire.deshacer()" icon="heroicon-o-arrow-uturn-left" type="button">Deshacer</x-ui.button>
            <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="button" x-on:click="$wire.guardar()" wire:loading.attr="disabled" wire:target="guardar">Guardar</x-ui.button>
        </div>
    </div>

    {{-- ═══ Tab: Firma y archivos ═══ --}}
    <div x-show="tab === 'firma'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">

        <div class="grid gap-8 md:grid-cols-2">
            {{-- Firma digital --}}
            <div>
                <h3 class="mb-1 text-sm font-semibold text-slate-900">Firma digital</h3>
                <p class="mb-4 text-xs text-slate-500">Configuración del enlace de firma que se envía por email.</p>

                <x-ui.field label="Caducidad del token de firma (días)"
                            required
                            :error="$errors->first('token_caducidad_dias')">
                    <x-ui.input type="number" min="1" max="90" wire:model="token_caducidad_dias" />
                    <p class="mt-1 text-xs text-slate-500">
                        Tiempo durante el cual el enlace de firma sigue siendo válido.
                    </p>
                </x-ui.field>
            </div>

            {{-- Archivos adjuntos --}}
            <div>
                <h3 class="mb-1 text-sm font-semibold text-slate-900">Archivos adjuntos en albaranes</h3>
                <p class="mb-4 text-xs text-slate-500">
                    Límites aplicados al subir documentos desde el tab «Archivos».
                    Límite del servidor PHP: <strong>{{ ini_get('upload_max_filesize') }}</strong>.
                </p>

                <div class="space-y-4">
                    <x-ui.field label="Tamaño máximo por archivo"
                                required
                                :error="$errors->first('archivo_tamano_max_mb')">
                        <x-ui.select wire:model="archivo_tamano_max_mb">
                            @foreach ([2, 5, 10, 20, 50] as $mb)
                                <option value="{{ $mb }}">{{ $mb }} MB</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Cantidad máxima de archivos por albarán"
                                required
                                :error="$errors->first('archivo_cantidad_max')">
                        <x-ui.input type="number" min="1" max="100" wire:model="archivo_cantidad_max" />
                        <p class="mt-1 text-xs text-slate-500">Entre 1 y 100 archivos.</p>
                    </x-ui.field>
                </div>
            </div>
        </div>

        <x-ui.flash class="mt-6" />

        @if ($errors->any())
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-3">
                <ul class="space-y-1 text-xs text-red-700">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
            <x-ui.button variant="neutral" x-on:click="$wire.deshacer()" icon="heroicon-o-arrow-uturn-left" type="button">Deshacer</x-ui.button>
            <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="button" x-on:click="$wire.guardar()" wire:loading.attr="disabled" wire:target="guardar">Guardar</x-ui.button>
        </div>
    </div>
    </div>{{-- /tabs + contenido --}}
</div>
