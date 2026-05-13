@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->class('mb-4 flex flex-wrap items-end justify-between gap-3') }}>
    <div>
        @if ($title)
            <h2 class="text-xl font-semibold text-slate-900">{{ $title }}</h2>
        @endif
        @if ($subtitle)
            <p class="text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset ($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
