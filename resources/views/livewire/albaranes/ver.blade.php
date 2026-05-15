<div class="space-y-4">
    <x-ui.page-header title="Albarán {{ $albaran->numero }}" subtitle="Detalle del parte de trabajo.">
        <x-slot:actions>
            <x-ui.button as="a" href="{{ route('albaranes.index') }}" wire:navigate variant="ghost" icon="heroicon-o-arrow-left">
                Albaranes
            </x-ui.button>
            @can('update', $albaran)
                <x-ui.button as="a" href="{{ route('albaranes.editar', $albaran) }}" wire:navigate.fresh variant="info" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('delete', $albaran)
                <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                    Eliminar
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Cabecera (readonly) --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field label="Nº Albarán">
                <x-ui.input readonly value="{{ $albaran->numero }}" class="font-mono" />
            </x-ui.field>

            <x-ui.field label="Estado">
                <div class="flex h-9 items-center">
                    <x-ui.badge :tone="$albaran->estado->tono()" dot>{{ $albaran->estado->etiqueta() }}</x-ui.badge>
                </div>
            </x-ui.field>

            <x-ui.field label="Proyecto">
                <x-ui.input readonly value="{{ $albaran->proyecto?->nombre ?? '—' }}" />
            </x-ui.field>

            <x-ui.field label="Cliente">
                <x-ui.input readonly value="{{ $albaran->cliente?->nombre ?? '—' }}" />
            </x-ui.field>

            <x-ui.field label="Fecha">
                <x-ui.input readonly value="{{ $albaran->fecha->format('d/m/Y') }}" />
            </x-ui.field>

            <x-ui.field label="Tipo de jornada">
                <x-ui.input readonly value="{{ $albaran->tipo_hora->etiqueta() }}" />
            </x-ui.field>

            <x-ui.field label="Concepto">
                <x-ui.input readonly value="{{ $albaran->concepto?->nombre ?? '—' }}" />
            </x-ui.field>

            <x-ui.field label="Responsable">
                <x-ui.input readonly value="{{ $albaran->responsable ? trim($albaran->responsable->nombre.' '.$albaran->responsable->apellidos) : '—' }}" />
            </x-ui.field>

            <x-ui.field label="Creado por">
                <x-ui.input readonly value="{{ $albaran->creador ? trim($albaran->creador->nombre.' '.$albaran->creador->apellidos) : '—' }}" />
            </x-ui.field>

            @if ($albaran->observaciones)
                <x-ui.field label="Observaciones" class="md:col-span-2">
                    <x-ui.textarea readonly rows="3">{{ $albaran->observaciones }}</x-ui.textarea>
                </x-ui.field>
            @endif
        </div>
    </div>

    {{-- Líneas personal --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 px-6 py-4">
            <span class="text-sm font-semibold text-slate-900">Personal</span>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $albaran->lineasPersonal->count() }}</span>
        </div>
        @if ($albaran->lineasPersonal->isEmpty())
            <p class="border-t border-slate-100 px-6 py-4 text-sm text-slate-500">Sin líneas de personal.</p>
        @else
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <th class="px-6 py-2.5">Trabajador</th>
                        <th class="px-6 py-2.5 text-right">Horas</th>
                        <th class="px-6 py-2.5 text-right">H. extra</th>
                        <th class="px-6 py-2.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($albaran->lineasPersonal as $linea)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium text-slate-800">
                                {{ $linea->trabajador ? trim($linea->trabajador->nombre.' '.$linea->trabajador->apellidos) : '—' }}
                            </td>
                            <td class="px-6 py-3 text-right text-slate-600">{{ number_format($linea->horas, 2) }}</td>
                            <td class="px-6 py-3 text-right text-slate-600">{{ number_format($linea->horas_extra, 2) }}</td>
                            <td class="px-6 py-3 text-right font-medium text-slate-800">
                                {{ number_format((float)$linea->horas + (float)$linea->horas_extra, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Líneas material --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 px-6 py-4">
            <span class="text-sm font-semibold text-slate-900">Materiales</span>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $albaran->lineasMaterial->count() }}</span>
        </div>
        @if ($albaran->lineasMaterial->isEmpty())
            <p class="border-t border-slate-100 px-6 py-4 text-sm text-slate-500">Sin líneas de material.</p>
        @else
            <table class="w-full text-sm">
                <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <tr>
                        <th class="px-6 py-2.5">Material</th>
                        <th class="px-6 py-2.5 text-right">Cantidad</th>
                        <th class="px-6 py-2.5">Unidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($albaran->lineasMaterial as $linea)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium text-slate-800">{{ $linea->material?->descripcion ?? '—' }}</td>
                            <td class="px-6 py-3 text-right text-slate-600">{{ number_format((float)$linea->cantidad, 2) }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $linea->material?->unidad_medida ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Firmas --}}
    @if ($albaran->firmas->isNotEmpty())
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-2 px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Firmas</span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $albaran->firmas->count() }}</span>
            </div>
            <div class="border-t border-slate-100">
                <div class="flex flex-wrap gap-6 px-6 py-4">
                    @foreach ($albaran->firmas as $firma)
                        <div class="flex flex-col items-center gap-2">
                            <img src="{{ $firma->firma_base64 ?? '' }}" alt="Firma {{ $firma->tipo }}"
                                 class="h-24 w-48 rounded border border-slate-200 bg-white object-contain p-1" />
                            <span class="text-xs font-medium capitalize text-slate-500">{{ $firma->tipo }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar albarán"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el albarán <strong>{{ $albaran->numero }}</strong> a la <strong>papelera</strong>.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Podrás restaurarlo desde el filtro <em>«En papelera»</em>.
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
