@props(['rows' => 3])

<textarea rows="{{ $rows }}"
          {{ $attributes->class('w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-slate-400 focus:border-primary-500 focus:ring-primary-500') }}>{{ $slot }}</textarea>
