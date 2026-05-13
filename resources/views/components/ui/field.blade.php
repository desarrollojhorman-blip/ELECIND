@props([
    'label' => null,
    'for' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
])

<div {{ $attributes->class('flex flex-col gap-1') }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="text-xs font-medium text-slate-600">
            {{ $label }}
            @if ($required)
                <span class="text-primary-600">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
