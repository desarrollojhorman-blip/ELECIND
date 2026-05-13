<x-layouts.web title="Dashboard" active="dashboard">
    <x-ui.page-header title="Dashboard" subtitle="Vista general de la operación." />

    <div class="grid gap-4 md:grid-cols-3">
        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Bienvenido</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">
                        {{ auth()->user()?->nombre }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">Panel web</p>
                </div>
                <div class="rounded-lg bg-primary-50 p-2 text-primary-600">
                    <x-heroicon-o-hand-raised class="size-6" />
                </div>
            </div>
        </x-ui.card>

        @can('clientes.ver')
            <a href="{{ route('clientes.index') }}"
               class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-primary-200 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Clientes</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">
                            {{ \App\Models\EmpresasCliente::count() }}
                        </p>
                        <p class="mt-1 inline-flex items-center gap-1 text-sm text-primary-600">
                            Gestionar
                            <x-heroicon-m-arrow-right class="size-3.5" />
                        </p>
                    </div>
                    <div class="rounded-lg bg-primary-50 p-2 text-primary-600">
                        <x-heroicon-o-building-office-2 class="size-6" />
                    </div>
                </div>
            </a>
        @endcan

        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Estado</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">Fase 1</p>
                    <p class="mt-1 text-sm text-slate-500">MVP base en construcción</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-2 text-amber-600">
                    <x-heroicon-o-wrench-screwdriver class="size-6" />
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.web>
