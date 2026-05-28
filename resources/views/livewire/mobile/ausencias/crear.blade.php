<div>
    <form wire:submit="guardar" class="px-4 pb-6 pt-3">

        {{-- Banner solo lectura --}}
        @if ($soloLectura && $ausencia)
            @php
                $estadoTono = $ausencia->estado->tono();
                $bannerClases = match($estadoTono) {
                    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                    'danger'  => 'bg-red-50 border-red-200 text-red-800',
                    default   => 'bg-slate-50 border-slate-200 text-slate-700',
                };
                $iconoClases = match($estadoTono) {
                    'success' => 'text-emerald-500',
                    'danger'  => 'text-red-500',
                    default   => 'text-slate-400',
                };
            @endphp
            <div class="mb-4 flex items-start gap-2 rounded-lg border px-3 py-2.5 {{ $bannerClases }}">
                @if ($estadoTono === 'success')
                    <x-heroicon-o-check-circle class="mt-0.5 size-4 shrink-0 {{ $iconoClases }}" />
                @else
                    <x-heroicon-o-x-circle class="mt-0.5 size-4 shrink-0 {{ $iconoClases }}" />
                @endif
                <div class="text-xs leading-snug">
                    <span class="font-semibold">{{ $ausencia->estado->etiqueta() }}</span>
                    — Esta solicitud ya no se puede modificar.
                    @if ($ausencia->observaciones && $ausencia->estado === \App\Enums\EstadoAusencia::RECHAZADA)
                        <div class="mt-1">
                            <span class="font-medium">Motivo de rechazo:</span> {{ $ausencia->observaciones }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="space-y-4">

            {{-- Tipo --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Tipo de ausencia <span class="text-red-500">*</span>
                </label>
                <select wire:model="tipo"
                        @if ($soloLectura) disabled @endif
                        @class([
                            'w-full rounded-md border px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500',
                            'border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed' => $soloLectura,
                            'border-slate-300 bg-white' => ! $soloLectura,
                        ])>
                    <option value="">— Selecciona tipo —</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t->value }}">{{ $t->etiqueta() }}</option>
                    @endforeach
                </select>
                @error('tipo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fechas --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Fecha inicio <span class="text-red-500">*</span>
                    </label>
                    @if ($soloLectura)
                        <input type="text" disabled
                               value="{{ $ausencia?->fecha_inicio->format('d/m/Y') }}"
                               class="w-full cursor-not-allowed rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500" />
                    @else
                        <x-ui.date-input wireModel="fechaInicio" :value="$fechaInicio" :live="true" placeholder="dd/mm/aaaa" />
                    @endif
                    @error('fechaInicio')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Fecha fin <span class="text-red-500">*</span>
                    </label>
                    @if ($soloLectura)
                        <input type="text" disabled
                               value="{{ $ausencia?->fecha_fin->format('d/m/Y') }}"
                               class="w-full cursor-not-allowed rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500" />
                    @else
                        <x-ui.date-input wireModel="fechaFin" :value="$fechaFin" :live="true" placeholder="dd/mm/aaaa" />
                    @endif
                    @error('fechaFin')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Motivo --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Motivo (opcional)</label>
                <textarea wire:model="motivo"
                          rows="3"
                          @if ($soloLectura) disabled @endif
                          placeholder="Describe brevemente el motivo de la ausencia…"
                          @class([
                              'w-full rounded-md border px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500',
                              'border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed resize-none' => $soloLectura,
                              'border-slate-300' => ! $soloLectura,
                          ])></textarea>
                @error('motivo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        @if (! $soloLectura)
            <div class="mt-6 flex gap-3">
                <a href="{{ route('mobile.ausencias.index') }}"
                   wire:navigate
                   class="flex-1 rounded-lg border border-slate-300 py-2.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex-1 rounded-lg bg-primary-700 py-2.5 text-sm font-medium text-white hover:bg-primary-800">
                    {{ $esEditar ? 'Guardar' : 'Solicitar' }}
                </button>
            </div>
        @endif
    </form>

    {{-- Loading --}}
    <div wire:loading.flex wire:target="guardar"
         class="fixed inset-0 z-50 items-center justify-center bg-white/70">
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
            <svg class="size-4 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-slate-600">Guardando…</span>
        </div>
    </div>
</div>
