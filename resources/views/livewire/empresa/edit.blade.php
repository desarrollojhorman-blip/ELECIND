<div>
    <x-ui.page-header
        title="Configuración de empresa"
        subtitle="Identidad visual, datos fiscales y operativa. El logo y los colores se aplican en runtime." />

    <form wire:submit="guardar" class="space-y-5">

        {{-- ============================== Identidad visual ============================== --}}
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Identidad visual</h3>

            <div class="grid gap-5 md:grid-cols-2">
                {{-- Logo --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Logo</label>

                    <div class="flex items-center gap-4">
                        <div class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                            @if ($form->nuevoLogo)
                                <img src="{{ $form->nuevoLogo->temporaryUrl() }}" alt="Previsualización" class="max-h-full max-w-full object-contain">
                            @elseif (! $form->eliminarLogo && $form->logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($form->logo_path) }}" alt="Logo actual" class="max-h-full max-w-full object-contain">
                            @else
                                <x-heroicon-o-photo class="size-8 text-slate-300" />
                            @endif
                        </div>

                        <div class="flex-1 space-y-2">
                            <input type="file"
                                   wire:model="form.nuevoLogo"
                                   accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                   class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">

                            <p class="text-xs text-slate-500">PNG/JPG/SVG/WebP, máx. 2 MB.</p>

                            <div wire:loading wire:target="form.nuevoLogo" class="text-xs text-slate-500">
                                Subiendo…
                            </div>

                            @error('form.nuevoLogo')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="flex flex-wrap gap-2">
                                @if ($form->nuevoLogo)
                                    <x-ui.button size="sm" variant="ghost" wire:click="descartarNuevoLogo" type="button">
                                        Descartar selección
                                    </x-ui.button>
                                @elseif ($form->logo_path && ! $form->eliminarLogo)
                                    <x-ui.button size="sm" variant="danger" wire:click="quitarLogo" type="button" icon="heroicon-o-trash">
                                        Quitar logo actual
                                    </x-ui.button>
                                @elseif ($form->eliminarLogo)
                                    <span class="text-xs text-amber-700">El logo actual se eliminará al guardar.</span>
                                    <x-ui.button size="sm" variant="ghost" wire:click="cancelarQuitarLogo" type="button">
                                        Cancelar
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Colores --}}
                <div class="grid grid-cols-2 gap-3">
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

                    <div class="col-span-2 rounded-md border border-slate-200 p-3"
                         style="background-color: {{ $form->color_secundario }};">
                        <p class="text-xs uppercase tracking-wide" style="color: {{ $form->color_primario }};">Previsualización</p>
                        <p class="mt-1 text-sm font-semibold" style="color: {{ $form->color_primario }};">
                            {{ $form->nombre_comercial ?: $form->nombre ?: 'ELECIND' }}
                        </p>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- ============================== Datos de empresa ============================== --}}
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

        {{-- ============================== Operativa ============================== --}}
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Operativa</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Plantilla de numeración de albaranes"
                            required
                            :error="$errors->first('form.plantilla_numeracion_albaran')">
                    <x-ui.input wire:model="form.plantilla_numeracion_albaran" class="font-mono" />
                    <p class="mt-1 text-xs text-slate-500">
                        Variables: <code>{YYYY}</code> (año), <code>{MM}</code> (mes), <code>{NNNN}</code> (secuencial).
                    </p>
                </x-ui.field>

                <x-ui.field label="Caducidad del token de firma (días)"
                            required
                            :error="$errors->first('form.token_caducidad_dias')">
                    <x-ui.input type="number" min="1" max="90" wire:model="form.token_caducidad_dias" />
                    <p class="mt-1 text-xs text-slate-500">
                        Tiempo durante el cual el enlace de firma por email sigue siendo válido.
                    </p>
                </x-ui.field>
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
