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
                            <div class="text-xs text-slate-400">{{ $borrador->clienteNombre() }}</div>
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
                                        <x-ui.icon-button wire:click="restaurar({{ $borrador->id }})" icon="heroicon-o-arrow-path" variant="ghost" tooltip="Restaurar" />
                                    @endcan
                                @else
                                    <x-ui.icon-button as="a" href="{{ route('borradores.ver', $borrador) }}" wire:navigate icon="heroicon-o-eye" variant="ghost" tooltip="Ver" />
                                    @can('update', $borrador)
                                        <x-ui.icon-button as="a" href="{{ route('borradores.editar', $borrador) }}" wire:navigate.fresh icon="heroicon-o-pencil-square" variant="ghost" tooltip="Editar" />
                                    @endcan
                                    @can('delete', $borrador)
                                        <x-ui.icon-button wire:click="confirmarEliminar({{ $borrador->id }})" icon="heroicon-o-trash" variant="ghost-danger" tooltip="Eliminar" />
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

        @if ($borradores->hasPages())
            <div class="border-t border-slate-100 px-6 py-3">
                {{ $borradores->links() }}
            </div>
        @endif
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
            <x-ui.button variant="danger" wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
