@props(['rows' => 3])

@php $readonly = $attributes->has('readonly'); @endphp

<textarea rows="{{ $rows }}"
    {{ $attributes->class([
        'w-full appearance-none rounded-md border px-3 py-2 text-sm shadow-none transition-colors placeholder:text-slate-400 focus:outline-none focus:ring-0 focus:shadow-none',
        'border-slate-300 bg-white focus:border-primary-500' => !$readonly,
        'border-slate-200 bg-slate-50 text-slate-500 cursor-default resize-none' => $readonly,
    ]) }}>{{ $slot }}</textarea>
