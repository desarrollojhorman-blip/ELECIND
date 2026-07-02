@props([
    'label' => null,
    'for' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
])

<div {{ $attributes->class('flex flex-col gap-1.5') }}>
    @if ($label)
        @isset($action)
            <div class="flex items-center justify-between gap-2">
                <label @if ($for) for="{{ $for }}" @endif class="text-sm font-medium text-slate-700">
                    {{ $label }}
                    @if ($required)
                        <span class="text-primary-600">*</span>
                    @endif
                </label>
                <div class="shrink-0">{{ $action }}</div>
            </div>
        @else
            <label @if ($for) for="{{ $for }}" @endif class="text-sm font-medium text-slate-700">
                {{ $label }}
                @if ($required)
                    <span class="text-primary-600">*</span>
                @endif
            </label>
        @endisset
    @endif

    {{ $slot }}

    @if ($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
