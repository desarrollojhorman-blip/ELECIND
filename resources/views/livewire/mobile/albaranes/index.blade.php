<div>
    {{-- Filtros pills --}}
    <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-3 py-2">
        <div class="flex gap-1.5 overflow-x-auto">
            @php
                $filtros = [
                    'todos' => 'Todos',
                    'borrador' => 'Borradores',
                    'pendiente_firma' => 'Pdte. firma',
                    'firmado' => 'Firmados',
                    'facturado' => 'Facturados',
                ];
            @endphp

            @foreach ($filtros as $valor => $label)
                <button type="button"
                        wire:click="setFiltro('{{ $valor }}')"
                        @class([
                            'shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                            'bg-primary-700 text-white' => $filtroEstado === $valor,
                            'bg-slate-100 text-slate-700 hover:bg-slate-200' => $filtroEstado !== $valor,
                        ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Listado --}}
    <div class="px-4 py-3">
        @forelse ($albaranes as $albaran)
            @php
                $estadoTone = $albaran->estado->tono();
            @endphp
            <a href="{{ route('mobile.albaranes.firmar', ['albaran' => $albaran->getKey()]) }}"
               wire:key="alb-{{ $albaran->id }}"
               class="mb-2 block rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition-colors hover:border-primary-300 hover:bg-primary-50/30 active:scale-[0.99] active:transition-transform">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-mono text-sm font-semibold text-slate-900">
                            {{ $albaran->numero }}
                        </p>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ $albaran->cliente?->nombre ?? '—' }}
                            @if ($albaran->proyecto)
                                · {{ $albaran->proyecto->nombre }}
                            @endif
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <x-ui.badge :tone="$estadoTone" dot>{{ $albaran->estado->etiqueta() }}</x-ui.badge>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ \Illuminate\Support\Carbon::parse($albaran->fecha)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </a>
        @empty
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="mb-3 inline-flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-heroicon-o-folder-open class="size-7" />
                </div>
                <p class="text-sm text-slate-600">No tienes albaranes en este filtro.</p>
                <a href="{{ route('mobile.albaranes.nuevo') }}"
                   class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    <x-heroicon-m-plus class="size-4" />
                    Crear nuevo parte
                </a>
            </div>
        @endforelse

        <div class="mt-3">
            {{ $albaranes->links() }}
        </div>
    </div>
</div>
