<div class="space-y-4">
    <div>
        <h2 class="text-xl font-semibold text-slate-900">API</h2>
        <p class="text-sm text-slate-500">Gestiona los tokens de acceso para aplicaciones externas.</p>
        @can('api_tokens.gestionar')
        <div class="mt-3">
            <button type="button" wire:click="nuevo"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition-colors">
                <x-heroicon-o-plus class="size-4" />
                Nuevo
            </button>
        </div>
        @endcan
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">Aplicación</th>
                    <th class="px-5 py-3 text-left">Descripción</th>
                    <th class="px-5 py-3 text-left">Token</th>
                    <th class="px-5 py-3 text-left">Estado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($this->apis as $api)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $api->nombre }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $api->descripcion ?? '—' }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $api->tokenMascarado() }}</td>
                        <td class="px-5 py-3">
                            @if ($api->activo)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Activo</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" wire:click="ver({{ $api->id }})"
                                        class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" title="Ver">
                                    <x-heroicon-o-eye class="size-4" />
                                </button>
                                @can('api_tokens.gestionar')
                                <button type="button" wire:click="editar({{ $api->id }})"
                                        class="rounded p-1.5 text-blue-400 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Editar">
                                    <x-heroicon-o-pencil-square class="size-4" />
                                </button>
                                <button type="button" wire:click="confirmarEliminar({{ $api->id }})"
                                        class="rounded p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar">
                                    <x-heroicon-o-trash class="size-4" />
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">
                            No hay ninguna API configurada todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ─── Modal Nuevo / Editar ──────────────────────────────────────── --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-slate-900">
                        {{ $editingId ? 'Editar API' : 'Nueva API' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                        <x-heroicon-o-x-mark class="size-5" />
                    </button>
                </div>
                <div class="space-y-4 px-6 py-5">
                    {{-- Nombre --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">Nombre de la aplicación <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="nombre" maxlength="100"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                               placeholder="Ej: ERP externo, App cliente…" />
                        @error('nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Token --}}
                    <div x-data="{ show: false }">
                        <label class="mb-1 block text-xs font-medium text-slate-700">Token <span class="text-red-500">*</span></label>
                        <div class="flex items-stretch overflow-hidden rounded-md border border-slate-300 bg-white focus-within:border-primary-500">
                            <input :type="show ? 'text' : 'password'"
                                   wire:model="token"
                                   class="flex-1 min-w-0 border-0 bg-transparent px-3 py-2 font-mono text-sm focus:outline-none focus:ring-0"
                                   placeholder="Introduce o genera el token" />
                            <button type="button" wire:click="generarTokenModal"
                                    class="inline-flex w-11 items-center justify-center self-stretch border-l border-slate-300 bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-900"
                                    title="Generar token">
                                <x-heroicon-o-arrow-path class="size-4" />
                            </button>
                            <button type="button" @click="show = !show"
                                    class="inline-flex w-11 items-center justify-center self-stretch border-l border-slate-300 bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-900"
                                    :title="show ? 'Ocultar token' : 'Mostrar token'">
                                <x-heroicon-o-eye class="size-4" x-show="!show" />
                                <x-heroicon-o-eye-slash class="size-4" x-show="show" />
                            </button>
                        </div>
                        @error('token') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">Descripción</label>
                        <textarea wire:model="descripcion" rows="2" maxlength="500"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                                  placeholder="Para qué se usa esta conexión…"></textarea>
                        @error('descripcion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Activo --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="activo" id="activo-api"
                               class="size-4 rounded border-slate-300 text-primary-600" />
                        <label for="activo-api" class="text-sm text-slate-700">API activa</label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                    <button type="button" wire:click="$set('showModal', false)"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardar" wire:loading.attr="disabled"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors disabled:opacity-60">
                        <span wire:loading.remove wire:target="guardar">Guardar</span>
                        <span wire:loading wire:target="guardar">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── Modal Ver ─────────────────────────────────────────────────── --}}
    @if ($showViewModal && $viewingApi)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-slate-900">{{ $viewingApi->nombre }}</h3>
                    <button type="button" wire:click="$set('showViewModal', false)"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                        <x-heroicon-o-x-mark class="size-5" />
                    </button>
                </div>
                <div class="space-y-4 px-6 py-5">
                    @if ($viewingApi->descripcion)
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Descripción</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $viewingApi->descripcion }}</p>
                        </div>
                    @endif
                    <div x-data="{ show: false }">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Token</p>
                        <div class="mt-1 flex items-center gap-2">
                            <code class="flex-1 break-all rounded-lg bg-slate-100 px-3 py-2 font-mono text-xs text-slate-800"
                                  x-text="show ? '{{ $viewingApi->token }}' : '{{ str_repeat('•', min(strlen($viewingApi->token), 48)) }}'">
                            </code>
                            <button type="button" @click="show = !show"
                                    class="shrink-0 rounded-lg border border-slate-200 p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                                <x-heroicon-o-eye class="size-4" x-show="!show" />
                                <x-heroicon-o-eye-slash class="size-4" x-show="show" />
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Estado</p>
                            <p class="mt-1">
                                @if ($viewingApi->activo)
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Activo</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Inactivo</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Creado por</p>
                            <p class="mt-1 text-slate-700">{{ trim($viewingApi->creador->nombre.' '.$viewingApi->creador->apellidos) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Fecha creación</p>
                            <p class="mt-1 text-slate-700">{{ $viewingApi->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-200 px-6 py-4">
                    <button type="button" wire:click="$set('showViewModal', false)"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── Modal Eliminar ─────────────────────────────────────────────── --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm rounded-2xl bg-white shadow-xl">
                <div class="flex flex-col items-center gap-3 px-6 pt-6 pb-4 text-center">
                    <div class="flex size-12 items-center justify-center rounded-full bg-red-100">
                        <x-heroicon-o-exclamation-triangle class="size-6 text-red-600" />
                    </div>
                    <h3 class="text-base font-semibold text-slate-900">Eliminar API</h3>
                    <p class="text-sm text-slate-500">Esta acción es definitiva y no se puede deshacer. El token dejará de funcionar inmediatamente.</p>
                </div>
                <div class="flex gap-2 border-t border-slate-200 px-6 py-4">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="eliminar" wire:loading.attr="disabled"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors disabled:opacity-60">
                        <span wire:loading.remove wire:target="eliminar">Eliminar</span>
                        <span wire:loading wire:target="eliminar">Eliminando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
