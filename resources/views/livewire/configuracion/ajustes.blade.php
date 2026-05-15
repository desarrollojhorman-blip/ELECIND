<div>
    <x-ui.page-header
        title="Ajustes"
        subtitle="Configuración operativa de albaranes y firma digital." />

    <form wire:submit="guardar" class="space-y-5">

        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Albaranes</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Plantilla de numeración"
                            required
                            :error="$errors->first('plantilla_numeracion_albaran')">
                    <x-ui.input wire:model="plantilla_numeracion_albaran" class="font-mono" />
                    <p class="mt-1 text-xs text-slate-500">
                        Variables: <code>{YYYY}</code> (año), <code>{MM}</code> (mes), <code>{NNNN}</code> (secuencial).
                    </p>
                </x-ui.field>

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
