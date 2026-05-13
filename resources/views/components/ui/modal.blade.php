@props([
    'show' => false,
    'title' => null,
    'size' => 'lg',
    'closeAction' => null,
])

@php
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

@if ($show)
    <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 p-4"
         @if ($closeAction) wire:keydown.escape.window="{{ $closeAction }}" @endif>
        <div class="flex max-h-[calc(100vh-2rem)] w-full {{ $sizes[$size] ?? $sizes['lg'] }} flex-col overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-slate-900/5"
             @click.outside="$wire.{{ $closeAction }}()"
             wire:click.stop>
            @if ($title || $closeAction)
                <div class="flex shrink-0 items-center justify-between rounded-t-lg border-b border-accent-200 bg-accent-100 px-5 py-3">
                    <h3 class="text-base font-semibold text-primary-800">
                        {{ $title }}
                    </h3>
                    @if ($closeAction)
                        <button type="button"
                                wire:click="{{ $closeAction }}"
                                class="rounded p-1 text-slate-500 transition-colors hover:bg-white/60 hover:text-slate-700">
                            <x-heroicon-o-x-mark class="size-5" />
                        </button>
                    @endif
                </div>
            @endif

            {{-- Body con scroll interno cuando hay overflow --}}
            <div class="flex-1 overflow-y-auto px-5 py-4">
                {{ $slot }}
            </div>

            @isset ($footer)
                <div class="flex shrink-0 items-center justify-end gap-2 rounded-b-lg border-t border-slate-200 bg-slate-50 px-5 py-3">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
@endif
