@props(['hint' => null])

<div {{ $attributes->class('mb-2 mt-4 flex items-end justify-between gap-2 first:mt-0') }}>
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
        {{ $slot }}
    </h3>
    @if ($hint)
        <span class="text-xs text-slate-400">{{ $hint }}</span>
    @endif
</div>
