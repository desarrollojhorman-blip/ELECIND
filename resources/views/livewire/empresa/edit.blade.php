<div>
    <x-ui.page-header
        title="Empresa"
        subtitle="Datos fiscales, identidad visual y colores de marca." />

    <form wire:submit="guardar" class="space-y-5">

        {{-- ─── Datos fiscales y de contacto ─── --}}
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Datos fiscales y de contacto</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                    <x-ui.input wire:model="form.nombre" />
                </x-ui.field>

                <x-ui.field label="Nombre comercial" :error="$errors->first('form.nombre_comercial')">
                    <x-ui.input wire:model="form.nombre_comercial" />
                </x-ui.field>

                <x-ui.field label="CIF" :error="$errors->first('form.cif')">
                    <x-ui.input wire:model="form.cif" />
                </x-ui.field>

                <x-ui.field label="Teléfono" :error="$errors->first('form.telefono')">
                    <x-ui.input wire:model="form.telefono" />
                </x-ui.field>

                <x-ui.field label="Dirección" class="md:col-span-2" :error="$errors->first('form.direccion')">
                    <x-ui.input wire:model="form.direccion" />
                </x-ui.field>

                <x-ui.field label="Código postal" :error="$errors->first('form.codigo_postal')">
                    <x-ui.input wire:model="form.codigo_postal" />
                </x-ui.field>

                <x-ui.field label="Población" :error="$errors->first('form.poblacion')">
                    <x-ui.input wire:model="form.poblacion" />
                </x-ui.field>

                <x-ui.field label="Provincia" :error="$errors->first('form.provincia')">
                    <x-ui.input wire:model="form.provincia" />
                </x-ui.field>

                <x-ui.field label="Email de contacto" :error="$errors->first('form.email_contacto')">
                    <x-ui.input type="email" wire:model="form.email_contacto" />
                </x-ui.field>

                <x-ui.field label="Email para notificaciones" class="md:col-span-2" :error="$errors->first('form.email_notificaciones')">
                    <x-ui.input type="email" wire:model="form.email_notificaciones" />
                </x-ui.field>
            </div>
        </x-ui.card>

        {{-- ─── Logos ─── --}}
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Logos</h3>

            @php
                $zoomOpciones = [80, 90, 100, 110, 120, 130];
                $clasificarRatio = static function (?float $ratio): string {
                    if ($ratio === null) { return 'desconocido'; }
                    if ($ratio >= 0.85 && $ratio <= 1.15) { return 'cuadrado'; }
                    return $ratio > 1 ? 'horizontal' : 'vertical';
                };
                $logoUrlActual = $form->nuevoLogo
                    ? $form->nuevoLogo->temporaryUrl()
                    : (! $form->eliminarLogo && $form->logo_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($form->logo_path)
                        : null);
                $logoAlbaranUrlActual = $form->nuevoLogoAlbaran
                    ? $form->nuevoLogoAlbaran->temporaryUrl()
                    : (! $form->eliminarLogoAlbaran && $form->logo_albaran_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($form->logo_albaran_path)
                        : null);
                $formaLogo = $clasificarRatio($form->logo_ratio);
                $formaLogoAlbaran = $clasificarRatio($form->logo_albaran_ratio);
            @endphp

            <div class="space-y-5">
                {{-- Logo principal --}}
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="mb-3">
                        <h4 class="text-sm font-semibold text-slate-800">Logo principal</h4>
                        <p class="text-xs text-slate-500">Se usa en la barra lateral web, cabecera móvil y pantalla de login.</p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-[auto_1fr_auto]">
                        <div class="flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                            @if ($logoUrlActual)
                                <img src="{{ $logoUrlActual }}" alt="Previsualización" class="max-h-full max-w-full object-contain">
                            @else
                                <x-heroicon-o-photo class="size-8 text-slate-300" />
                            @endif
                        </div>

                        <div class="space-y-2">
                            <input type="file"
                                   wire:model="form.nuevoLogo"
                                   accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                   class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">

                            <p class="text-xs text-slate-500">PNG/JPG/SVG/WebP, máx. 2 MB.</p>

                            <div wire:loading wire:target="form.nuevoLogo" class="text-xs text-slate-500">Subiendo…</div>

                            @error('form.nuevoLogo')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            @if ($form->logo_ratio !== null && ! $form->nuevoLogo && ! $form->eliminarLogo)
                                <p class="text-xs"
                                   @class([
                                       'text-emerald-700' => $formaLogo === 'cuadrado',
                                       'text-amber-700'   => $formaLogo === 'horizontal' || $formaLogo === 'vertical',
                                   ])>
                                    @if ($formaLogo === 'cuadrado')
                                        ✓ Logo cuadrado ({{ number_format($form->logo_ratio, 2) }}:1). Se mostrará entero en todas las vistas.
                                    @elseif ($formaLogo === 'horizontal')
                                        Logo horizontal ({{ number_format($form->logo_ratio, 2) }}:1). En el sidebar colapsado verás
                                        <strong class="font-semibold">{{ \App\Support\Branding::abreviatura() }}</strong> en su lugar.
                                    @else
                                        Logo vertical ({{ number_format($form->logo_ratio, 2) }}:1). Considera un logo más horizontal para cabeceras.
                                    @endif
                                </p>
                            @endif

                            <div class="flex flex-wrap gap-2 pt-1">
                                @if ($form->nuevoLogo)
                                    <x-ui.button size="sm" variant="ghost" wire:click="descartarNuevoLogo" type="button">Descartar selección</x-ui.button>
                                @elseif ($form->logo_path && ! $form->eliminarLogo)
                                    <x-ui.button size="sm" variant="danger" wire:click="quitarLogo" type="button" icon="heroicon-o-trash">Quitar logo actual</x-ui.button>
                                @elseif ($form->eliminarLogo)
                                    <span class="text-xs text-amber-700">El logo actual se eliminará al guardar.</span>
                                    <x-ui.button size="sm" variant="ghost" wire:click="cancelarQuitarLogo" type="button">Cancelar</x-ui.button>
                                @endif
                            </div>
                        </div>

                        <div class="w-44 space-y-2">
                            <x-ui.field label="Zoom" :error="$errors->first('form.logo_zoom')">
                                <x-ui.select wire:model.live="form.logo_zoom">
                                    @foreach ($zoomOpciones as $z)
                                        <option value="{{ $z }}">{{ $z }}%</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>

                            <div class="rounded-md bg-primary-700 px-2 py-1">
                                <p class="mb-1 text-[10px] uppercase tracking-wide text-white/60">Cabecera móvil</p>
                                <div class="flex h-10 items-center justify-center">
                                    @if ($logoUrlActual)
                                        <img src="{{ $logoUrlActual }}" alt=""
                                             style="max-height: calc(2rem * {{ $form->logo_zoom / 100 }});"
                                             class="w-auto">
                                    @else
                                        <span class="text-xs font-semibold text-white">{{ \App\Support\Branding::nombre() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logo de albarán --}}
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="mb-3">
                        <h4 class="text-sm font-semibold text-slate-800">Logo de albarán / factura</h4>
                        <p class="text-xs text-slate-500">Opcional. Aparece en los PDFs de albaranes. Si no subes uno, se usa el logo principal.</p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-[auto_1fr_auto]">
                        <div class="flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                            @if ($logoAlbaranUrlActual)
                                <img src="{{ $logoAlbaranUrlActual }}" alt="Previsualización albarán" class="max-h-full max-w-full object-contain">
                            @elseif ($logoUrlActual)
                                <div class="text-center">
                                    <img src="{{ $logoUrlActual }}" alt="" class="mx-auto max-h-14 max-w-full object-contain opacity-60">
                                    <p class="mt-0.5 text-[9px] uppercase tracking-wide text-slate-400">Usa el principal</p>
                                </div>
                            @else
                                <x-heroicon-o-document-text class="size-8 text-slate-300" />
                            @endif
                        </div>

                        <div class="space-y-2">
                            <input type="file"
                                   wire:model="form.nuevoLogoAlbaran"
                                   accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                   class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">

                            <p class="text-xs text-slate-500">PNG/JPG/SVG/WebP, máx. 2 MB.</p>

                            <div wire:loading wire:target="form.nuevoLogoAlbaran" class="text-xs text-slate-500">Subiendo…</div>

                            @error('form.nuevoLogoAlbaran')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            @if ($form->logo_albaran_ratio !== null && ! $form->nuevoLogoAlbaran && ! $form->eliminarLogoAlbaran)
                                <p class="text-xs"
                                   @class([
                                       'text-emerald-700' => $formaLogoAlbaran === 'cuadrado',
                                       'text-slate-500'   => $formaLogoAlbaran === 'horizontal' || $formaLogoAlbaran === 'vertical',
                                   ])>
                                    @if ($formaLogoAlbaran === 'cuadrado')
                                        ✓ Cuadrado ({{ number_format($form->logo_albaran_ratio, 2) }}:1).
                                    @elseif ($formaLogoAlbaran === 'horizontal')
                                        Horizontal ({{ number_format($form->logo_albaran_ratio, 2) }}:1) — ideal para cabecera de albarán.
                                    @else
                                        Vertical ({{ number_format($form->logo_albaran_ratio, 2) }}:1).
                                    @endif
                                </p>
                            @endif

                            <div class="flex flex-wrap gap-2 pt-1">
                                @if ($form->nuevoLogoAlbaran)
                                    <x-ui.button size="sm" variant="ghost" wire:click="descartarNuevoLogoAlbaran" type="button">Descartar selección</x-ui.button>
                                @elseif ($form->logo_albaran_path && ! $form->eliminarLogoAlbaran)
                                    <x-ui.button size="sm" variant="danger" wire:click="quitarLogoAlbaran" type="button" icon="heroicon-o-trash">Quitar logo de albarán</x-ui.button>
                                @elseif ($form->eliminarLogoAlbaran)
                                    <span class="text-xs text-amber-700">Se eliminará al guardar (se usará el logo principal).</span>
                                    <x-ui.button size="sm" variant="ghost" wire:click="cancelarQuitarLogoAlbaran" type="button">Cancelar</x-ui.button>
                                @endif
                            </div>
                        </div>

                        <div class="w-44 space-y-2">
                            <x-ui.field label="Zoom" :error="$errors->first('form.logo_albaran_zoom')">
                                <x-ui.select wire:model.live="form.logo_albaran_zoom">
                                    @foreach ($zoomOpciones as $z)
                                        <option value="{{ $z }}">{{ $z }}%</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>

                            <div class="rounded-md border border-slate-200 bg-white p-2">
                                <p class="mb-1 text-[10px] uppercase tracking-wide text-slate-400">Cabecera PDF</p>
                                <div class="flex h-12 items-center justify-center">
                                    @php $logoPdfPreview = $logoAlbaranUrlActual ?: $logoUrlActual; @endphp
                                    @if ($logoPdfPreview)
                                        <img src="{{ $logoPdfPreview }}" alt=""
                                             style="max-height: calc(2.5rem * {{ $form->logo_albaran_zoom / 100 }});"
                                             class="w-auto">
                                    @else
                                        <span class="text-xs text-slate-400">{{ \App\Support\Branding::nombre() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- ─── Colores de marca ─── --}}
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Colores de marca</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Color primario" required :error="$errors->first('form.color_primario')">
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live="form.color_primario"
                               class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                        <x-ui.input wire:model="form.color_primario" placeholder="#871f1f" class="font-mono" />
                    </div>
                </x-ui.field>

                <x-ui.field label="Color secundario" required :error="$errors->first('form.color_secundario')">
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live="form.color_secundario"
                               class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                        <x-ui.input wire:model="form.color_secundario" placeholder="#f5e6e6" class="font-mono" />
                    </div>
                </x-ui.field>

                <x-ui.field label="Color texto encabezado tabla" required :error="$errors->first('form.color_texto_encabezado')">
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live="form.color_texto_encabezado"
                               class="h-10 w-14 cursor-pointer rounded border border-slate-300">
                        <x-ui.input wire:model="form.color_texto_encabezado" placeholder="#ffffff" class="font-mono" />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Color de las letras en los encabezados de las tablas.</p>
                </x-ui.field>

                <div class="md:col-span-2 rounded-md border border-slate-200 p-3"
                     style="background-color: {{ $form->color_secundario }};">
                    <p class="text-xs uppercase tracking-wide" style="color: {{ $form->color_primario }};">Previsualización</p>
                    <p class="mt-1 text-sm font-semibold" style="color: {{ $form->color_primario }};">
                        {{ $form->nombre_comercial ?: $form->nombre ?: 'ELECIND' }}
                    </p>
                </div>
            </div>
        </x-ui.card>

        {{-- Acciones --}}
        <div class="flex justify-end gap-2 pt-2">
            <x-ui.button variant="success" type="submit" icon="heroicon-o-check" wire:loading.attr="disabled">
                Guardar cambios
            </x-ui.button>
        </div>

    </form>
</div>
