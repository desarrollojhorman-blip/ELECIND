@props([
    'type' => 'text',
])

@php $readonly = $attributes->has('readonly'); @endphp

<input type="{{ $type }}"
    {{ $attributes->class([
        'w-full appearance-none rounded-md border px-3 py-2 text-sm shadow-none transition-colors placeholder:text-slate-400 focus:outline-none focus:ring-0 focus:shadow-none disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500',
        'border-slate-300 bg-white focus:border-primary-500' => !$readonly,
        'border-slate-200 bg-slate-50 text-slate-500 cursor-default' => $readonly,
    ]) }}>
