<div class="space-y-4">
    <x-ui.page-header :title="$titulo" subtitle="Datos fiscales y de contacto del cliente.">
        <x-slot:actions>
            @if ($cliente)
                <x-ui.button as="a" href="{{ route('clientes.index') }}" wire:navigate variant="ghost" icon="heroicon-o-arrow-left">
                    Clientes
                </x-ui.button>
                @can('clientes.ver')
                    <x-ui.button as="a" href="{{ route('clientes.crear') }}" wire:navigate variant="ghost" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
                @can('delete', $cliente)
                    <x-ui.button variant="danger" wire:click="confirmarEliminar" icon="heroicon-o-trash">
                        Eliminar
                    </x-ui.button>
                @endcan
            @else
                <x-ui.button as="a" href="{{ route('clientes.index') }}" wire:navigate variant="ghost" icon="heroicon-o-x-mark">
                    Cancelar
                </x-ui.button>
            @endif
            <x-ui.button variant="success" type="submit" form="form-cliente" wire:loading.attr="disabled" icon="heroicon-o-check">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="guardar" id="form-cliente" autocomplete="off">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Código cliente" required :error="$errors->first('form.codigo_cliente')">
                    <x-ui.input wire:model="form.codigo_cliente" class="font-mono" autofocus />
                </x-ui.field>

                <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                    <x-ui.input wire:model="form.nombre" />
                </x-ui.field>

                <x-ui.field label="Nombre comercial" :error="$errors->first('form.nombre_comercial')">
                    <x-ui.input wire:model="form.nombre_comercial" />
                </x-ui.field>

                <x-ui.field label="CIF" :error="$errors->first('form.cif')">
                    <x-ui.input wire:model="form.cif" />
                </x-ui.field>

                <x-ui.field label="Teléfono" :error="$errors->first('form.telefono')">
                    <x-ui.input wire:model="form.telefono" />
                </x-ui.field>

                <x-ui.field label="Email" :error="$errors->first('form.email')">
                    <x-ui.input type="email" wire:model="form.email" />
                </x-ui.field>

                <x-ui.field label="Dirección" :error="$errors->first('form.direccion')">
                    <x-ui.input wire:model="form.direccion" />
                </x-ui.field>

                <x-ui.field label="Código postal" :error="$errors->first('form.codigo_postal')">
                    <x-ui.input wire:model="form.codigo_postal" />
                </x-ui.field>

                <x-ui.field label="Población" :error="$errors->first('form.poblacion')">
                    <x-ui.input wire:model="form.poblacion" />
                </x-ui.field>

                <x-ui.field label="Provincia" :error="$errors->first('form.provincia')">
                    <x-ui.input wire:model="form.provincia" />
                </x-ui.field>

                <x-ui.field label="Observaciones" class="md:col-span-2" :error="$errors->first('form.observaciones')">
                    <x-ui.textarea wire:model="form.observaciones" rows="3" />
                </x-ui.field>

                <div class="md:col-span-2">
                    <x-ui.checkbox wire:model="form.activo" label="Cliente activo" />
                </div>
            </div>
        </div>
    </form>

    {{-- Tablas relacionados: solo en modo edición --}}
    @if ($cliente)
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
    @endif

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
                    Esta acción enviará <strong>{{ $cliente?->nombre }}</strong> a la papelera.
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
