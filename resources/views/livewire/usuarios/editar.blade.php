<div class="space-y-4" x-data="{ tab: 'usuario' }">
    <x-ui.page-header :title="$titulo" :id-badge="$usuario?->id" subtitle="Datos del usuario y relaciones vinculadas.">
        @if ($usuario)
            @php
                $nombreCompleto = trim($usuario->apellidos.' '.$usuario->nombre);
            @endphp
            <x-slot:actions>
                {{-- Info contextual: usuario + nombre completo, sin marco. --}}
                <div class="text-right">
                    <div class="text-xl font-semibold text-slate-900">{{ $usuario->username }}</div>
                    @if ($nombreCompleto !== '')
                        <div class="text-sm text-slate-500">{{ $nombreCompleto }}</div>
                    @endif
                </div>
            </x-slot:actions>
        @endif

        <x-slot:actionsLeft>
            <x-ui.button as="a" href="{{ route('usuarios.index') }}" wire:navigate variant="neutral" icon="heroicon-o-list-bullet">
                Todos
            </x-ui.button>
            @if ($usuario)
                @can('create', App\Models\User::class)
                    <x-ui.button as="a" href="{{ route('usuarios.crear') }}" wire:navigate variant="success" icon="heroicon-o-plus">
                        Nuevo
                    </x-ui.button>
                @endcan
                @can('usuarios.eliminar')
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
            <x-ui.button variant="info" type="submit" form="form-usuario" wire:loading.attr="disabled" wire:target="guardar">
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

    @php $modoCrear = $usuario === null; @endphp

    <div>
        {{-- Tabs nav --}}
        <div class="flex items-end border-b border-slate-200 px-2 pt-1.5">
            <button type="button"
                    @click="tab = 'usuario'"
                    :class="tab === 'usuario'
                        ? '-mb-px border border-slate-200 border-b-white bg-white rounded-t-lg text-primary-700 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm transition-colors">
                Usuario
                @if ($usuario)
                    <span class="font-mono text-xs font-normal text-slate-400">#{{ $usuario->id }}</span>
                @endif
            </button>

            @php
                // Pestañas dinámicas en orden: Usuario → Tarifas → Albaranes → Proyectos.
                // "Tarifas" solo si:
                //   - el usuario ya existe (no estamos en alta),
                //   - el rol seleccionado es interno (no externo),
                //   - el actor tiene `usuarios.gestionar_tarifas`.
                $tabs = [];
                if ($usuario && ! $this->rolEsExterno && auth()->user()?->can('usuarios.gestionar_tarifas')) {
                    $tabs[] = ['key' => 'tarifas', 'label' => 'Tarifas', 'count' => null];
                }
                $tabs[] = ['key' => 'albaranes', 'label' => 'Albaranes', 'count' => $usuario ? $this->albaranesDelUsuario->count() : null];
                $tabs[] = ['key' => 'proyectos', 'label' => 'Proyectos', 'count' => $usuario ? $this->proyectosDelUsuario->count() : null];
            @endphp
            @foreach ($tabs as $t)
                @if ($modoCrear)
                    <span class="flex cursor-not-allowed items-center gap-1.5 whitespace-nowrap px-5 py-3 text-sm text-slate-300"
                          title="Guarda primero el usuario para acceder a esta sección">
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

        {{-- ═══ Tab: Usuario ═══ --}}
        <form wire:submit="guardar" id="form-usuario" autocomplete="off">
            <div x-show="tab === 'usuario'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">

                {{-- Acceso y rol --}}
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Acceso y rol</h3>
                <div class="mb-6 grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Usuario" required :error="$errors->first('form.username')">
                        <x-ui.input wire:model.blur="form.username" autofocus autocomplete="off" />
                    </x-ui.field>

                    <x-ui.field label="Contraseña"
                                :required="$form->id === null"
                                :error="$errors->first('form.password')">
                        <div class="space-y-2">
                            <div class="flex items-stretch overflow-hidden rounded-md border border-slate-300 bg-white focus-within:border-primary-500">
                                <div class="flex-1">
                                    <x-ui.input
                                        wire:key="password-{{ $passwordRenderKey }}"
                                        :type="$mostrarPassword ? 'text' : 'password'"
                                        wire:model.blur="form.password"
                                        autocomplete="new-password"
                                        class="rounded-none border-0 bg-transparent focus:border-0" />
                                </div>
                                <button type="button"
                                        wire:click.prevent="generarPasswordSegura"
                                        class="inline-flex w-8 items-center justify-center self-stretch border-l border-slate-300 bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-900"
                                        title="Generar contraseña segura"
                                        aria-label="Generar contraseña segura">
                                    <x-heroicon-o-arrow-path class="size-3.5" />
                                </button>
                                <button type="button"
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
                        <x-ui.select wire:model.live="form.rol">
                            @foreach ($this->rolesDisponibles as $rol)
                                <option value="{{ $rol->name }}">{{ $rol->nombreVisible() }} (nivel {{ $rol->nivel }})</option>
                            @endforeach
                        </x-ui.select>
                        <p class="mt-1 text-xs text-slate-400">Acceso del rol: {{ ucfirst($this->accesoRolSeleccionado) }}</p>
                    </x-ui.field>

                    {{-- Tipo de usuario: derivado del rol, solo informativo --}}
                    <x-ui.field label="Tipo usuario">
                        <x-ui.input
                            :value="$this->rolEsExterno ? 'Externo' : 'Interno'"
                            disabled
                            class="bg-slate-50 text-slate-600"
                        />
                        <p class="mt-1 text-xs text-slate-400">Se establece automáticamente según el rol.</p>
                    </x-ui.field>

                    {{-- Cliente único (solo para roles externos, p. ej. Responsable) --}}
                    @if ($this->rolEsExterno)
                        <x-ui.field label="Cliente" required :error="$errors->first('form.cliente_id')">
                            <x-ui.searchable-select
                                wire:key="cliente-externo-{{ $form->rol }}-{{ $form->cliente_id ?? 'null' }}"
                                wire-model="form.cliente_id"
                                :value="$form->cliente_id"
                                :options="$this->empresasDisponibles->map(fn($e) => ['value' => $e->id, 'label' => $e->codigo_cliente.' · '.$e->nombre])->all()"
                                placeholder="— Selecciona cliente —"
                            />
                        </x-ui.field>
                    @endif
                </div>

                {{-- Clientes gestionados (rol con scoping: Jefe de equipo) --}}
                @if ($this->rolTieneScoping)
                    <div class="mb-6 mt-2 rounded-lg border border-slate-200 bg-slate-50/40 p-4">
                        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-slate-800">Clientes gestionados</h3>
                                <p class="text-xs text-slate-500">
                                    Los clientes cuyos borradores, albaranes y proyectos podrá ver y gestionar.
                                </p>
                            </div>
                            <label class="inline-flex shrink-0 cursor-pointer items-center gap-2">
                                <input type="checkbox"
                                       wire:model.live="form.gestionaTodosClientes"
                                       class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
                                <span class="text-sm font-medium text-slate-700">
                                    Asignar todos los clientes
                                    <span class="text-xs font-normal text-slate-400">(incluido los futuros)</span>
                                </span>
                            </label>
                        </div>

                        @if ($form->gestionaTodosClientes)
                            <div class="flex items-start gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                                <x-heroicon-o-check-circle class="mt-0.5 size-4 shrink-0" />
                                <span>
                                    Verá los datos de <strong>todos los clientes</strong>, incluidos los que se den de alta en el futuro.
                                </span>
                            </div>
                        @else
                            @php
                                $idsSeleccionados = array_map('intval', $form->clientesGestionados);
                                $disponibles      = $this->empresasDisponibles->whereNotIn('id', $idsSeleccionados)->values();
                                $seleccionados    = $this->empresasDisponibles->whereIn('id', $idsSeleccionados)->values();
                            @endphp

                            <div class="space-y-2">
                                <x-ui.field :error="$errors->first('form.clientesGestionados')">
                                    <x-ui.searchable-select
                                        wire:key="cliente-add-{{ $selectAddClienteKey }}"
                                        wire-model="clienteAAgregar"
                                        :value="null"
                                        :options="$disponibles->map(fn($e) => ['value' => $e->id, 'label' => $e->codigo_cliente.' · '.$e->nombre])->all()"
                                        placeholder="Buscar y añadir cliente…"
                                    />
                                </x-ui.field>

                                @if ($seleccionados->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach ($seleccionados as $sel)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 bg-white py-1 pl-2.5 pr-1 text-xs">
                                                <span class="font-mono text-slate-500">{{ $sel->codigo_cliente }}</span>
                                                <span class="text-slate-700">· {{ $sel->nombre }}</span>
                                                <button type="button"
                                                        wire:click="quitarClienteGestionado({{ $sel->id }})"
                                                        class="ml-1 flex size-5 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-red-100 hover:text-red-600">
                                                    <x-heroicon-m-x-mark class="size-3" />
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="pt-1 text-xs text-amber-600">
                                        Sin clientes asignados: el usuario no verá ningún dato.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Datos personales --}}
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Datos personales</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Nombre" required :error="$errors->first('form.nombre')">
                        <x-ui.input wire:model.live.debounce.500ms="form.nombre" />
                    </x-ui.field>

                    <x-ui.field label="Apellidos" :error="$errors->first('form.apellidos')">
                        <x-ui.input wire:model.live.debounce.500ms="form.apellidos" />
                    </x-ui.field>

                    <x-ui.field label="Email" :error="$errors->first('form.email')">
                        <x-ui.input type="email" wire:model="form.email" />
                    </x-ui.field>

                    <x-ui.field label="Teléfono" :error="$errors->first('form.telefono')">
                        <x-ui.input wire:model="form.telefono" />
                    </x-ui.field>

                    <x-ui.field label="DNI" :error="$errors->first('form.dni')">
                        <x-ui.input wire:model="form.dni" />
                    </x-ui.field>

                    <x-ui.field label="Nº empleado" :error="$errors->first('form.numero_empleado')"
                                hint="Información extra (HR). Texto libre, no único.">
                        <x-ui.input wire:model="form.numero_empleado" />
                    </x-ui.field>
                </div>

                {{-- Tarifas (€/hora) — viven en su propia pestaña "Tarifas",
                     no en esta. Solo aparece si el usuario es interno (el
                     mismo dato se puede editar también desde
                     /tarifas/trabajadores). --}}

                {{-- Usuario activo: standalone al final, después de Tasas. --}}
                <div class="mt-6 border-t border-slate-100 pt-4">
                    <x-ui.checkbox wire:model="form.activo" label="Usuario activo" />
                </div>
            </div>
        </form>

        {{-- ═══ Tab: Proyectos ═══ --}}
        <div x-show="tab === 'proyectos'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Proyectos vinculados</span>
                <p class="mt-0.5 text-xs text-slate-400">Proyectos en los que participa como trabajador o responsable principal</p>
            </div>
            @if ($this->proyectosDelUsuario->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    No hay proyectos vinculados a este usuario.
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
                                <th class="w-16 px-6 py-2.5 text-right">Ir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($this->proyectosDelUsuario as $proyecto)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-medium text-slate-800">{{ $proyecto->nombre }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $proyecto->codigo ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $proyecto->cliente?->nombre ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-500">{{ $proyecto->tipoProyecto?->nombre ?? '—' }}</td>
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

        {{-- ═══ Tab: Albaranes ═══ --}}
        <div x-show="tab === 'albaranes'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4">
                <span class="text-sm font-semibold text-slate-900">Albaranes vinculados</span>
                <p class="mt-0.5 text-xs text-slate-400">Albaranes creados por este usuario o en los que aparece como trabajador</p>
            </div>
            @if ($this->albaranesDelUsuario->isEmpty())
                <div class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-400">
                    No hay albaranes vinculados a este usuario.
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
                            @foreach ($this->albaranesDelUsuario as $albaran)
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

        {{-- ═══ Tab: Tarifas ═══ ───────────────────────────────────────
             Solo se renderiza si el usuario ya existe, el rol es interno
             y el actor tiene `usuarios.gestionar_tarifas`. Vinculado a
             form.tasa_* (las mismas columnas users.tasa_* que edita la
             pantalla /tarifas/trabajadores). Al pulsar Guardar del form
             principal, se persisten junto al resto de campos.
        --}}
        @can('usuarios.gestionar_tarifas')
            @if ($usuario && ! $this->rolEsExterno)
                <div x-show="tab === 'tarifas'" class="rounded-b-xl border border-t-0 border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tarifas (€/hora)</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Tarifas que paga la empresa a este trabajador por cada tipo de hora. También se pueden editar desde el módulo <a href="{{ route('tarifas.trabajadores') }}" class="text-primary-600 underline" wire:navigate>Tarifas → Trabajadores</a>.</p>
                    </div>

                    {{-- Normales: un único campo "Laboral" que escribe en las 4 tasas normales --}}
                    <div class="mb-1 text-[10px] font-medium uppercase tracking-wider text-slate-400">Normales</div>
                    <div class="grid gap-4 md:grid-cols-4">
                        <x-ui.field label="Laboral" :error="$errors->first('form.tasa_hora')">
                            <x-ui.input type="number" step="0.001" min="0"
                                        wire:model="form.tasa_hora" form="form-usuario"
                                        placeholder="0" />
                            <p class="mt-1 text-[10px] text-slate-400">Labor · Lab Noche · Fest · Fest Noct</p>
                        </x-ui.field>
                    </div>

                    {{-- Extras --}}
                    <div class="mb-1 mt-5 text-[10px] font-medium uppercase tracking-wider text-slate-400">Extras</div>
                    <div class="grid gap-4 md:grid-cols-4">
                        <x-ui.field label="Ex Lab" :error="$errors->first('form.tasa_extra')">
                            <x-ui.input type="number" step="0.001" min="0"
                                        wire:model="form.tasa_extra" form="form-usuario"
                                        placeholder="0" />
                        </x-ui.field>
                        <x-ui.field label="Ex Lab Noc" :error="$errors->first('form.tasa_ex_lab_noc')">
                            <x-ui.input type="number" step="0.001" min="0"
                                        wire:model="form.tasa_ex_lab_noc" form="form-usuario"
                                        placeholder="0" />
                        </x-ui.field>
                        <x-ui.field label="Ex Fes" :error="$errors->first('form.tasa_ex_fes')">
                            <x-ui.input type="number" step="0.001" min="0"
                                        wire:model="form.tasa_ex_fes" form="form-usuario"
                                        placeholder="0" />
                        </x-ui.field>
                        <x-ui.field label="Ex Fes Noct" :error="$errors->first('form.tasa_ex_fes_noct')">
                            <x-ui.input type="number" step="0.001" min="0"
                                        wire:model="form.tasa_ex_fes_noct" form="form-usuario"
                                        placeholder="0" />
                        </x-ui.field>
                    </div>

                    {{-- Plus --}}
                    <div class="mb-1 mt-5 text-[10px] font-medium uppercase tracking-wider text-slate-400">Plus</div>
                    <div class="grid gap-4 md:grid-cols-4">
                        <x-ui.field label="Plus Retén" :error="$errors->first('form.tasa_plus_reten')">
                            <x-ui.input type="number" step="0.001" min="0"
                                        wire:model="form.tasa_plus_reten" form="form-usuario"
                                        placeholder="0" />
                        </x-ui.field>
                    </div>
                </div>
            @endif
        @endcan
    </div>

    {{-- Modal duplicados --}}
    <x-ui.modal :show="$modalDuplicadosAbierto" title="Posibles duplicados detectados" close-action="cancelarDuplicados" size="md">
        <div class="space-y-3">
            <p class="text-sm text-slate-600">Se encontraron usuarios con datos similares:</p>
            @foreach ($duplicadosEncontrados as $dup)
                <div class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <div class="text-sm">
                        <span class="font-medium text-slate-700">{{ $dup['usuario_nombre'] }}</span>
                        <span class="ml-2 text-slate-500">— mismo {{ strtoupper($dup['campo']) }}: <code class="font-mono text-xs">{{ $dup['valor'] }}</code></span>
                        @if ($dup['eliminado'])
                            <span class="ml-1 text-xs text-slate-400">(en papelera)</span>
                        @endif
                    </div>
                    <x-ui.button size="xs" variant="neutral" wire:click="usarExistente({{ $dup['usuario_id'] }})">
                        Editar ese
                    </x-ui.button>
                </div>
            @endforeach
        </div>
        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cancelarDuplicados">Cancelar</x-ui.button>
            <x-ui.button variant="warning" wire:click="confirmarCrearAunqueDuplicado" icon="heroicon-o-plus">
                Crear nuevo igualmente
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal confirmar eliminación --}}
    <x-ui.modal :show="$confirmarEliminarId !== null" title="Eliminar usuario" close-action="cancelarEliminar" size="sm">
        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">¿Eliminar el usuario <strong>{{ $usuario?->username }}</strong>?</p>
                <p class="mt-1 text-sm text-slate-500">Esta acción no se puede deshacer.</p>
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

    {{-- Modal eliminación bloqueada --}}
    <x-ui.modal :show="$bloqueadoEliminarMensaje !== null" title="No se puede eliminar" close-action="cerrarBloqueo" size="sm">
        <div class="flex gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                <x-heroicon-o-exclamation-triangle class="size-5" />
            </div>
            <div>
                <p class="text-sm text-slate-700">{{ $bloqueadoEliminarMensaje }}</p>
                <p class="mt-2 text-xs text-slate-500">Elimina o reasigna primero esos elementos.</p>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="neutral" wire:click="cerrarBloqueo">Entendido</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
