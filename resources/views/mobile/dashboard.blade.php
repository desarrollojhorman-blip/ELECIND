<x-layouts.mobile>
    <div class="space-y-3 px-4 py-5">
        <x-mobile.menu-action href="{{ route('mobile.albaranes.nuevo') }}" variant="primary">
            Parte de Trabajo
        </x-mobile.menu-action>

        <x-mobile.menu-action href="{{ route('mobile.albaranes.personalizado') }}" variant="primary">
            Parte personalizado
        </x-mobile.menu-action>

        <x-mobile.menu-action
            href="{{ route('mobile.albaranes.index') }}"
            variant="outline"
            icon="heroicon-o-document-text">
            Gestión de Albaranes
        </x-mobile.menu-action>

        <x-mobile.menu-action
            href="{{ route('mobile.ausencias.index') }}"
            variant="outline"
            icon="heroicon-o-calendar-days">
            Faltas de Asistencia
        </x-mobile.menu-action>

        <x-mobile.menu-action
            href="{{ route('mobile.resumen.index') }}"
            variant="outline"
            icon="heroicon-o-chart-bar">
            Resumen mensual
        </x-mobile.menu-action>
    </div>
</x-layouts.mobile>
