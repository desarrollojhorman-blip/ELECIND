@props([
    'icon' => 'heroicon-o-wrench-screwdriver',
    'roadmap' => null,
])

<div class="flex flex-1 flex-col items-center justify-center px-6 py-12 text-center">
    <div class="mb-4 inline-flex size-16 items-center justify-center rounded-full bg-amber-100 text-amber-600">
        <x-dynamic-component :component="$icon" class="size-8" />
    </div>

    <h2 class="text-lg font-semibold text-slate-900">
        En construcción
    </h2>

    @if ($slot->isNotEmpty())
        <p class="mt-2 text-sm text-slate-600">
            {{ $slot }}
        </p>
    @endif

    @if ($roadmap)
        <p class="mt-3 inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">
            <x-heroicon-m-clock class="size-3" />
            {{ $roadmap }}
        </p>
    @endif

    <a href="{{ route('mobile.dashboard') }}"
       class="mt-6 inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
        <x-heroicon-m-arrow-left class="size-4" />
        Volver al inicio
    </a>
</div>
