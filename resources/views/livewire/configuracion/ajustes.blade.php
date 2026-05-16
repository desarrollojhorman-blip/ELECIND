<div>
    <x-ui.page-header
        title="Ajustes"
        subtitle="Identidad visual de la aplicación, plantillas de numeración y firma digital." />

    <form wire:submit="guardar" class="space-y-5">

        {{-- ─── Identidad visual ─── --}}
        <x-ui.card>
            <h3 class="mb-1 text-sm font-semibold text-slate-900">Identidad visual</h3>
            <p class="mb-4 text-xs text-slate-500">
                El logo y los colores aquí configurados tienen <strong>prioridad absoluta</strong> en toda la interfaz
                (login, barra lateral, cabecera móvil). Si no se sube un logo de aplicación, se usará el logo de empresa.
            </p>

            @php
                $logoAppUrlActual = $nuevoLogoApp
                    ? $nuevoLogoApp->temporaryUrl()
                    : (! $eliminarLogoApp && $logo_app_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($logo_app_path)
                        : null);
                $zoomOpciones = [80, 90, 100, 110, 120, 130];
            @endphp

            {{-- Logo de la aplicación --}}
            <div class="mb-5 rounded-lg border border-slate-200 p-4">
                <div class="mb-3">
                    <h4 class="text-sm font-semibold text-slate-800">Logo de la aplicación</h4>
                    <p class="text-xs text-slate-500">Aparece en login, barra lateral y cabecera móvil. Prioridad sobre el logo de empresa.</p>
                </div>

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

            {{-- Colores de la aplicación --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <div class="mb-3">
                    <h4 class="text-sm font-semibold text-slate-800">Colores de la aplicación</h4>
                    <p class="text-xs text-slate-500">Afectan a botones, encabezados de tablas y elementos de marca en toda la interfaz web y móvil.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Color primario" required :error="$errors->first('color_primario')">
                        <div class="flex items-center gap-2"
                             x-data="{ color: '{{ $color_primario }}' }">
                            <input type="color"
                                   x-model="color"
                                   x-on:input="$wire.set('color_primario', color)"
                                   class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                            <x-ui.input x-model="color"
                                        x-on:input="$wire.set('color_primario', color)"
                                        placeholder="#334155" class="font-mono" />
                            <button type="button"
                                    x-on:click="color = '#334155'; $wire.set('color_primario', '#334155')"
                                    title="Restablecer (#334155)"
                                    class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <x-heroicon-o-arrow-path class="size-4" />
                            </button>
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Color secundario" required :error="$errors->first('color_secundario')">
                        <div class="flex items-center gap-2"
                             x-data="{ color: '{{ $color_secundario }}' }">
                            <input type="color"
                                   x-model="color"
                                   x-on:input="$wire.set('color_secundario', color)"
                                   class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                            <x-ui.input x-model="color"
                                        x-on:input="$wire.set('color_secundario', color)"
                                        placeholder="#f1f5f9" class="font-mono" />
                            <button type="button"
                                    x-on:click="color = '#f1f5f9'; $wire.set('color_secundario', '#f1f5f9')"
                                    title="Restablecer (#f1f5f9)"
                                    class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <x-heroicon-o-arrow-path class="size-4" />
                            </button>
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Color texto encabezado tabla" required :error="$errors->first('color_texto_encabezado')">
                        <div class="flex items-center gap-2"
                             x-data="{ color: '{{ $color_texto_encabezado }}' }">
                            <input type="color"
                                   x-model="color"
                                   x-on:input="$wire.set('color_texto_encabezado', color)"
                                   class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                            <x-ui.input x-model="color"
                                        x-on:input="$wire.set('color_texto_encabezado', color)"
                                        placeholder="#ffffff" class="font-mono" />
                            <button type="button"
                                    x-on:click="color = '#ffffff'; $wire.set('color_texto_encabezado', '#ffffff')"
                                    title="Restablecer (#ffffff)"
                                    class="shrink-0 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <x-heroicon-o-arrow-path class="size-4" />
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Color de las letras en los encabezados de las tablas.</p>
                    </x-ui.field>

                    <div class="rounded-md border border-slate-200 p-3"
                         style="background-color: {{ $color_secundario }};">
                        <p class="text-xs uppercase tracking-wide" style="color: {{ $color_primario }};">Previsualización</p>
                        <p class="mt-1 text-sm font-semibold" style="color: {{ $color_primario }};">
                            {{ \App\Support\Branding::nombre() }}
                        </p>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- ─── Plantillas de numeración ─── --}}
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Plantillas de numeración</h3>
            <p class="mb-4 text-xs text-slate-500">
                Variables disponibles: <code>{YYYY}</code> (año 4 dígitos), <code>{YY}</code> (año 2 dígitos),
                <code>{MM}</code> (mes), <code>{NNNN}</code> / <code>{NNN}</code> / <code>{NN}</code> (secuencial con ceros).
            </p>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Código cliente"
                            required
                            :error="$errors->first('plantilla_numeracion_cliente')">
                    <x-ui.input wire:model="plantilla_numeracion_cliente" class="font-mono" />
                </x-ui.field>

                <x-ui.field label="Número de albarán"
                            required
                            :error="$errors->first('plantilla_numeracion_albaran')">
                    <x-ui.input wire:model="plantilla_numeracion_albaran" class="font-mono" />
                </x-ui.field>

                <x-ui.field label="Nº pedido"
                            required
                            :error="$errors->first('plantilla_numeracion_pedido')">
                    <x-ui.input wire:model="plantilla_numeracion_pedido" class="font-mono" />
                </x-ui.field>

                <x-ui.field label="Código proyecto"
                            required
                            :error="$errors->first('plantilla_numeracion_proyecto')">
                    <x-ui.input wire:model="plantilla_numeracion_proyecto" class="font-mono" />
                </x-ui.field>
            </div>
        </x-ui.card>

        {{-- ─── Firma digital ─── --}}
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Firma digital</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Caducidad del token de firma (días)"
                            required
                            :error="$errors->first('token_caducidad_dias')">
                    <x-ui.input type="number" min="1" max="90" wire:model="token_caducidad_dias" />
                    <p class="mt-1 text-xs text-slate-500">
                        Tiempo durante el cual el enlace de firma por email sigue siendo válido.
                    </p>
                </x-ui.field>
            </div>
        </x-ui.card>

        <x-ui.flash />

        <div class="flex justify-end gap-2 pt-2">
            <x-ui.button variant="info" type="submit" icon="heroicon-o-check" wire:loading.attr="disabled">
                Guardar cambios
            </x-ui.button>
        </div>

    </form>
</div>
