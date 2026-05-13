@props([
    'type' => 'text',
])

<input type="{{ $type }}"
    {{ $attributes->class('w-full appearance-none rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-none transition-colors placeholder:text-slate-400 focus:border-primary-500 focus:outline-none focus:ring-0 focus:shadow-none disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500') }}>
