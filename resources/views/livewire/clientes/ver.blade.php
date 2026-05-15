<div class="space-y-4">
    {{-- Cabecera --}}
    <x-ui.page-header :title="$cliente->nombre" :subtitle="$cliente->nombre_comercial ?? 'Ver cliente'">
        <x-slot:actions>
            <x-ui.button as="a" href="{{ route('clientes.index') }}" wire:navigate variant="ghost" icon="heroicon-o-arrow-left">
                Clientes
            </x-ui.button>
            @can('update', $cliente)
                <x-ui.button as="a" href="{{ route('clientes.editar', $cliente) }}" wire:navigate.fresh variant="info" icon="heroicon-o-pencil-square">
                    Editar
                </x-ui.button>
            @endcan
            @can('delete', $cliente)
                <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                    Eliminar
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Datos del cliente (solo lectura) --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field label="Nº cliente">
                <x-ui.input type="number" :value="$cliente->numero_cliente" readonly />
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

    {{-- Proyectos vinculados --}}
    <div x-data="{ abierto: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <button type="button"
                x-on:click="abierto = !abierto"
                class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">
                Proyectos vinculados
                <span class="ml-1 font-normal text-slate-400">({{ $this->proyectosDelCliente->count() }})</span>
            </h2>
            <x-heroicon-o-chevron-down class="size-4 text-slate-400 transition-transform duration-150"
                                       x-bind:class="abierto ? 'rotate-180' : ''" />
        </button>
        <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
            @if ($this->proyectosDelCliente->isEmpty())
                <p class="px-6 py-4 text-sm text-slate-500">Sin proyectos asociados.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Proyecto</th>
                            <th class="px-6 py-2.5">Código</th>
                            <th class="px-6 py-2.5">Tipo</th>
                            <th class="px-6 py-2.5">Estado</th>
                            <th class="px-6 py-2.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->proyectosDelCliente as $proyecto)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-medium text-slate-800">{{ $proyecto->nombre }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $proyecto->codigo ?? '—' }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $proyecto->tipoProyecto?->nombre ?? '—' }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $proyecto->estado ? ucfirst($proyecto->estado) : '—' }}</td>
                                <td class="px-6 py-3 text-right">
                                    <x-ui.icon-button
                                        as="a"
                                        href="/proyectos/{{ $proyecto->id }}"
                                        wire:navigate
                                        icon="heroicon-o-arrow-top-right-on-square"
                                        variant="ghost"
                                        tooltip="Ver proyecto" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Usuarios vinculados --}}
    <div x-data="{ abierto: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <button type="button"
                x-on:click="abierto = !abierto"
                class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">
                Usuarios vinculados
                <span class="ml-1 font-normal text-slate-400">({{ $this->usuariosDeLosProyectos->count() }})</span>
            </h2>
            <x-heroicon-o-chevron-down class="size-4 text-slate-400 transition-transform duration-150"
                                       x-bind:class="abierto ? 'rotate-180' : ''" />
        </button>
        <div x-show="abierto" x-cloak x-transition class="border-t border-slate-100">
            @if ($this->usuariosDeLosProyectos->isEmpty())
                <p class="px-6 py-4 text-sm text-slate-500">Sin usuarios vinculados.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-primary-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-6 py-2.5">Nombre</th>
                            <th class="px-6 py-2.5">Email</th>
                            <th class="px-6 py-2.5">Rol</th>
                            <th class="px-6 py-2.5">Estado</th>
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
                    Esta acción enviará <strong>{{ $cliente->nombre }}</strong> a la papelera.
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
