<div class="space-y-4" x-data="{ tab: 'cliente' }">
    <x-ui.page-header :title="$titulo" subtitle="Datos, proyectos y usuarios vinculados al cliente.">
        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('clientes.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($cliente)
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
            @endif
        </x-slot:actionsLeft>

        <x-slot:actionsRight>
            <x-ui.button variant="neutral" wire:click="deshacer" icon="heroicon-o-arrow-uturn-left">
                Deshacer
            </x-ui.button>
            <x-ui.button variant="info" icon="heroicon-o-arrow-down-tray" type="submit" form="form-cliente" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </x-ui.button>
        </x-slot:actionsRight>
    </x-ui.page-header>

    {{-- Tabs + contenido --}}
    @php $modoCrear = $cliente === null; @endphp
    <div>
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
            ['key' => 'proyectos', 'label' => 'Proyectos', 'count' => $cliente ? $this->proyectosDelCliente->count() : null],
            ['key' => 'usuarios',  'label' => 'Usuarios',  'count' => $cliente ? $this->usuariosDeLosProyectos->count() : null],
        ] as $t)
            @if ($modoCrear)
                <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                      title="Guarda primero el cliente para acceder a esta sección">
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

    {{-- ═══ Tab: Cliente ═══ --}}
    <form wire:submit="guardar" id="form-cliente" autocomplete="off">
        <div x-show="tab === 'cliente'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Código cliente" required :error="$errors->first('form.codigo_cliente')">
                    @if ($cliente === null)
                        <x-ui.input wire:model="form.codigo_cliente" type="number" min="1" max="100000" step="1" class="font-mono" />
                    @else
                        <x-ui.input wire:model="form.codigo_cliente" type="number" class="font-mono" readonly />
                    @endif
                </x-ui.field>

                <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                    <x-ui.input wire:model="form.nombre" maxlength="{{ \App\Support\ClienteFields::getMaxLength('nombre') }}" />
                </x-ui.field>

                <x-ui.field label="Nombre comercial" :error="$errors->first('form.nombre_comercial')">
                    <x-ui.input wire:model="form.nombre_comercial" maxlength="{{ \App\Support\ClienteFields::getMaxLength('nombre_comercial') }}" />
                </x-ui.field>

                <x-ui.field label="CIF" :error="$errors->first('form.cif')">
                    <x-ui.input wire:model="form.cif" maxlength="{{ \App\Support\ClienteFields::getMaxLength('cif') }}" />
                </x-ui.field>

                <x-ui.field label="Teléfono" :error="$errors->first('form.telefono')">
                    <x-ui.input wire:model="form.telefono" maxlength="{{ \App\Support\ClienteFields::getMaxLength('telefono') }}" />
                </x-ui.field>

                <x-ui.field label="Email" :error="$errors->first('form.email')">
                    <x-ui.input type="email" wire:model="form.email" maxlength="{{ \App\Support\ClienteFields::getMaxLength('email') }}" />
                </x-ui.field>

                <x-ui.field label="Dirección" :error="$errors->first('form.direccion')">
                    <x-ui.input wire:model="form.direccion" maxlength="{{ \App\Support\ClienteFields::getMaxLength('direccion') }}" />
                </x-ui.field>

                <x-ui.field label="Código postal" :error="$errors->first('form.codigo_postal')">
                    <x-ui.input wire:model="form.codigo_postal" maxlength="{{ \App\Support\ClienteFields::getMaxLength('codigo_postal') }}" />
                </x-ui.field>

                <x-ui.field label="Población" :error="$errors->first('form.poblacion')">
                    <x-ui.input wire:model="form.poblacion" maxlength="{{ \App\Support\ClienteFields::getMaxLength('poblacion') }}" />
                </x-ui.field>

                <x-ui.field label="Provincia" :error="$errors->first('form.provincia')">
                    <x-ui.input wire:model="form.provincia" maxlength="{{ \App\Support\ClienteFields::getMaxLength('provincia') }}" />
                </x-ui.field>

                <x-ui.field label="Observaciones" class="md:col-span-2" :error="$errors->first('form.observaciones')">
                    <x-ui.textarea wire:model="form.observaciones" rows="3" maxlength="{{ \App\Support\ClienteFields::getMaxLength('observaciones') }}" />
                </x-ui.field>

                <div class="md:col-span-2">
                    <x-ui.checkbox wire:model="form.activo" label="Activo" />
                </div>
            </div>

        </div>
    </form>

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
                                <button type="button" wire:click="ordenarProyectos('tipo')" class="flex items-center gap-1 hover:opacity-80">
                                    Tipo <span class="text-[10px] opacity-70">{{ $ordenProyectos === 'tipo' ? ($dirProyectos === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
                            <th class="w-28 px-6 py-2.5">
                                <button type="button" wire:click="ordenarProyectos('estado')" class="flex items-center gap-1 hover:opacity-80">
                                    Estado <span class="text-[10px] opacity-70">{{ $ordenProyectos === 'estado' ? ($dirProyectos === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
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
                            <th class="px-6 py-2.5">
                                <button type="button" wire:click="ordenarUsuarios('nombre')" class="flex items-center gap-1 hover:opacity-80">
                                    Nombre <span class="text-[10px] opacity-70">{{ $ordenUsuarios === 'nombre' ? ($dirUsuarios === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-2.5">
                                <button type="button" wire:click="ordenarUsuarios('email')" class="flex items-center gap-1 hover:opacity-80">
                                    Email <span class="text-[10px] opacity-70">{{ $ordenUsuarios === 'email' ? ($dirUsuarios === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
                            <th class="w-40 px-6 py-2.5">
                                <button type="button" wire:click="ordenarUsuarios('rol')" class="flex items-center gap-1 hover:opacity-80">
                                    Rol <span class="text-[10px] opacity-70">{{ $ordenUsuarios === 'rol' ? ($dirUsuarios === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
                            <th class="w-28 px-6 py-2.5">
                                <button type="button" wire:click="ordenarUsuarios('estado')" class="flex items-center gap-1 hover:opacity-80">
                                    Estado <span class="text-[10px] opacity-70">{{ $ordenUsuarios === 'estado' ? ($dirUsuarios === 'asc' ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($this->usuariosDeLosProyectos as $usuario)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-medium text-slate-800">
                                    {{ trim($usuario->nombre.' '.$usuario->apellidos) }}
                                </td>
                                <td class="px-6 py-3 text-slate-500">{{ $usuario->email ?? '—' }}</td>
                                <td class="px-6 py-3 text-slate-500">
                                    {{ $usuario->getRoleNames()->join(', ') ?: '—' }}
                                </td>
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
    </div>{{-- /tabs + contenido --}}

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
                    ¿Eliminar el cliente <strong>{{ $cliente?->nombre }}</strong>?
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
