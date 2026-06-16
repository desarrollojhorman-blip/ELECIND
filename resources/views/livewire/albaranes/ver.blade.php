<div class="space-y-4" x-data="{ tab: 'albaran' }">
    <x-ui.page-header title="Ver albarán" subtitle="Detalle del parte de trabajo.">
        <x-slot:actions>
            <div class="text-right">
                @if ($albaran->numero)
                    <div class="text-xl font-semibold text-slate-900 font-mono">{{ $albaran->numero }}</div>
                @endif
                @if ($albaran->estado)
                    <div class="text-sm text-slate-500">{{ $albaran->estado->etiqueta() }}</div>
                @endif
            </div>
        </x-slot:actions>

        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('albaranes.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @can('update', $albaran)
                <x-ui.button as="a" href="{{ route('albaranes.editar', $albaran) }}" wire:navigate.fresh variant="neutral" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('albaranes.crear_web')
                <x-ui.button as="a" href="{{ route('albaranes.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                    Nuevo
                </x-ui.button>
            @endcan
            @can('delete', $albaran)
                <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                    Eliminar
                </x-ui.button>
            @endcan
        </x-slot:actionsLeft>
    </x-ui.page-header>

    <div>
        {{-- Tabs nav --}}
        <div class="flex items-end overflow-x-auto border-b border-slate-200 px-2 pt-1.5">
            <button type="button"
                    @click="tab = 'albaran'"
                    :class="tab === 'albaran'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                Albarán
            </button>

            @foreach (array_values(array_filter([
                ['key' => 'trabajadores', 'label' => 'Trabajadores', 'count' => $albaran->lineasPersonal->count()],
                \App\Support\Modulos::materialesAvanzado() ? ['key' => 'materiales', 'label' => 'Materiales', 'count' => $albaran->lineasMaterial->count()] : false,
                ['key' => 'firmas',       'label' => 'Firmas',       'count' => $albaran->firmas->count()],
                ['key' => 'archivos',     'label' => 'Archivos',     'count' => $albaran->archivos->count()],
            ])) as $t)
                <button type="button"
                        @click="tab = '{{ $t['key'] }}'"
                        :class="tab === '{{ $t['key'] }}'
                            ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                            : 'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                    {{ $t['label'] }}
                    @if ($t['count'])
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600">
                            {{ $t['count'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- ═══ Tab: Albarán ═══ --}}
        <div x-show="tab === 'albaran'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
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

        {{-- ═══ Tab: Trabajadores ═══ --}}
        <div x-show="tab === 'trabajadores'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Trabajadores</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $albaran->lineasPersonal->count() }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Trabajadores que participan en este parte</p>
            </div>
            @if ($albaran->lineasPersonal->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin líneas de personal.
                </div>
            @else
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Trabajador</th>
                                <th class="w-28 px-4 py-2.5 text-right">Horas</th>
                                <th class="w-28 px-4 py-2.5 text-right">H. extra</th>
                                <th class="w-24 px-4 py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($albaran->lineasPersonal as $linea)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-medium text-slate-800">
                                        {{ trim(($linea->trabajador->nombre ?? '') . ' ' . ($linea->trabajador->apellidos ?? '')) ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600">{{ number_format((float) $linea->horas, 2) }} h</td>
                                    <td class="px-4 py-3 text-right text-slate-600">{{ number_format((float) $linea->horas_extra, 2) }} h</td>
                                    <td class="px-4 py-3 text-right font-medium text-slate-800">
                                        {{ number_format((float) $linea->horas + (float) $linea->horas_extra, 2) }} h
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ═══ Tab: Materiales ═══ --}}
        @if(\App\Support\Modulos::materialesAvanzado())
        <div x-show="tab === 'materiales'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Materiales</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $albaran->lineasMaterial->count() }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Materiales del proyecto utilizados en este parte</p>
            </div>
            @if ($albaran->lineasMaterial->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin líneas de material.
                </div>
            @else
                <div class="overflow-x-auto border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Material</th>
                                <th class="w-28 px-4 py-2.5 text-right">Cantidad</th>
                                <th class="w-24 px-4 py-2.5">Unidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($albaran->lineasMaterial as $linea)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-medium text-slate-800">{{ $linea->material?->descripcion ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600">{{ number_format((float) $linea->cantidad, 2) }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $linea->material?->unidad_medida ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif

        {{-- ═══ Tab: Firmas ═══ --}}
        <div x-show="tab === 'firmas'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Firmas</span>
                <p class="mt-0.5 text-xs text-slate-400">Estado de las firmas del parte</p>
            </div>
            @if ($albaran->firmas->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin firmas registradas.
                </div>
            @else
                <div class="grid gap-px border-t border-slate-100 bg-slate-100 md:grid-cols-2">
                    @foreach ($albaran->firmas as $firma)
                        <div class="bg-white p-6">
                            <div class="mb-3 flex items-center justify-between">
                                <h4 class="text-sm font-semibold capitalize text-slate-800">{{ $firma->tipo }}</h4>
                                <div class="flex items-center gap-1.5 text-xs text-green-700">
                                    <x-heroicon-o-check-circle class="size-4" />
                                    Firmado el {{ $firma->firmado_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            @if ($firma->firma_path)
                                <img src="{{ Storage::disk('public')->url($firma->firma_path) }}"
                                     alt="Firma {{ $firma->tipo }}"
                                     class="h-24 w-full rounded border border-slate-200 bg-white object-contain p-1" />
                                <a href="{{ Storage::disk('public')->url($firma->firma_path) }}" target="_blank"
                                   class="mt-2 inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                    <x-heroicon-o-arrow-down-tray class="size-3.5" />
                                    Descargar
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ═══ Tab: Archivos ═══ --}}
        <div x-show="tab === 'archivos'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-900">Archivos adjuntos</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ $albaran->archivos->count() }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-400">Documentos relacionados con este parte</p>
            </div>
            @if ($albaran->archivos->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    Sin archivos adjuntos.
                </div>
            @else
                <div class="overflow-x-auto border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Nombre</th>
                                <th class="w-48 px-4 py-2.5">Archivo</th>
                                <th class="w-24 px-4 py-2.5 text-right">Tamaño</th>
                                <th class="w-36 px-4 py-2.5">Fecha</th>
                                <th class="w-20 px-4 py-2.5 text-right">Descargar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($albaran->archivos as $archivo)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-medium text-slate-800">{{ $archivo->nombre }}</td>
                                    <td class="max-w-[180px] truncate px-4 py-3 text-xs text-slate-500">{{ $archivo->nombre_original }}</td>
                                    <td class="px-4 py-3 text-right text-xs text-slate-500">{{ $archivo->tamanoFormateado() }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-500">{{ $archivo->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ $archivo->url() }}" target="_blank"
                                           class="inline-flex items-center justify-center rounded-md p-1.5 text-blue-600 hover:bg-blue-50"
                                           title="Descargar">
                                            <x-heroicon-o-arrow-down-tray class="size-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

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
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminar"
                         wire:loading.attr="disabled"
                         wire:target="eliminar">
                <x-heroicon-o-trash wire:loading.remove wire:target="eliminar" class="size-4" />
                <svg wire:loading wire:target="eliminar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="eliminar">Eliminar</span>
                <span wire:loading wire:target="eliminar">Eliminando…</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
