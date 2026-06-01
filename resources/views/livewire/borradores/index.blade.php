<div>
    <x-ui.page-header title="Borradores" subtitle="Partes personalizados pendientes de convertir a albarán." />

    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por número, proyecto o cliente…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\Borrador::class)
                    <x-ui.button as="a" href="{{ route('borradores.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>

            <div class="grid gap-3 md:grid-cols-3">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="convertido">Convertido</option>
                        <option value="papelera">En papelera</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Desde">
                    <x-ui.input wire:key="desde-{{ $resetKey }}" type="date" wire:model.live="filtroDesde" />
                </x-ui.field>

                <x-ui.field label="Hasta">
                    <x-ui.input wire:key="hasta-{{ $resetKey }}" type="date" wire:model.live="filtroHasta" />
                </x-ui.field>
            </div>
        </x-ui.search-and-filter>
    </div>

    {{-- Pills de asignación (solo para usuarios con scope por cliente) --}}
    @if ($this->usuarioEsScoped)
        <div class="mb-3 flex flex-wrap items-center gap-1.5">
            @php
                $pillsAsignacion = [
                    'todos'       => 'Todos',
                    'asignados'   => 'Asignados a mis clientes',
                    'por_revisar' => 'Por revisar (texto libre)',
                ];
            @endphp
            @foreach ($pillsAsignacion as $valor => $label)
                <button type="button"
                        wire:click="setFiltroAsignacion('{{ $valor }}')"
                        @class([
                            'shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                            'bg-primary-700 text-white' => $filtroAsignacion === $valor,
                            'bg-slate-100 text-slate-700 hover:bg-slate-200' => $filtroAsignacion !== $valor,
                        ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Chips de filtros activos --}}
    @if ($this->filtrosAplicados > 0)
        <div class="mb-3 flex flex-wrap gap-2">
            @if ($filtroEstado)
                <x-ui.filter-chip label="Estado: {{ ucfirst($filtroEstado) }}" wire:click="quitarFiltroEstado" />
            @endif
            @if ($filtroDesde)
                <x-ui.filter-chip label="Desde: {{ $filtroDesde }}" wire:click="quitarFiltroDesde" />
            @endif
            @if ($filtroHasta)
                <x-ui.filter-chip label="Hasta: {{ $filtroHasta }}" wire:click="quitarFiltroHasta" />
            @endif
        </div>
    @endif

    {{-- Tabla --}}
    <div class="mb-3 flex items-center justify-between">
        <div class="flex shrink-0 items-center gap-2">
            <span class="text-xs text-slate-500">Filas:</span>
            <select wire:model.live="porPagina"
                    class="rounded-md border-slate-300 py-1 pl-2 pr-7 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="250">250</option>
                <option value="500">500</option>
            </select>
        </div>
        {{ $borradores->links() }}
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                <tr>
                    <th class="px-6 py-3">
                        <button type="button" wire:click="ordenarPor('numero_borrador')" class="flex items-center gap-1 hover:text-slate-200">
                            Nº Borrador
                            @if ($ordenColumna === 'numero_borrador')
                                <x-heroicon-o-chevron-{{ $ordenDireccion === 'asc' ? 'up' : 'down' }} class="size-3" />
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3">Proyecto / Cliente</th>
                    <th class="w-36 px-6 py-3">
                        <button type="button" wire:click="ordenarPor('fecha')" class="flex items-center gap-1 hover:text-slate-200">
                            Fecha
                            @if ($ordenColumna === 'fecha')
                                <x-heroicon-o-chevron-{{ $ordenDireccion === 'asc' ? 'up' : 'down' }} class="size-3" />
                            @endif
                        </button>
                    </th>
                    <th class="w-32 px-6 py-3">Estado</th>
                    <th class="w-28 px-6 py-3">Creador</th>
                    <th class="w-24 px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($borradores as $borrador)
                    <tr wire:key="borrador-{{ $borrador->id }}" class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-mono text-xs font-medium text-slate-800">
                            {{ $borrador->numero_borrador }}
                        </td>
                        <td class="px-6 py-3">
                            <div class="font-medium text-slate-800">{{ $borrador->proyectoNombre() }}</div>
                            <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-400">
                                <span>{{ $borrador->clienteNombre() }}</span>
                                @if ($borrador->cliente_id === null && trim((string) $borrador->cliente_texto) !== '')
                                    <span class="inline-flex items-center rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-800"
                                          title="Cliente en texto libre — pendiente de asignar al convertir">
                                        Texto libre
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3 text-slate-500">
                            {{ $borrador->fecha?->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-3">
                            @if ($borrador->estado === 'convertido')
                                <x-ui.badge tone="success" dot>Convertido</x-ui.badge>
                            @else
                                <x-ui.badge tone="warning" dot>Pendiente</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-slate-500">
                            {{ trim($borrador->creador?->nombre.' '.$borrador->creador?->apellidos) ?: '—' }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if ($borrador->trashed())
                                    @can('restore', $borrador)
                                        <x-ui.icon-button
                                        wire:click="restaurar({{ $borrador->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="restaurar({{ $borrador->id }})"
                                        variant="success"
                                        tooltip="Restaurar">
                                        <span wire:loading.remove wire:target="restaurar({{ $borrador->id }})">
                                            <x-heroicon-o-arrow-path class="size-4" />
                                        </span>
                                        <svg wire:loading wire:target="restaurar({{ $borrador->id }})" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                    </x-ui.icon-button>
                                    @endcan
                                @else
                                    <x-ui.icon-button as="a" href="{{ route('borradores.ver', $borrador) }}" wire:navigate icon="heroicon-o-eye" variant="neutral" tooltip="Ver" />
                                    @can('update', $borrador)
                                        <x-ui.icon-button as="a" href="{{ route('borradores.editar', $borrador) }}" wire:navigate.fresh icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                    @endcan
                                    @can('delete', $borrador)
                                        <x-ui.icon-button wire:click="confirmarEliminar({{ $borrador->id }})" icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">
                            No hay borradores que coincidan con los filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar borrador"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <p class="text-sm text-slate-700">
                ¿Eliminar este borrador? Se enviará a la papelera y podrás restaurarlo después.
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})"
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
