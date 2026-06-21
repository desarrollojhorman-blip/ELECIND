<div class="px-4 py-3 space-y-3">

    {{-- Cabecera --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-2">
            <p class="font-mono text-sm font-semibold text-slate-900">{{ $parte->numero }}</p>
            <x-ui.badge :tone="$parte->estado === 'cerrado' ? 'neutral' : 'info'" dot>
                {{ $parte->estado === 'cerrado' ? 'Cerrado' : 'Abierto' }}
            </x-ui.badge>
        </div>
        <dl class="space-y-1.5 text-sm">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Fecha</dt>
                <dd class="font-medium text-slate-800">
                    {{ $parte->fecha->format('d/m/Y') }}
                    <span class="ml-1 text-xs text-slate-500">({{ $parte->tipo_hora->etiqueta() }})</span>
                </dd>
            </div>
            @if ($parte->cliente)
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Cliente</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $parte->cliente->nombre }}</dd>
                </div>
            @endif
            @if ($parte->proyecto)
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Proyecto</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $parte->proyecto->nombre }}</dd>
                </div>
            @endif
            @if ($parte->concepto)
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Concepto</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $parte->concepto->nombre }}</dd>
                </div>
            @endif
        </dl>
        @if ($parte->observaciones)
            <div class="mt-3 rounded-md bg-slate-50 p-2.5 text-xs text-slate-600">
                {{ $parte->observaciones }}
            </div>
        @endif
    </div>

    {{-- Personal --}}
    @if ($parte->lineasPersonal->isNotEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Personal</p>
            <div class="divide-y divide-slate-100">
                @foreach ($parte->lineasPersonal as $linea)
                    @php
                        $horas      = (float) $linea->horas;
                        $horasExtra = (float) $linea->horas_extra;
                        $hFmt  = rtrim(rtrim(number_format($horas, 2, ',', ''), '0'), ',');
                        $heFmt = rtrim(rtrim(number_format($horasExtra, 2, ',', ''), '0'), ',');
                    @endphp
                    <div class="flex items-center justify-between gap-2 py-1.5 text-sm">
                        <span class="min-w-0 truncate text-slate-700">
                            {{ trim($linea->trabajador->nombre.' '.$linea->trabajador->apellidos) }}
                        </span>
                        <span class="shrink-0 font-medium text-slate-900">
                            {{ $hFmt }} h
                            @if ($horasExtra > 0)
                                <span class="text-amber-700">+ {{ $heFmt }} extra</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Materiales --}}
    @if (\App\Support\Modulos::materialesAvanzado() && $parte->lineasMaterial->isNotEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Materiales</p>
            <div class="divide-y divide-slate-100">
                @foreach ($parte->lineasMaterial as $linea)
                    <div class="flex items-center justify-between gap-2 py-1.5 text-sm">
                        <span class="min-w-0 truncate text-slate-700">{{ $linea->material->descripcion ?? '—' }}</span>
                        <span class="shrink-0 font-medium text-slate-900">
                            {{ rtrim(rtrim(number_format((float) $linea->cantidad, 2, ',', ''), '0'), ',') }}
                            <span class="text-xs text-slate-500">{{ $linea->material->unidad_medida ?? '' }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Botón generar albarán --}}
    @if ($parte->esEditable())
        <button type="button"
                wire:click="abrirConfirmarGenerar"
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-md bg-green-600 px-4 py-3 text-sm font-medium text-white hover:bg-green-700">
            <x-heroicon-o-document-plus class="size-4" />
            Generar albarán
        </button>
    @endif

    {{-- Modal confirmación --}}
    <x-ui.modal
        :show="$confirmarGenerar"
        title="Generar albarán"
        close-action="cancelarGenerar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-600">
                <x-heroicon-o-document-plus class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    ¿Seguro que quieres generar un albarán a partir del parte
                    <strong class="font-mono">{{ $parte->numero }}</strong>?
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    El parte quedará bloqueado hasta que el albarán sea eliminado.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cancelarGenerar">Cancelar</x-ui.button>
            <x-ui.button variant="success" wire:click="generarAlbaran" wire:loading.attr="disabled" wire:target="generarAlbaran">
                <x-heroicon-o-document-plus wire:loading.remove wire:target="generarAlbaran" class="size-4" />
                <span wire:loading.remove wire:target="generarAlbaran">Generar</span>
                <span wire:loading wire:target="generarAlbaran">Generando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

</div>
