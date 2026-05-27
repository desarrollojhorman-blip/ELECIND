<div class="space-y-4" x-data="{ tab: 'material' }">
    <x-ui.page-header :title="$titulo" :id-badge="$material?->id" subtitle="Datos del material y relaciones vinculadas.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('materiales.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($material)
                @can('create', App\Models\Material::class)
                    <x-ui.button as="a" href="{{ route('materiales.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
                @can('delete', $material)
                    <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                        Eliminar
                    </x-ui.button>
                @endcan
            @endif
        </x-slot:actionsLeft>

        <x-slot:actionsRight>
            <x-ui.button variant="neutral" wire:click="deshacer" wire:loading.attr="disabled" wire:target="deshacer">
                <x-heroicon-o-arrow-uturn-left wire:loading.remove wire:target="deshacer" class="size-4" />
                <svg wire:loading wire:target="deshacer" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="deshacer">Deshacer</span>
                <span wire:loading wire:target="deshacer">Deshaciendo…</span>
            </x-ui.button>
            <x-ui.button variant="info" type="submit" form="form-material" wire:loading.attr="disabled" wire:target="guardar">
                <x-heroicon-o-arrow-down-tray wire:loading.remove wire:target="guardar" class="size-4" />
                <svg wire:loading wire:target="guardar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actionsRight>
    </x-ui.page-header>

    @php $modoCrear = $material === null; @endphp

    <div>
        {{-- Tabs nav --}}
        <div class="flex items-end border-b border-slate-200 px-2 pt-1.5">
            <button type="button"
                    @click="tab = 'material'"
                    :class="tab === 'material'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                Material
                @if ($material)
                    <span class="font-mono text-xs font-normal text-slate-400">#{{ $material->id }}</span>
                @endif
            </button>

            @foreach ([
                ['key' => 'albaranes', 'label' => 'Albaranes', 'count' => $material ? $this->albaranesDelMaterial->count() : null],
                ['key' => 'proyectos', 'label' => 'Proyectos', 'count' => $material ? $this->proyectosDelMaterial->count() : null],
            ] as $t)
                @if ($modoCrear)
                    <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                          title="Guarda primero el material para acceder a esta sección">
                        <x-heroicon-o-lock-closed class="size-3" />
                        {{ $t['label'] }}
                    </span>
                @else
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
                @endif
            @endforeach
        </div>

        {{-- ═══ Tab: Material ═══ --}}
        <form wire:submit="guardar" id="form-material" autocomplete="off">
            <div x-show="tab === 'material'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Nº Pedido" required :error="$errors->first('form.numero_pedido_id')">
                        <x-ui.searchable-select
                            wire-model="form.numero_pedido_id"
                            :value="$form->numero_pedido_id"
                            :options="$this->pedidosDisponibles->map(fn($p) => ['value' => $p->id, 'label' => $p->numero.($p->proveedor ? ' ('.$p->proveedor.')' : '')])->all()"
                            placeholder="— Selecciona un pedido —"
                        />
                    </x-ui.field>

                    <x-ui.field label="Familia" :error="$errors->first('form.familia_id')"
                                hint="Opcional.">
                        <x-ui.searchable-select
                            wire-model="form.familia_id"
                            :value="$form->familia_id"
                            :options="$this->familiasDisponibles->map(fn($f) => ['value' => $f->id, 'label' => $f->id.' · '.$f->nombre])->all()"
                            placeholder="— Sin familia —"
                        />
                    </x-ui.field>

                    <x-ui.field label="Descripción" required :error="$errors->first('form.descripcion')" class="md:col-span-2">
                        <x-ui.input wire:model="form.descripcion" placeholder="Ej. Cable H07V-K 2,5mm² negro" autofocus />
                    </x-ui.field>

                    <x-ui.field label="Unidad de medida" required :error="$errors->first('form.unidad_medida')">
                        <x-ui.select wire:model="form.unidad_medida">
                            <option value="ud">ud (unidades)</option>
                            <option value="m">m (metros)</option>
                            <option value="kg">kg (kilogramos)</option>
                            <option value="l">l (litros)</option>
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Stock" required :error="$errors->first('form.stock')">
                        <x-ui.input type="number" step="0.01" min="0" wire:model="form.stock" class="font-mono" />
                    </x-ui.field>
                </div>

                {{-- Precios (€) — visible solo a quien tenga `materiales.gestionar_precios`.
                     Por defecto solo lo tiene superadmin; otorgable desde Roles.
                     En el albarán solo se muestra `precio_venta`; `precio_coste`
                     queda para informes internos (margen, etc.). --}}
                @can('materiales.gestionar_precios')
                    <h3 class="mt-6 mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Precios (€)</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ui.field label="Precio coste €" :error="$errors->first('form.precio_coste')">
                            <x-ui.input type="number" step="0.01" min="0"
                                        wire:model="form.precio_coste"
                                        placeholder="0,00"
                                        class="font-mono" />
                        </x-ui.field>

                        <x-ui.field label="Precio venta €" :error="$errors->first('form.precio_venta')">
                            <x-ui.input type="number" step="0.01" min="0"
                                        wire:model="form.precio_venta"
                                        placeholder="0,00"
                                        class="font-mono" />
                        </x-ui.field>
                    </div>
                @endcan

                {{-- Material activo: standalone al final. --}}
                <div class="mt-6 border-t border-slate-100 pt-4">
                    <x-ui.checkbox wire:model="form.activo" label="Material activo" />
                </div>
            </div>
        </form>

        {{-- ═══ Tab: Albaranes ═══ --}}
        <div x-show="tab === 'albaranes'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Albaranes vinculados</span>
                <p class="mt-0.5 text-xs text-slate-400">Albaranes que incluyen este material en sus líneas</p>
            </div>
            @if ($this->albaranesDelMaterial->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    No hay albaranes vinculados a este material.
                </div>
            @else
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="w-32 px-6 py-2.5">
                                    <button type="button" wire:click="ordenarAlbaranes('numero')" class="flex items-center gap-1 hover:opacity-80">
                                        Número <span class="text-[10px] opacity-70">{{ $ordenAlbaranes === 'numero' ? ($dirAlbaranes === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="w-32 px-6 py-2.5">
                                    <button type="button" wire:click="ordenarAlbaranes('fecha')" class="flex items-center gap-1 hover:opacity-80">
                                        Fecha <span class="text-[10px] opacity-70">{{ $ordenAlbaranes === 'fecha' ? ($dirAlbaranes === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="px-6 py-2.5">
                                    <button type="button" wire:click="ordenarAlbaranes('proyecto')" class="flex items-center gap-1 hover:opacity-80">
                                        Proyecto <span class="text-[10px] opacity-70">{{ $ordenAlbaranes === 'proyecto' ? ($dirAlbaranes === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="w-40 px-6 py-2.5">
                                    <button type="button" wire:click="ordenarAlbaranes('cliente')" class="flex items-center gap-1 hover:opacity-80">
                                        Cliente <span class="text-[10px] opacity-70">{{ $ordenAlbaranes === 'cliente' ? ($dirAlbaranes === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="w-28 px-6 py-2.5">
                                    <button type="button" wire:click="ordenarAlbaranes('estado')" class="flex items-center gap-1 hover:opacity-80">
                                        Estado <span class="text-[10px] opacity-70">{{ $ordenAlbaranes === 'estado' ? ($dirAlbaranes === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="w-16 px-6 py-2.5 text-right">Ir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->albaranesDelMaterial as $albaran)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-mono text-xs text-slate-700">{{ $albaran->numero ?? '#'.$albaran->id }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $albaran->fecha?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-700">{{ $albaran->proyecto?->nombre ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $albaran->cliente?->nombre ?? '—' }}</td>
                                    <td class="px-6 py-3">
                                        @php $estado = $albaran->estado instanceof \BackedEnum ? $albaran->estado->value : (string) $albaran->estado; @endphp
                                        <x-ui.badge :tone="match($estado) {
                                            'firmado', 'facturado' => 'success',
                                            'pendiente' => 'warning',
                                            default => 'neutral'
                                        }" dot>{{ ucfirst($estado) }}</x-ui.badge>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <x-ui.icon-button as="a" href="{{ route('albaranes.ver', $albaran) }}" wire:navigate
                                            icon="heroicon-o-arrow-top-right-on-square" variant="info" tooltip="Ver albarán" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ═══ Tab: Proyectos ═══ --}}
        <div x-show="tab === 'proyectos'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Proyectos vinculados</span>
                <p class="mt-0.5 text-xs text-slate-400">Proyectos en los que se usa este material</p>
            </div>
            @if ($this->proyectosDelMaterial->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    No hay proyectos vinculados a este material.
                </div>
            @else
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">
                                    <button type="button" wire:click="ordenarProyectos('nombre')" class="flex items-center gap-1 hover:opacity-80">
                                        Proyecto <span class="text-[10px] opacity-70">{{ $ordenProyectos === 'nombre' ? ($dirProyectos === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="w-36 px-6 py-2.5">
                                    <button type="button" wire:click="ordenarProyectos('codigo')" class="flex items-center gap-1 hover:opacity-80">
                                        Código <span class="text-[10px] opacity-70">{{ $ordenProyectos === 'codigo' ? ($dirProyectos === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="w-40 px-6 py-2.5">
                                    <button type="button" wire:click="ordenarProyectos('cliente')" class="flex items-center gap-1 hover:opacity-80">
                                        Cliente <span class="text-[10px] opacity-70">{{ $ordenProyectos === 'cliente' ? ($dirProyectos === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="w-28 px-6 py-2.5">
                                    <button type="button" wire:click="ordenarProyectos('estado')" class="flex items-center gap-1 hover:opacity-80">
                                        Estado <span class="text-[10px] opacity-70">{{ $ordenProyectos === 'estado' ? ($dirProyectos === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                    </button>
                                </th>
                                <th class="w-16 px-6 py-2.5 text-right">Ir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->proyectosDelMaterial as $proyecto)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-medium text-slate-800">{{ $proyecto->nombre }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $proyecto->codigo ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $proyecto->cliente?->nombre ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $proyecto->estado ? ucfirst($proyecto->estado) : '—' }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <x-ui.icon-button as="a" href="{{ route('proyectos.ver', $proyecto) }}" wire:navigate
                                            icon="heroicon-o-arrow-top-right-on-square" variant="info" tooltip="Ver proyecto" />
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
    <x-ui.modal :show="$confirmarEliminarId !== null" title="Eliminar material" close-action="cancelarEliminar" size="sm">
        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">¿Eliminar el material <strong>{{ $material?->descripcion }}</strong>?</p>
                <p class="mt-1 text-sm text-slate-500">El material irá a la papelera y podrá restaurarse después.</p>
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
