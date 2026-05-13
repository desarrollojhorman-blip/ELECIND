@props(['padding' => 'p-5'])

<div {{ $attributes->class("rounded-lg border border-slate-200 bg-white shadow-sm {$padding}") }}>
    {{ $slot }}
</div>
