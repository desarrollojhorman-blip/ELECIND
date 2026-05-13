@props(['label' => null])

<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="checkbox"
           {{ $attributes->class('rounded border-slate-300 text-primary-600 focus:ring-primary-500') }}>
    @if ($label)
        <span>{{ $label }}</span>
    @endif
    {{ $slot }}
</label>
