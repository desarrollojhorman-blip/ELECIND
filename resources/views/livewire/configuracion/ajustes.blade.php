<div>
    <x-ui.page-header
        title="Ajustes"
        subtitle="Configuración operativa de albaranes, clientes y firma digital." />

    <form wire:submit="guardar" class="space-y-5">

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
            </div>
        </x-ui.card>

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

        <div class="flex justify-end gap-2 pt-2">
            <x-ui.button variant="success" type="submit" icon="heroicon-o-check" wire:loading.attr="disabled">
                Guardar cambios
            </x-ui.button>
        </div>

    </form>
</div>
