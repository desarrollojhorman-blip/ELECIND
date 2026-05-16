<div>
    <x-ui.page-header
        title="Empresa"
        subtitle="Datos fiscales y logos para documentos (albaranes y PDF)." />

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

        {{-- ─── Logo ─── --}}
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Logo</h3>

            @php
                $zoomOpciones = [80, 90, 100, 110, 120, 130];
                $logoUrlActual = $form->nuevoLogo
                    ? $form->nuevoLogo->temporaryUrl()
                    : (! $form->eliminarLogo && $form->logo_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($form->logo_path)
                        : null);
                $formaLogo = match(true) {
                    $form->logo_ratio === null                              => 'desconocido',
                    $form->logo_ratio >= 0.85 && $form->logo_ratio <= 1.15 => 'cuadrado',
                    $form->logo_ratio > 1                                   => 'horizontal',
                    default                                                 => 'vertical',
                };
            @endphp

            <div class="rounded-lg border border-slate-200 p-4">
                <p class="mb-3 text-xs text-slate-500">
                    Se usa en los albaranes y documentos PDF. También actúa como fallback en la interfaz si no hay logo de aplicación configurado en Ajustes.
                </p>

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
                                    ✓ Cuadrado ({{ number_format($form->logo_ratio, 2) }}:1).
                                @elseif ($formaLogo === 'horizontal')
                                    Horizontal ({{ number_format($form->logo_ratio, 2) }}:1) — ideal para cabecera de albarán.
                                @else
                                    Vertical ({{ number_format($form->logo_ratio, 2) }}:1).
                                @endif
                            </p>
                        @endif

                        <div class="flex flex-wrap gap-2 pt-1">
                            @if ($form->nuevoLogo)
                                <x-ui.button size="sm" variant="neutral" wire:click="descartarNuevoLogo" type="button">Descartar selección</x-ui.button>
                            @elseif ($form->logo_path && ! $form->eliminarLogo)
                                <x-ui.button size="sm" variant="danger" wire:click="quitarLogo" type="button" icon="heroicon-o-trash">Quitar logo actual</x-ui.button>
                            @elseif ($form->eliminarLogo)
                                <span class="text-xs text-amber-700">El logo se eliminará al guardar.</span>
                                <x-ui.button size="sm" variant="neutral" wire:click="cancelarQuitarLogo" type="button">Cancelar</x-ui.button>
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

                        <div class="rounded-md border border-slate-200 bg-white p-2">
                            <p class="mb-1 text-[10px] uppercase tracking-wide text-slate-400">Cabecera PDF</p>
                            <div class="flex h-12 items-center justify-center">
                                @if ($logoUrlActual)
                                    <img src="{{ $logoUrlActual }}" alt=""
                                         style="max-height: calc(2.5rem * {{ $form->logo_zoom / 100 }});"
                                         class="w-auto">
                                @else
                                    <span class="text-xs text-slate-400">{{ \App\Support\Branding::nombre() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Acciones --}}
        <div class="flex justify-end gap-2 pt-2">
            <x-ui.button variant="info" type="submit" icon="heroicon-o-check" wire:loading.attr="disabled">
                Guardar cambios
            </x-ui.button>
        </div>

    </form>
</div>
