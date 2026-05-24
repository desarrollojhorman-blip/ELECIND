<div>
    <div class="px-4 py-3">
        {{-- Estado + datos generales --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between gap-2">
                <p class="font-mono text-sm font-semibold text-slate-900">{{ $albaran->numero }}</p>
                <x-ui.badge :tone="$albaran->estado->tono()" dot>{{ $albaran->estado->etiqueta() }}</x-ui.badge>
            </div>

            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Fecha</dt>
                    <dd class="font-medium text-slate-800">
                        {{ \Illuminate\Support\Carbon::parse($albaran->fecha)->format('d/m/Y') }}
                        <span class="ml-1 text-xs text-slate-500">({{ $albaran->tipo_hora->etiqueta() }})</span>
                    </dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Cliente</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $albaran->cliente?->nombre ?? '—' }}</dd>
                </div>
                @if ($albaran->proyecto)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Proyecto</dt>
                        <dd class="text-right font-medium text-slate-800">{{ $albaran->proyecto->nombre }}</dd>
                    </div>
                @endif
                @if ($albaran->concepto)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Concepto</dt>
                        <dd class="text-right font-medium text-slate-800">{{ $albaran->concepto->nombre }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Creado por</dt>
                    <dd class="text-right font-medium text-slate-800">
                        {{ trim($albaran->creador->nombre.' '.$albaran->creador->apellidos) }}
                    </dd>
                </div>
                @if ($albaran->responsable)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Responsable</dt>
                        <dd class="text-right font-medium text-slate-800">
                            {{ trim($albaran->responsable->nombre.' '.$albaran->responsable->apellidos) }}
                        </dd>
                    </div>
                @endif
            </dl>

            @if ($albaran->observaciones)
                <div class="mt-3 rounded-md bg-slate-50 p-2.5 text-xs text-slate-600">
                    {{ $albaran->observaciones }}
                </div>
            @endif
        </div>

        {{-- Personal --}}
        <x-mobile.section-title :hint="$albaran->lineasPersonal->count().' líneas'">Personal</x-mobile.section-title>
        <div class="space-y-2">
            @forelse ($albaran->lineasPersonal as $linea)
                @php
                    $horas = (float) $linea->horas;
                    $horasExtra = (float) $linea->horas_extra;
                    $horasFmt = rtrim(rtrim(number_format($horas, 2, ',', ''), '0'), ',');
                    $horasExtraFmt = rtrim(rtrim(number_format($horasExtra, 2, ',', ''), '0'), ',');
                @endphp
                <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">
                                {{ trim($linea->trabajador->nombre.' '.$linea->trabajador->apellidos) }}
                            </p>
                        </div>
                        <p class="shrink-0 text-right text-sm">
                            <span class="font-semibold text-slate-900">{{ $horasFmt }} h</span>
                            @if ($horasExtra > 0)
                                <span class="text-amber-700">+ {{ $horasExtraFmt }} extra</span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="py-3 text-center text-xs text-slate-400">Sin líneas de personal.</p>
            @endforelse
        </div>

        @if(\App\Support\Modulos::materialesAvanzado())
        {{-- Materiales --}}
        <x-mobile.section-title :hint="$albaran->lineasMaterial->count().' líneas'">Materiales</x-mobile.section-title>
        <div class="space-y-2">
            @forelse ($albaran->lineasMaterial as $linea)
                <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">
                                {{ $linea->material->descripcion ?? '—' }}
                            </p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-slate-900">
                            {{ rtrim(rtrim(number_format((float) $linea->cantidad, 2, ',', ''), '0'), ',') }}
                            <span class="text-xs text-slate-500">{{ $linea->material->unidad_medida ?? '' }}</span>
                        </p>
                    </div>
                </div>
            @empty
                <p class="py-3 text-center text-xs text-slate-400">Sin materiales.</p>
            @endforelse
        </div>
        @endif

        @if ($puedeEliminar)
            <div class="mt-6">
                <button type="button"
                        wire:click="confirmarEliminar"
                        class="flex w-full items-center justify-center gap-2 rounded-md bg-red-600 px-4 py-3 text-sm font-medium text-white hover:bg-red-700">
                    <x-heroicon-o-trash class="size-4" />
                    Eliminar
                </button>
            </div>
        @endif

        {{-- Botón firmar (borrador) --}}
        @if ($albaran->estado->value === 'borrador')
            @can('albaranes.crear_movil')
                <a href="{{ route('mobile.albaranes.firmar', ['albaran' => $albaran->getKey()]) }}"
                   class="mt-2 flex w-full items-center justify-center gap-2 rounded-md bg-primary-600 px-4 py-3 text-sm font-medium text-white hover:bg-primary-700">
                    <x-heroicon-o-pencil class="size-4" />
                    Iniciar proceso de firma
                </a>
            @endcan
        @endif

        {{-- Botón firmar (pendiente_firma) --}}
        @if ($albaran->estado->value === 'pendiente_firma')
            @can('albaranes.crear_movil')
                <a href="{{ route('mobile.albaranes.firmar', ['albaran' => $albaran->getKey()]) }}"
                   class="mt-2 flex w-full items-center justify-center gap-2 rounded-md bg-amber-600 px-4 py-3 text-sm font-medium text-white hover:bg-amber-700">
                    <x-heroicon-o-pencil class="size-4" />
                    Completar firma pendiente
                </a>
            @endcan
        @endif
    </div>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar parte"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el parte <strong class="font-mono">{{ $albaran->numero }}</strong> a la papelera.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    El stock de los materiales será devuelto a sus lotes.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
