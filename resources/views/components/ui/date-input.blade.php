@props([
    'wireModel' => null,        // nombre de la propiedad Livewire (sin "wire:model")
    'live' => false,             // true → wire:model.live (sincroniza en cada cambio)
    'enableTime' => false,       // muestra selector de hora
    'mode' => 'single',          // 'single' | 'multiple' | 'range'
    'placeholder' => '',
    'name' => null,              // por si se usa fuera de Livewire (form clásico)
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
        'minDate'       => $minDate,
        'maxDate'       => $maxDate,
    ], fn ($v) => $v !== null && $v !== false);
@endphp

{{-- wire:ignore: Livewire NO debe morphear este árbol porque flatpickr
     inyecta DOM y se rompería al re-render. El input oculto sigue
     llevando wire:model (la sincronización con el servidor funciona). --}}
<div wire:ignore x-data="{
    init() {
        window.flatpickr(this.$refs.input, @js($config));
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
           placeholder="{{ $placeholder }}"
           class="hidden">
</div>
