<div class="space-y-4" x-data="{ tab: 'cliente' }">
    <x-ui.page-header :title="$cliente->nombre" :subtitle="$cliente->nombre_comercial ?? 'Ver cliente'">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('clientes.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @can('update', $cliente)
                <x-ui.button as="a" href="{{ route('clientes.editar', $cliente) }}" wire:navigate.fresh variant="neutral" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('clientes.ver')
                <x-ui.button as="a" href="{{ route('clientes.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                    Nuevo
                </x-ui.button>
            @endcan
            @can('clientes.eliminar')
                {{-- @can('clientes.eliminar') (no @can('delete',$cliente)):
                     el botón se ve a quien tenga permiso; el bloqueo por
                     dependencias se gestiona al pulsar (modal informativo). --}}
                <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                    Eliminar
                </x-ui.button>
            @endcan
        </x-slot:actionsLeft>
    </x-ui.page-header>

    <div>
        {{-- Tabs nav --}}
        <div class="flex items-end border-b border-slate-200 px-2 pt-1.5">
            <button type="button"
                    @click="tab = 'cliente'"
                    :class="tab === 'cliente'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                Cliente
            </button>

            @foreach ([
                ['key' => 'proyectos', 'label' => 'Proyectos', 'count' => $this->proyectosDelCliente->count()],
                ['key' => 'usuarios',  'label' => 'Usuarios',  'count' => $this->usuariosDeLosProyectos->count()],
            ] as $t)
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

        {{-- ═══ Tab: Cliente ═══ --}}
        <div x-show="tab === 'cliente'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Código cliente">
                    <x-ui.input :value="$cliente->codigo_cliente" readonly class="font-mono" />
                </x-ui.field>

                <x-ui.field label="Nombre">
                    <x-ui.input :value="$cliente->nombre" readonly />
                </x-ui.field>

                <x-ui.field label="Nombre comercial">
                    <x-ui.input :value="$cliente->nombre_comercial" readonly />
                </x-ui.field>

                <x-ui.field label="CIF">
                    <x-ui.input :value="$cliente->cif" readonly />
                </x-ui.field>

                <x-ui.field label="Teléfono">
                    <x-ui.input :value="$cliente->telefono" readonly />
                </x-ui.field>

                <x-ui.field label="Email">
                    <x-ui.input type="email" :value="$cliente->email" readonly />
                </x-ui.field>

                <x-ui.field label="Dirección">
                    <x-ui.input :value="$cliente->direccion" readonly />
                </x-ui.field>

                <x-ui.field label="Código postal">
                    <x-ui.input :value="$cliente->codigo_postal" readonly />
                </x-ui.field>

                <x-ui.field label="Población">
                    <x-ui.input :value="$cliente->poblacion" readonly />
                </x-ui.field>

                <x-ui.field label="Provincia">
                    <x-ui.input :value="$cliente->provincia" readonly />
                </x-ui.field>

                <x-ui.field label="Observaciones" class="md:col-span-2">
                    <x-ui.textarea :value="$cliente->observaciones" rows="3" readonly />
                </x-ui.field>

                <div class="md:col-span-2">
                    <x-ui.checkbox :checked="$cliente->activo" label="Cliente activo" disabled />
                </div>
            </div>
        </div>

        {{-- ═══ Tab: Proyectos ═══ --}}
        <div x-show="tab === 'proyectos'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Proyectos vinculados</span>
                <p class="mt-0.5 text-xs text-slate-400">Proyectos asociados a este cliente</p>
            </div>
            @if ($this->proyectosDelCliente->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    No hay proyectos asociados a este cliente.
                </div>
            @else
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Proyecto</th>
                                <th class="w-36 px-6 py-2.5">Código</th>
                                <th class="w-40 px-6 py-2.5">Tipo</th>
                                <th class="w-28 px-6 py-2.5">Estado</th>
                                <th class="w-20 px-6 py-2.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->proyectosDelCliente as $proyecto)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-medium text-slate-800">{{ $proyecto->nombre }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $proyecto->codigo ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $proyecto->tipoProyecto?->nombre ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $proyecto->estado ? ucfirst($proyecto->estado) : '—' }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <x-ui.icon-button
                                            as="a"
                                            href="{{ route('proyectos.ver', $proyecto) }}"
                                            wire:navigate
                                            icon="heroicon-o-arrow-top-right-on-square"
                                            variant="ghost"
                                            tooltip="Ver proyecto" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ═══ Tab: Usuarios ═══ --}}
        <div x-show="tab === 'usuarios'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Usuarios vinculados</span>
                <p class="mt-0.5 text-xs text-slate-400">Usuarios asignados a los proyectos de este cliente</p>
            </div>
            @if ($this->usuariosDeLosProyectos->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    No hay usuarios vinculados a los proyectos de este cliente.
                </div>
            @else
                <div class="border-t border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-2.5">Nombre</th>
                                <th class="px-6 py-2.5">Email</th>
                                <th class="w-40 px-6 py-2.5">Rol</th>
                                <th class="w-28 px-6 py-2.5">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->usuariosDeLosProyectos as $usuario)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-medium text-slate-800">{{ trim($usuario->nombre.' '.$usuario->apellidos) }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $usuario->email ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $usuario->getRoleNames()->join(', ') ?: '—' }}</td>
                                    <td class="px-6 py-3">
                                        @if ($usuario->activo)
                                            <x-ui.badge tone="success" dot>Activo</x-ui.badge>
                                        @else
                                            <x-ui.badge tone="neutral" dot>Inactivo</x-ui.badge>
                                        @endif
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
        title="Eliminar cliente"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    ¿Eliminar el cliente <strong>{{ $cliente->nombre }}</strong>?
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Esta acción no se puede deshacer.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">Cancelar</x-ui.button>
            <x-ui.button variant="danger" wire:click="eliminar" icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal informativo: la eliminación está bloqueada por dependencias --}}
    <x-ui.modal
        :show="$bloqueadoEliminarMensaje !== null"
        title="No se puede eliminar"
        close-action="cerrarBloqueo"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    {{ $bloqueadoEliminarMensaje }}
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    Elimina o reasigna primero esos elementos.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarBloqueo">
                Entendido
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
