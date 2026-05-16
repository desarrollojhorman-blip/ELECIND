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
                        Nuevo
                    </x-ui.button>
                @endcan
            </x-slot:leftActions>
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla --}}
    <x-ui.data-table :colspan="4" empty="No hay familias que coincidan con la búsqueda.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header column="descripcion" :current-column="$ordenColumna" :current-direction="$ordenDireccion" align="center">
                    Descripción
                </x-ui.sortable-header>
                <x-ui.sortable-header align="center">Materiales</x-ui.sortable-header>
                <x-ui.sortable-header align="center">Acciones</x-ui.sortable-header>
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
                    <td class="px-4 py-3 text-center text-sm text-slate-700">{{ $familia->materiales_count }}</td>
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

        {{-- Sección materiales (solo en ver/editar de una familia existente) --}}
        @if ($form->id)
            <div class="mt-5 border-t border-slate-200 pt-4">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Materiales en esta familia
                </h3>

                {{-- Mini-formulario añadir material (solo en modo edición) --}}
                @if (! $modoSoloLectura && auth()->user()?->can('materiales.familias.modificar'))
                    <div class="mb-3 rounded-md border border-dashed border-slate-300 bg-slate-50 p-3">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">
                            Añadir material sin familia
                        </label>
                        <div class="flex items-stretch gap-2">
                            <div class="min-w-0 flex-1">
                                <x-ui.select wire:model="materialAAsignar" class="w-full">
                                    <option value="">— Selecciona un material —</option>
                                    @foreach ($this->materialesHuerfanos as $huerfano)
                                        <option value="{{ $huerfano->id }}">
                                            {{ $huerfano->descripcion }}
                                            @if ($huerfano->numeroPedido)
                                                · {{ $huerfano->numeroPedido->numero }}
                                            @endif
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <x-ui.button variant="success" wire:click="agregarMaterialAFamilia"
                                         icon="heroicon-o-plus" class="shrink-0 whitespace-nowrap">
                                Añadir
                            </x-ui.button>
                        </div>
                        @if ($this->materialesHuerfanos->isEmpty())
                            <p class="mt-2 text-xs text-slate-500">
                                No hay materiales sin familia disponibles.
                            </p>
                        @endif
                    </div>
                @endif

                {{-- Lista de materiales (plegable para evitar scroll largo) --}}
                <div class="mt-3" x-data="{ abierto: false }">
                    <div class="mb-2 flex items-center justify-between">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Materiales ya asignados
                            <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-700">
                                {{ $this->materialesDeLaFamiliaActual->count() }}
                            </span>
                        </h4>
                        <button type="button" x-on:click="abierto = !abierto"
                                class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                x-bind:title="abierto ? 'Plegar lista' : 'Desplegar lista'">
                            <x-heroicon-o-chevron-down x-bind:class="abierto ? 'rotate-180' : ''"
                                                       class="size-4 transition-transform" />
                        </button>
                    </div>

                    <div x-show="abierto" x-cloak x-transition>
                        @if ($this->materialesDeLaFamiliaActual->isNotEmpty())
                            <div class="overflow-hidden rounded-md border border-slate-200">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2 text-center">Descripción</th>
                                            <th class="px-3 py-2 text-center">Nº Pedido</th>
                                            <th class="px-3 py-2 text-center">Unidad</th>
                                            <th class="px-3 py-2 text-center">Stock</th>
                                            @if (! $modoSoloLectura)
                                                <th class="px-3 py-2 text-center"></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($this->materialesDeLaFamiliaActual as $mat)
                                            <tr wire:key="famm-{{ $mat->id }}" class="hover:bg-slate-50">
                                                <td class="px-3 py-2 text-slate-800">{{ $mat->descripcion }}</td>
                                                <td class="px-3 py-2 text-slate-600">
                                                    @if ($mat->numeroPedido)
                                                        <span class="inline-flex items-center rounded-full bg-primary-50 px-2 py-0.5 font-mono text-xs text-primary-700">
                                                            {{ $mat->numeroPedido->numero }}
                                                        </span>
                                                    @else
                                                        <span class="text-slate-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-slate-600">{{ $mat->unidad_medida }}</td>
                                                <td class="px-3 py-2 text-right font-mono text-slate-700">
                                                    {{ rtrim(rtrim(number_format((float) $mat->stock, 2, ',', ''), '0'), ',') }}
                                                </td>
                                                @if (! $modoSoloLectura)
                                                    <td class="px-3 py-2 text-right">
                                                        <button type="button"
                                                                wire:click="quitarMaterialDeFamilia({{ $mat->id }})"
                                                                class="text-slate-400 hover:text-red-500"
                                                                title="Quitar de la familia">
                                                            <x-heroicon-o-trash class="size-4" />
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="rounded-md border border-dashed border-slate-200 bg-slate-50 px-3 py-6 text-center text-sm text-slate-500">
                                Esta familia aún no tiene materiales asignados.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <x-slot:footer>
            @if (!$modoSoloLectura)
                <x-ui.button variant="neutral" wire:click="cerrarModal">Cancelar</x-ui.button>
                <x-ui.button variant="info" type="submit" form="form-familia"
                             wire:loading.attr="disabled" icon="heroicon-o-check">
                    Guardar
                </x-ui.button>
            @endif
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
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
