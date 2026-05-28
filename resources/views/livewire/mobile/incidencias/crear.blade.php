<div>
    <form wire:submit="guardar" class="px-4 pb-6 pt-3">

        <div class="space-y-4">

            {{-- Tipo --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Tipo de incidencia <span class="text-red-500">*</span>
                </label>
                <select wire:model="tipo"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">— Selecciona tipo —</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t->value }}">{{ $t->etiqueta() }}</option>
                    @endforeach
                </select>
                @error('tipo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Prioridad --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Prioridad</label>
                <select wire:model="prioridad"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @foreach ($prioridades as $p)
                        <option value="{{ $p->value }}">{{ $p->etiqueta() }}</option>
                    @endforeach
                </select>
                @error('prioridad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Título --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Título breve <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       wire:model="titulo"
                       maxlength="150"
                       placeholder="Describe brevemente el problema…"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500" />
                @error('titulo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Descripción --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Descripción detallada</label>
                <textarea wire:model="descripcion"
                          rows="4"
                          placeholder="Aporta todos los detalles que puedan ayudar a resolver la incidencia…"
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                @error('descripcion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="mt-6 flex gap-3">
            <a href="{{ route('mobile.incidencias.index') }}"
               wire:navigate
               class="flex-1 rounded-lg border border-slate-300 py-2.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancelar
            </a>
            <button type="submit"
                    class="flex-1 rounded-lg bg-primary-700 py-2.5 text-sm font-medium text-white hover:bg-primary-800">
                Reportar
            </button>
        </div>
    </form>

    {{-- Loading --}}
    <div wire:loading.flex wire:target="guardar"
         class="fixed inset-0 z-50 items-center justify-center bg-white/70">
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
            <svg class="size-4 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-slate-600">Enviando…</span>
        </div>
    </div>
</div>
