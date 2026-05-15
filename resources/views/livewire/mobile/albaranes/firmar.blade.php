<div class="px-4 py-3 space-y-4">

    {{-- Resumen del parte --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-2">
            <p class="font-mono text-sm font-semibold text-slate-900">{{ $albaran->numero }}</p>
            <x-ui.badge :tone="$albaran->estado->tono()" dot>{{ $albaran->estado->etiqueta() }}</x-ui.badge>
        </div>
        <dl class="space-y-1.5 text-sm">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Fecha</dt>
                <dd class="font-medium text-slate-800">
                    {{ $albaran->fecha->format('d/m/Y') }}
                    <span class="ml-1 text-xs text-slate-500">({{ $albaran->tipo_hora->etiqueta() }})</span>
                </dd>
            </div>
            @if ($albaran->proyecto)
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Proyecto</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $albaran->proyecto->nombre }}</dd>
                </div>
            @endif
            @if ($albaran->concepto)
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Concepto</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $albaran->concepto->nombre }}</dd>
                </div>
            @endif
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Trabajador</dt>
                <dd class="text-right font-medium text-slate-800">
                    {{ trim($albaran->creador->nombre.' '.$albaran->creador->apellidos) }}
                </dd>
            </div>
            @if ($albaran->responsable)
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Responsable</dt>
                    <dd class="text-right font-medium text-slate-800">
                        {{ trim($albaran->responsable->nombre.' '.$albaran->responsable->apellidos) }}
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- ══════════════ FORMULARIO DE FIRMAS ══════════════ --}}
    @if (! $firmado)

        <div
            x-data="{
                /* ── Trabajador ── */
                ctxT: null, vacioT: true, dibT: false, posT: {x:0,y:0},
                /* ── Responsable ── */
                ctxR: null, vacioR: true, dibR: false, posR: {x:0,y:0},

                init() {
                    const setup = (ctx) => {
                        ctx.strokeStyle = '#1e293b';
                        ctx.lineWidth   = 2.5;
                        ctx.lineCap     = 'round';
                        ctx.lineJoin    = 'round';
                    };
                    this.ctxT = this.$refs.canvasT.getContext('2d');
                    setup(this.ctxT);
                    if (this.$refs.canvasR) {
                        this.ctxR = this.$refs.canvasR.getContext('2d');
                        setup(this.ctxR);
                    }
                },

                getPos(canvas, e) {
                    const rect = canvas.getBoundingClientRect();
                    const src  = e.touches ? e.touches[0] : e;
                    return {
                        x: (src.clientX - rect.left) * (canvas.width  / rect.width),
                        y: (src.clientY - rect.top)  * (canvas.height / rect.height)
                    };
                },

                /* Trabajador */
                iT(e) { this.dibT = true; this.posT = this.getPos(this.$refs.canvasT, e); e.preventDefault(); },
                dT(e) {
                    if (!this.dibT) return;
                    const p = this.getPos(this.$refs.canvasT, e);
                    this.ctxT.beginPath(); this.ctxT.moveTo(this.posT.x, this.posT.y);
                    this.ctxT.lineTo(p.x, p.y); this.ctxT.stroke();
                    this.posT = p; this.vacioT = false; e.preventDefault();
                },
                fT() { this.dibT = false; },
                lT() { this.ctxT.clearRect(0, 0, this.$refs.canvasT.width, this.$refs.canvasT.height); this.vacioT = true; $wire.set('firmaTrabajadorData', ''); },

                /* Responsable */
                iR(e) { if (!this.ctxR) return; this.dibR = true; this.posR = this.getPos(this.$refs.canvasR, e); e.preventDefault(); },
                dR(e) {
                    if (!this.dibR || !this.ctxR) return;
                    const p = this.getPos(this.$refs.canvasR, e);
                    this.ctxR.beginPath(); this.ctxR.moveTo(this.posR.x, this.posR.y);
                    this.ctxR.lineTo(p.x, p.y); this.ctxR.stroke();
                    this.posR = p; this.vacioR = false; e.preventDefault();
                },
                fR() { this.dibR = false; },
                lR() { if (!this.ctxR) return; this.ctxR.clearRect(0, 0, this.$refs.canvasR.width, this.$refs.canvasR.height); this.vacioR = true; $wire.set('firmaResponsableData', ''); },

                /* Envío */
                enviar() {
                    if (this.vacioT) return;
                    $wire.set('firmaTrabajadorData', this.$refs.canvasT.toDataURL('image/png'));
                    if (this.$refs.canvasR && !this.vacioR) {
                        $wire.set('firmaResponsableData', this.$refs.canvasR.toDataURL('image/png'));
                    }
                    $wire.call('firmar');
                }
            }"
            class="space-y-4"
        >
            {{-- Canvas trabajador --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="mb-1 text-sm font-semibold text-slate-800">
                    Firma del trabajador
                </p>
                <p class="mb-3 text-xs text-slate-500">
                    {{ trim($albaran->creador->nombre.' '.$albaran->creador->apellidos) }}
                </p>

                <div class="relative overflow-hidden rounded-lg border-2 border-dashed border-slate-300 bg-slate-50"
                     style="touch-action:none">
                    <canvas x-ref="canvasT" width="600" height="180" class="block w-full"
                            x-on:mousedown="iT" x-on:mousemove="dT" x-on:mouseup="fT" x-on:mouseleave="fT"
                            x-on:touchstart="iT" x-on:touchmove="dT" x-on:touchend="fT"></canvas>
                    <p x-show="vacioT" class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-slate-400 select-none">
                        Firma aquí
                    </p>
                </div>

                @error('firmaTrabajadorData')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <button type="button" x-on:click="lT()"
                        class="mt-2 flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600">
                    <x-heroicon-o-arrow-path class="size-3.5" /> Borrar firma
                </button>
            </div>

            {{-- Canvas responsable (solo si hay responsable asignado) --}}
            @if ($albaran->responsable_id)
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="mb-1 text-sm font-semibold text-slate-800">
                        Firma del responsable
                    </p>
                    <p class="mb-3 text-xs text-slate-500">
                        {{ trim($albaran->responsable->nombre.' '.$albaran->responsable->apellidos) }}
                        <span class="ml-1 text-slate-400">(opcional si no está presente)</span>
                    </p>

                    <div class="relative overflow-hidden rounded-lg border-2 border-dashed border-amber-300 bg-amber-50"
                         style="touch-action:none">
                        <canvas x-ref="canvasR" width="600" height="180" class="block w-full"
                                x-on:mousedown="iR" x-on:mousemove="dR" x-on:mouseup="fR" x-on:mouseleave="fR"
                                x-on:touchstart="iR" x-on:touchmove="dR" x-on:touchend="fR"></canvas>
                        <p x-show="vacioR" class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-amber-500 select-none">
                            Firma aquí
                        </p>
                    </div>

                    <button type="button" x-on:click="lR()"
                            class="mt-2 flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600">
                        <x-heroicon-o-arrow-path class="size-3.5" /> Borrar firma
                    </button>
                </div>
            @endif

            {{-- Botón único al final --}}
            <button
                type="button"
                x-on:click="enviar()"
                x-bind:disabled="vacioT"
                :class="vacioT ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'"
                class="flex w-full items-center justify-center gap-2 rounded-md bg-green-600 px-4 py-3.5 text-sm font-semibold text-white transition-colors"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="firmar">
                    <x-heroicon-o-check class="inline size-4" /> Confirmar y firmar
                </span>
                <span wire:loading wire:target="firmar" class="flex items-center gap-2">
                    <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Guardando…
                </span>
            </button>

        </div>

    {{-- ══════════════ PANTALLA COMPLETADO ══════════════ --}}
    @else

        <div class="flex flex-col items-center gap-4 rounded-lg border border-green-200 bg-green-50 p-6 text-center shadow-sm">
            <div class="flex size-14 items-center justify-center rounded-full bg-green-600 text-white shadow-md">
                <x-heroicon-o-check-badge class="size-8" />
            </div>
            <div>
                <h2 class="text-base font-semibold text-green-800">Parte firmado</h2>
                <p class="mt-1 text-sm text-green-700">
                    El parte <strong class="font-mono">{{ $albaran->numero }}</strong>
                    ha quedado registrado correctamente.
                </p>
            </div>
            <button
                type="button"
                wire:click="irAlListado"
                class="mt-2 flex items-center gap-2 rounded-md bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700"
            >
                <x-heroicon-o-list-bullet class="size-4" />
                Volver a mis albaranes
            </button>
        </div>

    @endif

</div>
