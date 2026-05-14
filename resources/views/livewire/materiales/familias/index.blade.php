<div>
    <x-ui.page-header title="Familias de Material"
                      subtitle="Agrupa materiales que representan el mismo artículo aunque vengan de pedidos distintos." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por nombre o descripción…"
            :filtros-aplicados="0"
            panel-toggle=""
            :panel-open="false"
            :reset-key="$resetKey"
            clear-all-action="limpiarBuscador"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\FamiliaMaterial::class)
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
                        Nueva familia
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla --}}
    <x-ui.data-table :colspan="4" empty="No hay familias que coincidan con la búsqueda.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header column="descripcion" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Descripción
                </x-ui.sortable-header>
                <x-ui.sortable-header>Materiales</x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($familias as $familia)
                <tr wire:key="fam-{{ $familia->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $familia->nombre }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $familia->descripcion ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                            {{ $familia->materiales_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($familia->trashed())
                                @can('restore', $familia)
                                    <x-ui.icon-button wire:click="restaurar({{ $familia->id }})"
                                        icon="heroicon-o-arrow-uturn-left" variant="success" tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('view', $familia)
                                    <x-ui.icon-button wire:click="abrirVer({{ $familia->id }})"
                                        icon="heroicon-o-eye" variant="secondary" tooltip="Ver" />
                                @endcan
                                @can('update', $familia)
                                    <x-ui.icon-button wire:click="abrirEditar({{ $familia->id }})"
                                        icon="heroicon-o-pencil-square" variant="info" tooltip="Editar" />
                                @endcan
                                @can('delete', $familia)
                                    <x-ui.icon-button wire:click="confirmarEliminar({{ $familia->id }})"
                                        icon="heroicon-o-trash" variant="danger" tooltip="Eliminar" />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    <div class="mt-3">{{ $familias->links() }}</div>

    {{-- Modal crear / editar / ver familia --}}
    <x-ui.modal :show="$modalAbierto"
        :title="$modoSoloLectura ? 'Ver familia' : ($form->id ? 'Editar familia' : 'Nueva familia')"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-familia" class="space-y-4">
            <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                <x-ui.input wire:model="form.nombre" autofocus
                            placeholder="Ej. Cables H07V-K"
                            :disabled="$modoSoloLectura" />
            </x-ui.field>

            <x-ui.field label="Descripción" :error="$errors->first('form.descripcion')"
                        hint="Opcional. Aparece en el listado.">
                <x-ui.input wire:model="form.descripcion"
                            placeholder="Ej. Cable flexible 750V para instalaciones interiores"
                            :disabled="$modoSoloLectura" />
            </x-ui.field>
        </form>

        {{-- Panel de materiales asignados (solo si la familia ya existe) --}}
        @if ($form->id)
            <div class="mt-5 border-t border-slate-200 pt-4">
                <div x-data="{ abierto: true }">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Materiales en esta familia
                            <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-700">
                                {{ $this->materialesDeLaFamiliaActual->count() }}
                            </span>
                        </h3>
                        <div class="flex items-center gap-2">
                            @if (! $modoSoloLectura && auth()->user()?->can('materiales.familias.modificar'))
                                <x-ui.button variant="secondary" size="sm" wire:click="abrirModalAsignar"
                                             icon="heroicon-o-plus">
                                    Asignar materiales
                                </x-ui.button>
                            @endif
                            <button type="button" x-on:click="abierto = !abierto"
                                    class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <x-heroicon-o-chevron-down x-bind:class="abierto ? 'rotate-180' : ''"
                                                           class="size-4 transition-transform" />
                            </button>
                        </div>
                    </div>

                    <div x-show="abierto" x-cloak x-transition>
                        @if ($this->materialesDeLaFamiliaActual->isEmpty())
                            <p class="rounded-md border border-dashed border-slate-200 bg-slate-50 px-3 py-6 text-center text-sm text-slate-500">
                                Esta familia aún no tiene materiales asignados.
                                @if (! $modoSoloLectura)
                                    Usa <strong>Asignar materiales</strong> para añadir.
                                @endif
                            </p>
                        @else
                            <ul class="space-y-1.5">
                                @foreach ($this->materialesDeLaFamiliaActual as $mat)
                                    <li wire:key="famm-{{ $mat->id }}"
                                        class="flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-slate-900 truncate">
                                                {{ $mat->descripcion }}
                                            </div>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                                @if ($mat->numeroPedido)
                                                    <span class="inline-flex items-center rounded-full bg-primary-50 px-2 py-0.5 font-mono text-primary-700">
                                                        {{ $mat->numeroPedido->numero }}
                                                    </span>
                                                @endif
                                                <span>
                                                    Stock:
                                                    <span class="font-mono font-semibold text-slate-700">
                                                        {{ rtrim(rtrim(number_format((float) $mat->stock, 2, ',', ''), '0'), ',') }}
                                                    </span>
                                                    {{ $mat->unidad_medida }}
                                                </span>
                                            </div>
                                        </div>
                                        @if (! $modoSoloLectura)
                                            <button type="button"
                                                    wire:click="quitarMaterialDeFamilia({{ $mat->id }})"
                                                    class="rounded-md p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                                    title="Quitar de la familia">
                                                <x-heroicon-o-x-mark class="size-4" />
                                            </button>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <x-slot:footer>
            @if ($modoSoloLectura)
                <x-ui.button variant="ghost" wire:click="cerrarModal">Cerrar</x-ui.button>
            @else
                <x-ui.button variant="ghost" wire:click="cerrarModal">Cancelar</x-ui.button>
                <x-ui.button variant="success" type="submit" form="form-familia"
                             wire:loading.attr="disabled" icon="heroicon-o-check">
                    Guardar
                </x-ui.button>
            @endif
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal "Asignar materiales a esta familia" --}}
    <x-ui.modal :show="$modalAsignarAbierto"
        title="Asignar materiales"
        close-action="cerrarModalAsignar"
        size="xl">

        <div class="space-y-3">
            {{-- Buscador + toggle --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <x-ui.input wire:model.live.debounce.300ms="buscarAsignar"
                                placeholder="Buscar por descripción, unidad o nº pedido…" />
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" wire:model.live="mostrarTodosAsignar"
                           class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    <span>Mostrar también materiales con otra familia</span>
                </label>
            </div>

            {{-- Tabla con checkboxes --}}
            <div class="max-h-[50vh] overflow-y-auto rounded-md border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50">
                        <tr>
                            <th class="w-10 px-3 py-2"></th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Descripción</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Pedido</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Familia actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($this->materialesAsignables as $mat)
                            <tr wire:key="asig-{{ $mat->id }}" class="hover:bg-slate-50">
                                <td class="px-3 py-2">
                                    <input type="checkbox"
                                           wire:model.live="materialesSeleccionados"
                                           value="{{ $mat->id }}"
                                           class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-slate-900">{{ $mat->descripcion }}</div>
                                    <div class="text-xs text-slate-500">
                                        Stock: {{ rtrim(rtrim(number_format((float) $mat->stock, 2, ',', ''), '0'), ',') }}
                                        {{ $mat->unidad_medida }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    @if ($mat->numeroPedido)
                                        <span class="font-mono text-xs text-primary-700">
                                            {{ $mat->numeroPedido->numero }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if ($mat->familia)
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">
                                            {{ $mat->familia->nombre }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Sin familia</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-8 text-center text-sm text-slate-500">
                                    @if ($mostrarTodosAsignar)
                                        No hay materiales que coincidan con la búsqueda.
                                    @else
                                        No hay materiales sin familia que coincidan. Activa el toggle para incluir materiales con otra familia.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-slate-500">
                Mostrando hasta 100 resultados. Refina la búsqueda si no encuentras el material.
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cerrarModalAsignar">Cancelar</x-ui.button>
            <x-ui.button variant="success" wire:click="asignarSeleccionados"
                         :disabled="empty($materialesSeleccionados)" icon="heroicon-o-check">
                Asignar {{ count($materialesSeleccionados) > 0 ? count($materialesSeleccionados).' material'.(count($materialesSeleccionados) === 1 ? '' : 'es') : '' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal :show="$confirmarEliminarId !== null"
        title="Eliminar familia"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará la familia a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Los materiales asignados quedarán <strong>sin familia</strong>, pero no se borran.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
