<div class="px-4 py-3 space-y-3">

    {{-- Cabecera --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-2">
            <p class="font-mono text-sm font-semibold text-slate-900">{{ $borrador->numero_borrador }}</p>
            <span class="inline-flex items-center rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">Borrador</span>
        </div>
        @if ($borrador->parteConvertido)
            <div class="mb-3 flex items-center gap-2 rounded-md bg-green-50 px-3 py-2 text-xs text-green-800">
                <x-heroicon-o-check-circle class="size-4 shrink-0" />
                Convertido al parte
                <a href="{{ route('mobile.partes.ver', $borrador->parteConvertido) }}"
                   wire:navigate
                   class="font-semibold underline">
                    {{ $borrador->parteConvertido->numero }}
                </a>
            </div>
        @endif
        <dl class="space-y-1.5 text-sm">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Fecha</dt>
                <dd class="font-medium text-slate-800">
                    {{ $borrador->fecha->format('d/m/Y') }}
                    <span class="ml-1 text-xs text-slate-500">({{ $borrador->tipo_hora->etiqueta() }})</span>
                </dd>
            </div>
            @if ($borrador->clienteNombre() !== '—')
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Cliente</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $borrador->clienteNombre() }}</dd>
                </div>
            @endif
            @if ($borrador->proyectoNombre() !== '—')
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Proyecto</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $borrador->proyectoNombre() }}</dd>
                </div>
            @endif
            @if ($borrador->conceptoNombre() !== '—')
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Concepto</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $borrador->conceptoNombre() }}</dd>
                </div>
            @endif
        </dl>
        @if ($borrador->observaciones)
            <div class="mt-3 rounded-md bg-slate-50 p-2.5 text-xs text-slate-600">
                {{ $borrador->observaciones }}
            </div>
        @endif
    </div>

    {{-- Personal --}}
    @if ($borrador->lineasPersonal->isNotEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Personal</p>
            <div class="divide-y divide-slate-100">
                @foreach ($borrador->lineasPersonal as $linea)
                    @php
                        $horas      = (float) $linea->horas;
                        $horasExtra = (float) $linea->horas_extra;
                        $hFmt  = rtrim(rtrim(number_format($horas, 2, ',', ''), '0'), ',');
                        $heFmt = rtrim(rtrim(number_format($horasExtra, 2, ',', ''), '0'), ',');
                    @endphp
                    <div class="flex items-center justify-between gap-2 py-1.5 text-sm">
                        <span class="min-w-0 truncate text-slate-700">
                            {{ $linea->trabajadorNombre() }}
                        </span>
                        <span class="shrink-0 font-medium text-slate-900">
                            {{ $hFmt }} h
                            @if ($horasExtra > 0)
                                <span class="text-amber-700">+ {{ $heFmt }} extra</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Materiales --}}
    @if (\App\Support\Modulos::materialesAvanzado() && $borrador->lineasMaterial->isNotEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Materiales</p>
            <div class="divide-y divide-slate-100">
                @foreach ($borrador->lineasMaterial as $linea)
                    <div class="flex items-center justify-between gap-2 py-1.5 text-sm">
                        <span class="min-w-0 truncate text-slate-700">{{ $linea->material->descripcion ?? '—' }}</span>
                        <span class="shrink-0 font-medium text-slate-900">
                            {{ rtrim(rtrim(number_format((float) $linea->cantidad, 2, ',', ''), '0'), ',') }}
                            <span class="text-xs text-slate-500">{{ $linea->material->unidad_medida ?? '' }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
