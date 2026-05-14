@php
    /** @var string $titulo */
    /** @var string $icono */
    /** @var string|null $roadmap */
    /** @var string|null $descripcion */
    $titulo ??= 'En construcción';
    $icono ??= 'heroicon-o-wrench-screwdriver';
    $roadmap ??= null;
    $descripcion ??= null;
@endphp

<x-layouts.mobile :title="$titulo" show-back>
    <x-mobile.placeholder :icon="$icono" :roadmap="$roadmap">
        {{ $descripcion ?? '' }}
    </x-mobile.placeholder>
</x-layouts.mobile>
