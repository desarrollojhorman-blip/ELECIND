@props([
    'wireModel' => null,        // nombre de la propiedad Livewire (sin "wire:model")
    'live' => false,             // true → wire:model.live (sincroniza en cada cambio)
    'enableTime' => false,       // muestra selector de hora
    'mode' => 'single',          // 'single' | 'multiple' | 'range'
    'placeholder' => '',
    'name' => null,              // por si se usa fuera de Livewire (form clásico)
    'value' => null,             // valor inicial (se pasa a flatpickr como defaultDate)
    'minDate' => null,           // YYYY-MM-DD o palabras tipo 'today'
    'maxDate' => null,
])

@php
    $inputClasses = 'w-full appearance-none rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-none transition-colors focus:border-primary-500 focus:outline-none focus:ring-0';

    $config = array_filter([
        'enableTime'    => (bool) $enableTime,
        'time_24hr'     => true,
        'dateFormat'    => $enableTime ? 'Y-m-d H:i' : 'Y-m-d',
        'altInput'      => true,
        'altFormat'     => $enableTime ? 'd/m/Y H:i' : 'd/m/Y',
        'altInputClass' => $inputClasses,
        'allowInput'    => true,
        'mode'          => $mode,
        'defaultDate'   => $value,
        'minDate'       => $minDate,
        'maxDate'       => $maxDate,
    ], fn ($v) => $v !== null && $v !== false);
@endphp

{{-- wire:ignore: Livewire NO debe morphear este árbol porque flatpickr
     inyecta DOM y se rompería al re-render. El input oculto sigue
     llevando wire:model (la sincronización con el servidor funciona).

     Si flatpickr no está disponible (carga tardía o build roto), caemos
     a un <input type="date"> nativo del navegador para no dejar el campo
     invisible. --}}
<div wire:ignore
     x-data="{
        inicializado: false,
        intentos: 0,
        init() {
            this.intentar();
        },
        intentar() {
            if (typeof window.flatpickr === 'function') {
                const cfg = @js($config);
                // Si el input ya trae valor (lo puso wire:model en el render SSR
                // de Livewire), se lo pasamos a flatpickr como defaultDate para
                // que el alt-input visible muestre la fecha inicial.
                const valor = this.$refs.input.value;
                if (valor) cfg.defaultDate = valor;

                window.flatpickr(this.$refs.input, cfg);
                this.inicializado = true;
                return;
            }
            if (this.intentos++ < 20) {
                setTimeout(() => this.intentar(), 50);
                return;
            }
            // Fallback: deja el input visible como date nativo.
            const i = this.$refs.input;
            i.classList.remove('hidden');
            i.type = '{{ $enableTime ? 'datetime-local' : 'date' }}';
            i.className = '{{ $inputClasses }}';
        }
     }">
    <input x-ref="input"
           type="text"
           @if ($wireModel)
               @if ($live) wire:model.live="{{ $wireModel }}"
               @else wire:model="{{ $wireModel }}"
               @endif
           @endif
           @if ($name) name="{{ $name }}" @endif
           @if ($value !== null) value="{{ $value }}" @endif
           placeholder="{{ $placeholder }}"
           class="hidden">
</div>
