@props([
    'label' => null,
    'value' => null,
    'removeAction' => null,
])

<span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700">
    @if ($label)
        <span class="text-primary-600/80">{{ $label }}:</span>
    @endif
    <span>{{ $value ?? $slot }}</span>
    @if ($removeAction)
        <button type="button"
                wire:click="{{ $removeAction }}"
                class="ml-0.5 rounded-full p-0.5 hover:bg-primary-100"
                title="Quitar filtro">
            <x-heroicon-m-x-mark class="size-3" />
        </button>
    @endif
</span>
