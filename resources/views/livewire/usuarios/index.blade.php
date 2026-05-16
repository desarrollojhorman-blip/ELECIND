<div>
    <x-ui.page-header title="Usuarios" subtitle="Gestión de usuarios internos y responsables externos con roles y niveles." />

    {{-- Toolbar --}}
    <div class="mb-3">
        <x-ui.search-and-filter
            search-model="buscar"
            placeholder="Buscar por usuario, nombre, email o DNI…"
            :filtros-aplicados="$this->filtrosAplicados"
            panel-toggle="togglePanelFiltros"
            :panel-open="$panelFiltrosAbierto"
            :reset-key="$resetKey"
            clear-all-action="limpiarFiltros"
            clear-search-action="limpiarBuscador"
            :has-content-to-clear="$this->tieneAlgoQueLimpiar">

            <x-slot:leftActions>
                @can('create', App\Models\User::class)
                    <x-ui.button variant="success" wire:click="abrirCrear" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan

                <x-ui.actions-menu label="Acciones" icon="heroicon-o-bars-3">
                    <x-ui.actions-menu-item icon="heroicon-o-arrow-up-tray" disabled badge="Pronto">
                        Importar desde Excel/CSV
                    </x-ui.actions-menu-item>
                    <x-ui.actions-menu-divider />
                    <x-ui.actions-menu-item icon="heroicon-o-arrow-down-tray" disabled badge="Pronto">
                        Exportar a Excel
                    </x-ui.actions-menu-item>
                    <x-ui.actions-menu-item icon="heroicon-o-document-arrow-down" disabled badge="Pronto">
                        Exportar a PDF
                    </x-ui.actions-menu-item>
                </x-ui.actions-menu>
            </x-slot:leftActions>

            <div class="grid gap-3 md:grid-cols-4">
                <x-ui.field label="Estado">
                    <x-ui.select wire:key="estado-{{ $resetKey }}" wire:model.live="filtroEstado">
                        <option value="activos">Activos</option>
                        <option value="inactivos">Inactivos</option>
                        <option value="papelera">En papelera</option>
                        <option value="todos">Todos</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Tipo">
                    <x-ui.select wire:key="tipo-{{ $resetKey }}" wire:model.live="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="interno">Interno (Elecind)</option>
                        <option value="externo">Externo (cliente)</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Rol">
                    <x-ui.select wire:key="rol-{{ $resetKey }}" wire:model.live="filtroRol">
                        <option value="">Todos los roles</option>
                        @foreach ($this->rolesDisponibles as $rol)
                            <option value="{{ $rol->name }}">{{ ucfirst($rol->name) }} (nivel {{ $rol->nivel }})</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Cliente">
                    <x-ui.input
                        wire:key="cliente-{{ $resetKey }}"
                        wire:model.live.debounce.300ms="filtroEmpresaCliente"
                        placeholder="Escribe nombre del cliente..." />
                </x-ui.field>
            </div>

            @if ($this->filtrosAplicados > 0)
                <x-slot:chips>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">Filtros aplicados:</span>
                        @if ($filtroEstado !== 'activos')
                            <x-ui.filter-chip label="Estado" :value="ucfirst($filtroEstado)" remove-action="quitarFiltroEstado" />
                        @endif
                        @if ($filtroTipo !== null)
                            <x-ui.filter-chip label="Tipo" :value="ucfirst($filtroTipo)" remove-action="quitarFiltroTipo" />
                        @endif
                        @if ($filtroRol !== null)
                            <x-ui.filter-chip label="Rol" :value="ucfirst($filtroRol)" remove-action="quitarFiltroRol" />
                        @endif
                        @if (trim($filtroEmpresaCliente) !== '')
                            <x-ui.filter-chip label="Empresa"
                            :value="$filtroEmpresaCliente"
                                remove-action="quitarFiltroEmpresaCliente" />
                        @endif
                        <button type="button"
                                wire:click="limpiarFiltros"
                                class="text-xs text-slate-500 underline hover:text-slate-700">
                            Limpiar todos
                        </button>
                    </div>
                </x-slot:chips>
            @endif
        </x-ui.search-and-filter>
    </div>

    {{-- Tabla --}}
    <x-ui.data-table :colspan="7" empty="No hay usuarios que coincidan con los filtros aplicados.">
        <x-slot:head>
            <tr>
                <x-ui.sortable-header column="username" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Usuario
                </x-ui.sortable-header>
                <x-ui.sortable-header column="nombre" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Nombre
                </x-ui.sortable-header>
                <x-ui.sortable-header>Rol</x-ui.sortable-header>
                <x-ui.sortable-header column="email" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Email
                </x-ui.sortable-header>
                <x-ui.sortable-header column="tipo_usuario" :current-column="$ordenColumna" :current-direction="$ordenDireccion">
                    Tipo
                </x-ui.sortable-header>
                <x-ui.sortable-header>Estado</x-ui.sortable-header>
                <x-ui.sortable-header align="right">Acciones</x-ui.sortable-header>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @foreach ($usuarios as $usuario)
                <tr wire:key="usuario-{{ $usuario->id }}" class="transition-colors hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-mono text-sm font-medium text-slate-900">{{ $usuario->username }}</div>
                        @php $rolPrincipal = $usuario->roles->first(); @endphp
                        @if ($rolPrincipal)
                            <div class="text-xs text-slate-400">Acceso: {{ ucfirst($rolPrincipal->acceso) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ trim($usuario->nombre.' '.$usuario->apellidos) }}</div>
                        @if ($usuario->telefono)
                            <div class="text-xs text-slate-500">{{ $usuario->telefono }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php $rol = $usuario->roles->first(); @endphp
                        @if ($rol)
                            <x-ui.badge tone="brand">{{ ucfirst($rol->name) }}</x-ui.badge>
                            <div class="mt-0.5 text-xs text-slate-400">Nivel {{ $rol->nivel }}</div>
                        @else
                            <span class="text-xs text-slate-400">Sin rol</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        <div class="text-sm">{{ $usuario->email ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        <div>{{ ucfirst($usuario->tipo_usuario) }}</div>
                        @if ($usuario->cliente)
                            <div class="text-xs text-slate-400">{{ $usuario->cliente->nombre }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($usuario->trashed())
                            <x-ui.badge tone="danger" dot>Eliminado</x-ui.badge>
                        @elseif ($usuario->activo)
                            <x-ui.badge tone="success" dot>Activo</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral" dot>Inactivo</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @if ($usuario->trashed())
                                @can('restore', $usuario)
                                    <x-ui.icon-button
                                        wire:click="restaurar({{ $usuario->id }})"
                                        icon="heroicon-o-arrow-uturn-left"
                                        variant="success"
                                        tooltip="Restaurar" />
                                @endcan
                            @else
                                @can('view', $usuario)
                                    <x-ui.icon-button
                                        wire:click="abrirVer({{ $usuario->id }})"
                                        icon="heroicon-o-eye"
                                        variant="neutral"
                                        tooltip="Ver detalle" />
                                @endcan
                                @can('update', $usuario)
                                    <x-ui.icon-button
                                        wire:click="abrirEditar({{ $usuario->id }})"
                                        icon="heroicon-o-pencil-square"
                                        variant="info"
                                        tooltip="Editar" />
                                @endcan
                                @can('delete', $usuario)
                                    <x-ui.icon-button
                                        wire:click="confirmarEliminar({{ $usuario->id }})"
                                        icon="heroicon-o-trash"
                                        variant="danger"
                                        tooltip="Eliminar" />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-ui.data-table>

    <div class="mt-3">
        {{ $usuarios->links() }}
    </div>

    {{-- Modal crear/editar/ver --}}
    <x-ui.modal
        :show="$modalAbierto"
        :title="$modoSoloLectura ? 'Ver usuario' : ($form->id ? 'Editar usuario' : 'Nuevo usuario')"
        close-action="cerrarModal"
        size="lg">

        <form wire:submit="guardar" id="form-usuario" class="space-y-5">
            {{-- Acceso y rol --}}
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Acceso y rol</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Usuario" required :error="$errors->first('form.username')">
                        <x-ui.input wire:model.live.debounce.500ms="form.username" :disabled="$modoSoloLectura" autofocus />
                    </x-ui.field>

                    <x-ui.field label="Contraseña"
                                :required="$form->id === null"
                                :error="$errors->first('form.password')">
                        <div class="space-y-2">
                            <div class="flex items-stretch overflow-hidden rounded-md border border-slate-300 bg-white focus-within:border-primary-500">
                                <div class="flex-1">
                                    <x-ui.input wire:key="password-{{ $passwordRenderKey }}" :type="$mostrarPassword ? 'text' : 'password'" wire:model.live="form.password" :disabled="$modoSoloLectura" class="rounded-none border-0 bg-transparent focus:border-0" />
                                </div>
                                @if (!$modoSoloLectura)
                                    <button
                                        type="button"
                                        wire:click.prevent="generarPasswordSegura"
                                        class="inline-flex w-8 items-center justify-center self-stretch border-l border-slate-300 bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-900"
                                        title="Generar contraseña segura"
                                        aria-label="Generar contraseña segura">
                                        <x-heroicon-o-arrow-path class="size-3.5" />
                                    </button>
                                @endif
                                <button
                                    type="button"
                                    wire:click.prevent="toggleMostrarPassword"
                                    class="inline-flex w-8 items-center justify-center self-stretch border-l border-slate-300 bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-900"
                                    title="{{ $mostrarPassword ? 'Ocultar contraseña' : 'Mostrar contraseña' }}"
                                    aria-label="{{ $mostrarPassword ? 'Ocultar contraseña' : 'Mostrar contraseña' }}">
                                    <x-dynamic-component :component="$mostrarPassword ? 'heroicon-o-eye-slash' : 'heroicon-o-eye'" class="size-3.5" />
                                </button>
                            </div>
                            @if ($form->id !== null)
                                <p class="text-xs text-slate-400">Déjala vacía para mantener la contraseña actual.</p>
                            @endif
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Rol" required :error="$errors->first('form.rol')">
                        <x-ui.select wire:model="form.rol" :disabled="$modoSoloLectura">
                            @foreach ($this->rolesDisponibles as $rol)
                                <option value="{{ $rol->name }}">{{ ucfirst($rol->name) }} (nivel {{ $rol->nivel }})</option>
                            @endforeach
                        </x-ui.select>
                        <p class="mt-1 text-xs text-slate-400">Acceso del rol: {{ ucfirst($this->accesoRolSeleccionado) }}</p>
                    </x-ui.field>

                    <x-ui.field label="Tipo usuario" required :error="$errors->first('form.tipo_usuario')">
                        <x-ui.select wire:model.live="form.tipo_usuario" :disabled="$modoSoloLectura">
                            <option value="interno">Interno (Elecind)</option>
                            <option value="externo">Externo (cliente)</option>
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Empresa cliente"
                                :required="$form->tipo_usuario === 'externo'"
                                :error="$errors->first('form.cliente_id')">
                        <x-ui.select wire:model="form.cliente_id" :disabled="$modoSoloLectura || $form->tipo_usuario !== 'externo'">
                            <option value="">— Ninguna —</option>
                            @foreach ($this->empresasDisponibles as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                </div>
            </div>

            {{-- Datos personales --}}
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Datos personales</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                        <x-ui.input wire:model.blur="form.nombre" :disabled="$modoSoloLectura" />
                    </x-ui.field>

                    <x-ui.field label="Apellidos" :error="$errors->first('form.apellidos')">
                        <x-ui.input wire:model.blur="form.apellidos" :disabled="$modoSoloLectura" />
                    </x-ui.field>

                    <x-ui.field label="Email" :error="$errors->first('form.email')">
                        <x-ui.input type="email" wire:model="form.email" :disabled="$modoSoloLectura" />
                    </x-ui.field>

                    <x-ui.field label="Teléfono" :error="$errors->first('form.telefono')">
                        <x-ui.input wire:model="form.telefono" :disabled="$modoSoloLectura" />
                    </x-ui.field>

                    <x-ui.field label="DNI" :error="$errors->first('form.dni')">
                        <x-ui.input wire:model="form.dni" :disabled="$modoSoloLectura" />
                    </x-ui.field>

                    <div class="md:col-span-2">
                        <x-ui.checkbox wire:model="form.activo" label="Usuario activo" :disabled="$modoSoloLectura" />
                    </div>
                </div>
            </div>
        </form>

        <x-slot:footer>
            @if (!$modoSoloLectura)
                <x-ui.button variant="neutral" wire:click="cerrarModal">
                    Cancelar
                </x-ui.button>
                <x-ui.button variant="info" type="submit" form="form-usuario" wire:loading.attr="disabled" icon="heroicon-o-check">
                    Guardar
                </x-ui.button>
            @endif
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal duplicados (no bloqueante) --}}
    <x-ui.modal
        :show="$modalDuplicadosAbierto"
        title="Posibles duplicados detectados"
        close-action="cancelarDuplicados"
        size="md">

        <div class="space-y-3">
            <div class="flex gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                    <x-heroicon-o-exclamation-triangle class="size-5" />
                </div>
                <div>
                    <p class="text-sm text-slate-700">
                        Hay usuarios existentes que comparten datos con el que estás creando.
                        ¿Quieres <strong>usar el existente</strong> o <strong>crear uno nuevo igualmente</strong>?
                    </p>
                </div>
            </div>

            <ul class="space-y-2">
                @foreach ($duplicadosEncontrados as $dup)
                    <li class="flex items-center justify-between gap-3 rounded-md border border-amber-200 bg-amber-50/50 px-3 py-2">
                        <div class="min-w-0">
                            <p class="text-sm">
                                <span class="font-semibold uppercase tracking-wide text-amber-700">{{ $dup['campo'] }}</span>
                                <span class="ml-1 text-slate-600">«{{ $dup['valor'] }}»</span>
                            </p>
                            <p class="text-xs text-slate-500">
                                Coincide con
                                <strong>{{ $dup['usuario_nombre'] ?: '(sin nombre)' }}</strong>
                                @if ($dup['eliminado'])
                                    <span class="ml-1 text-red-500">(en papelera)</span>
                                @endif
                            </p>
                        </div>
                        <x-ui.button variant="info" size="sm" wire:click="usarExistente({{ $dup['usuario_id'] }})">
                            Usar este
                        </x-ui.button>
                    </li>
                @endforeach
            </ul>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarDuplicados">
                Volver al formulario
            </x-ui.button>
            <x-ui.button variant="success"
                         wire:click="confirmarCrearAunqueDuplicado"
                         icon="heroicon-o-plus">
                Crear nuevo igualmente
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal
        :show="$confirmarEliminarId !== null"
        title="Eliminar usuario"
        close-action="cancelarEliminar"
        size="sm">

        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">
                    Esta acción enviará el usuario a la <strong>papelera</strong> (eliminación lógica).
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Podrás restaurarlo más tarde desde el filtro <em>«En papelera»</em>.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarEliminar">
                Cancelar
            </x-ui.button>
            <x-ui.button variant="danger"
                         wire:click="eliminar({{ $confirmarEliminarId ?? 0 }})"
                         icon="heroicon-o-trash">
                Eliminar
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
