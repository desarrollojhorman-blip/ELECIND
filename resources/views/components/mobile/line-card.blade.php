@props([
    'title' => null,
    'subtitle' => null,
    'removeAction' => null,
])

<div {{ $attributes->class('rounded-lg border border-slate-200 bg-white p-3 shadow-sm') }}>
    @if ($title || $removeAction)
        <div class="mb-2 flex items-center justify-between gap-2">
            <div class="min-w-0 flex-1">
                @if ($title)
                    <p class="truncate text-sm font-medium text-slate-700">{{ $title }}</p>
                @endif
                @if ($subtitle)
                    <p class="truncate text-xs text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($removeAction)
                <button type="button"
                        wire:click="{{ $removeAction }}"
                        class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-red-600 transition-colors hover:bg-red-50"
                        title="Eliminar">
                    <x-heroicon-o-x-mark class="size-4" />
                </button>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
