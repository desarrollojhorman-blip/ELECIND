@props([
    'options' => [],
    'placeholder' => 'Seleccionar...',
])

@php
    $normalizedOptions = collect($options)
        ->map(fn ($o) => [
            'value' => (string) data_get($o, 'value', ''),
            'text'  => (string) data_get($o, 'label', ''),
        ])
        ->values()
        ->all();
@endphp

<div>
    <select {{ $attributes->class('w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm') }}>
        @if ($placeholder !== '')
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($normalizedOptions as $option)
            <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
        @endforeach
    </select>
</div>
