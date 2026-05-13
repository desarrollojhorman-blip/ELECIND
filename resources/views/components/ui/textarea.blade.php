@props(['rows' => 3])

<textarea rows="{{ $rows }}"
          {{ $attributes->class('w-full appearance-none rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-none transition-colors placeholder:text-slate-400 focus:border-primary-500 focus:outline-none focus:ring-0 focus:shadow-none') }}>{{ $slot }}</textarea>
