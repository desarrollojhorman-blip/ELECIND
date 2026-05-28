<?php

namespace App\Livewire\Mobile\Incidencias;

use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\TipoIncidencia;
use App\Models\Incidencia;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filtroEstado = 'todas';

    public string $buscar = '';

    public function mount(): void
    {
        Gate::authorize('incidencias.ver_propias');
    }

    public function setFiltro(string $estado): void
    {
        $this->filtroEstado = $estado;
        $this->resetPage();
    }

    public function updatedBuscar(): void { $this->resetPage(); }

    public function limpiarBuscar(): void
    {
        $this->buscar = '';
        $this->resetPage();
    }

    #[Computed]
    public function incidencias()
    {
        $query = Incidencia::where('trabajador_id', Auth::id())
            ->when($this->filtroEstado !== 'todas', fn ($q) => $q->where('estado', $this->filtroEstado));

        if ($this->buscar !== '') {
            $termino    = '%'.trim($this->buscar).'%';
            $terminoMin = mb_strtolower(trim($this->buscar));

            $tiposCoincidentes = collect(TipoIncidencia::cases())
                ->filter(fn ($t) => str_contains(mb_strtolower($t->etiqueta()), $terminoMin))
                ->map(fn ($t) => $t->value)->values()->all();

            $prioridadesCoincidentes = collect(PrioridadIncidencia::cases())
                ->filter(fn ($p) => str_contains(mb_strtolower($p->etiqueta()), $terminoMin))
                ->map(fn ($p) => $p->value)->values()->all();

            $estadosCoincidentes = collect(EstadoIncidencia::cases())
                ->filter(fn ($e) => str_contains(mb_strtolower($e->etiqueta()), $terminoMin))
                ->map(fn ($e) => $e->value)->values()->all();

            $query->where(function ($q) use ($termino, $tiposCoincidentes, $prioridadesCoincidentes, $estadosCoincidentes): void {
                $q->where('titulo', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino)
                    ->orWhere('resolucion', 'like', $termino)
                    ->orWhereRaw("DATE_FORMAT(created_at, '%d/%m/%Y') LIKE ?", [$termino]);

                if (! empty($tiposCoincidentes)) {
                    $q->orWhereIn('tipo', $tiposCoincidentes);
                }
                if (! empty($prioridadesCoincidentes)) {
                    $q->orWhereIn('prioridad', $prioridadesCoincidentes);
                }
                if (! empty($estadosCoincidentes)) {
                    $q->orWhereIn('estado', $estadosCoincidentes);
                }
            });
        }

        return $query->orderByDesc('created_at')->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.mobile.incidencias.index')
            ->layout('components.layouts.mobile', [
                'title'     => 'Mis Incidencias',
                'showBack'  => true,
                'backRoute' => route('mobile.dashboard'),
            ]);
    }
}
