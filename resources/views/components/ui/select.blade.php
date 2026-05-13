<select {{ $attributes->class('w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow-sm transition-colors focus:border-primary-500 focus:ring-primary-500 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500') }}>
    {{ $slot }}
</select>
