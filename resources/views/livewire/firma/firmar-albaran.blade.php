<div>
    {{-- ── Error / Token inválido ── --}}
    @if ($error !== '')
        <div class="rounded-xl border border-red-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-red-50">
                <x-heroicon-o-x-circle class="size-8 text-red-500" />
            </div>
            <h2 class="mb-2 text-lg font-semibold text-slate-900">Enlace no válido</h2>
            <p class="text-sm text-slate-500">{{ $error }}</p>
        </div>

    {{-- ── Firma completada ── --}}
    @elseif ($firmado)
        <div class="rounded-xl border border-green-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-green-50">
                <x-heroicon-o-check-circle class="size-8 text-green-500" />
            </div>
            <h2 class="mb-2 text-lg font-semibold text-slate-900">Documento firmado</h2>
            <p class="text-sm text-slate-500">Tu firma ha sido registrada correctamente. Puedes cerrar esta página.</p>
        </div>

    {{-- ── Formulario de firma ── --}}
    @else
        @php
            $empresa  = \App\Models\Empresa::actual();
            $logoUrl  = $empresa->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($empresa->logo_path) : null;
            $color    = $empresa->color_primario ?? '#334155';
            $totalLineas = $firmable->lineasPersonal->count();
        @endphp

        {{-- ══ DOCUMENTO (misma estructura que el email) ══ --}}
        <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            {{-- Cabecera: logo + datos empresa / Nº albarán --}}
            <div class="border-b-2 p-4 sm:p-5" style="border-color: {{ $color }}">
                <div class="flex items-start justify-between gap-4">

                    {{-- Izquierda: logo o nombre --}}
                    <div class="min-w-0">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $empresa->nombre }}"
                                 class="mb-2 block max-h-16 max-w-[180px]">
                        @else
                            <div class="mb-2 text-xl font-extrabold" style="color: {{ $color }}">
                                {{ $empresa->nombre_comercial ?: $empresa->nombre }}
                            </div>
                        @endif
                        <div class="text-[11px] leading-relaxed text-slate-500">
                            @if ($empresa->nombre_comercial && $empresa->nombre)
                                <div class="font-semibold text-slate-600">{{ $empresa->nombre }}</div>
                            @endif
                            @if ($empresa->direccion)
                                <div>{{ $empresa->direccion }}</div>
                            @endif
                            @if ($empresa->codigo_postal || $empresa->poblacion)
                                <div>{{ trim($empresa->codigo_postal . ' ' . $empresa->poblacion) }}{{ $empresa->provincia ? ' (' . $empresa->provincia . ')' : '' }}</div>
                            @endif
                            @if ($empresa->telefono)
                                <div>Tlf. {{ $empresa->telefono }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Derecha: número + fecha + tipo jornada --}}
                    <table class="shrink-0 rounded border border-slate-200 text-xs">
                        <tr>
                            <td class="px-2 py-1.5 text-slate-500 whitespace-nowrap">Nº Documento</td>
                            <td class="pl-3 pr-2 py-1.5 font-bold text-slate-900 whitespace-nowrap">{{ $firmable->numero }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-2 py-1.5 text-slate-500">Fecha</td>
                            <td class="pl-3 pr-2 py-1.5 font-semibold text-slate-800">{{ $firmable->fecha->format('d/m/Y') }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-2 py-1.5 text-slate-500 whitespace-nowrap">Tipo jornada</td>
                            <td class="pl-3 pr-2 py-1.5 text-slate-700">{{ $firmable->tipo_hora->etiqueta() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Barra cliente / proyecto / concepto --}}
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-600">
                <strong>Cliente:</strong> {{ $firmable->cliente?->nombre ?? '—' }}
                @if ($firmable->proyecto)
                    &nbsp;·&nbsp; <strong>Proyecto:</strong> {{ $firmable->proyecto->nombre }}
                @endif
                @if ($firmable->concepto)
                    &nbsp;·&nbsp; <strong>Concepto:</strong> {{ $firmable->concepto->nombre }}
                @endif
            </div>

            {{-- Tabla trabajadores --}}
            @if ($firmable->lineasPersonal->isNotEmpty())
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background: {{ $color }}">
                            <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-white">Trabajo realizado</th>
                            <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-white">Nombre del trabajador</th>
                            <th class="px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wide text-white whitespace-nowrap">Horas<br>normales</th>
                            <th class="px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wide text-white whitespace-nowrap">Horas<br>extras</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($firmable->lineasPersonal as $i => $linea)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} border-t border-slate-100">
                                @if ($i === 0)
                                    <td rowspan="{{ $totalLineas }}" class="px-3 py-2.5 align-middle text-slate-600 border-r border-slate-100">
                                        {{ $firmable->concepto?->nombre ?? '—' }}
                                    </td>
                                @endif
                                <td class="px-3 py-2.5 font-medium text-slate-800">
                                    {{ trim(($linea->trabajador->nombre ?? '') . ' ' . ($linea->trabajador->apellidos ?? '')) ?: '—' }}
                                </td>
                                <td class="px-3 py-2.5 text-center font-semibold text-slate-800">
                                    {{ number_format((float) $linea->horas, 2) }}
                                </td>
                                <td class="px-3 py-2.5 text-center text-slate-500">
                                    {{ number_format((float) $linea->horas_extra, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Materiales --}}
            @if ($firmable->lineasMaterial->isNotEmpty())
                <table class="w-full border-t border-slate-200 text-sm">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wide text-slate-500">Material</th>
                            <th class="px-3 py-2 text-right text-[11px] font-bold uppercase tracking-wide text-slate-500">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($firmable->lineasMaterial as $i => $linea)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} border-t border-slate-100">
                                <td class="px-3 py-2.5 font-medium text-slate-800">{{ $linea->material->descripcion ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-700">{{ number_format((float) $linea->cantidad, 2) }} {{ $linea->material->unidad_medida ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Observaciones --}}
            @if ($firmable->observaciones)
                <div class="border-t border-slate-200 px-4 py-3 text-xs text-slate-500">
                    <strong>Observaciones:</strong> {{ $firmable->observaciones }}
                </div>
            @endif
        </div>

        {{-- ══ CANVAS DE FIRMA ══ --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
             x-data="{
                 vacio: true,
                 init() {
                     const canvas = this.$refs.canvas;
                     const ctx = canvas.getContext('2d');
                     let drawing = false;
                     let lx = 0, ly = 0;

                     const pos = (e) => {
                         const r = canvas.getBoundingClientRect();
                         const src = e.touches ? e.touches[0] : e;
                         const scaleX = canvas.width / r.width;
                         const scaleY = canvas.height / r.height;
                         return {
                             x: (src.clientX - r.left) * scaleX,
                             y: (src.clientY - r.top) * scaleY
                         };
                     };

                     const start = (e) => { e.preventDefault(); drawing = true; const p = pos(e); lx = p.x; ly = p.y; };
                     const move  = (e) => {
                         if (!drawing) return; e.preventDefault();
                         const p = pos(e);
                         ctx.beginPath(); ctx.moveTo(lx, ly); ctx.lineTo(p.x, p.y);
                         ctx.strokeStyle = '#1e293b'; ctx.lineWidth = 2.5; ctx.lineCap = 'round'; ctx.stroke();
                         lx = p.x; ly = p.y;
                         this.vacio = false;
                     };
                     const stop = () => { drawing = false; };

                     canvas.addEventListener('mousedown', start);
                     canvas.addEventListener('mousemove', move);
                     canvas.addEventListener('mouseup', stop);
                     canvas.addEventListener('mouseleave', stop);
                     canvas.addEventListener('touchstart', start, { passive: false });
                     canvas.addEventListener('touchmove', move, { passive: false });
                     canvas.addEventListener('touchend', stop);
                 },
                 limpiar() {
                     const canvas = this.$refs.canvas;
                     canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                     this.vacio = true;
                 },
                 async enviar() {
                     if (this.vacio) return;
                     const data = this.$refs.canvas.toDataURL('image/png');
                     await $wire.set('firmaData', data);
                     $wire.firmar();
                 }
             }">

            <p class="mb-1 text-sm text-slate-600">
                Se solicita tu firma como <strong>{{ $tokenFirma->tipo_firmante->etiqueta() }}</strong>@if ($tokenFirma->nombre_destino), {{ $tokenFirma->nombre_destino }}@endif.
            </p>
            <p class="mb-3 text-xs text-slate-400">Dibuja tu firma dentro del recuadro con el dedo o el ratón.</p>

            <div class="rounded-lg border-2 border-dashed border-slate-300 bg-slate-50">
                <canvas
                    x-ref="canvas"
                    width="560"
                    height="180"
                    class="block w-full touch-none rounded-lg"
                    style="cursor: crosshair;"
                ></canvas>
            </div>

            @error('firmaData')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-4 flex items-center justify-between gap-3">
                <button type="button"
                        @click="limpiar()"
                        class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Limpiar
                </button>

                <button type="button"
                        @click="enviar()"
                        :disabled="vacio"
                        wire:loading.attr="disabled"
                        wire:target="firmar"
                        :class="vacio ? 'opacity-40 cursor-not-allowed' : 'hover:opacity-90'"
                        class="inline-flex items-center gap-2 rounded-md px-6 py-2 text-sm font-semibold text-white transition-opacity"
                        style="background-color: {{ \App\Support\Branding::colorPrimario() }}">
                    <svg wire:loading wire:target="firmar" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <span wire:loading.remove wire:target="firmar">Firmar documento</span>
                    <span wire:loading wire:target="firmar">Guardando…</span>
                </button>
            </div>

            <p class="mt-4 text-center text-xs text-slate-400">
                Al firmar confirmas que has revisado y aceptas el contenido del documento.
                Enlace válido hasta el {{ $tokenFirma->caduca_at->format('d/m/Y') }}.
            </p>
        </div>
    @endif
</div>
