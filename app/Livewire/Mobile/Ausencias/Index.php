<?php

namespace App\Livewire\Mobile\Ausencias;

use App\Enums\EstadoAusencia;
use App\Enums\TipoAusencia;
use App\Models\Ausencia;
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
        Gate::authorize('ausencias.ver_propias');
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

    public function eliminar(int $id): void
    {
        Gate::authorize('ausencias.solicitar');

        $ausencia = Ausencia::where('trabajador_id', Auth::id())->findOrFail($id);

        if ($ausencia->estado !== EstadoAusencia::PENDIENTE) {
            return;
        }

        $ausencia->delete();
        $this->dispatch('toast', tipo: 'success', mensaje: 'Ausencia eliminada.');
    }

    #[Computed]
    public function ausencias()
    {
        $query = Ausencia::query()
            ->where('trabajador_id', Auth::id())
            ->when($this->filtroEstado !== 'todas', fn ($q) => $q->where('estado', $this->filtroEstado));

        if ($this->buscar !== '') {
            $termino    = '%'.trim($this->buscar).'%';
            $terminoMin = mb_strtolower(trim($this->buscar));

            $tiposCoincidentes = collect(TipoAusencia::cases())
                ->filter(fn ($t) => str_contains(mb_strtolower($t->etiqueta()), $terminoMin))
                ->map(fn ($t) => $t->value)->values()->all();

            $estadosCoincidentes = collect(EstadoAusencia::cases())
                ->filter(fn ($e) => str_contains(mb_strtolower($e->etiqueta()), $terminoMin))
                ->map(fn ($e) => $e->value)->values()->all();

            $query->where(function ($q) use ($termino, $tiposCoincidentes, $estadosCoincidentes): void {
                $q->where('motivo', 'like', $termino)
                    ->orWhere('observaciones', 'like', $termino)
                    ->orWhereRaw("DATE_FORMAT(fecha_inicio, '%d/%m/%Y') LIKE ?", [$termino])
                    ->orWhereRaw("DATE_FORMAT(fecha_fin, '%d/%m/%Y') LIKE ?", [$termino]);

                if (! empty($tiposCoincidentes)) {
                    $q->orWhereIn('tipo', $tiposCoincidentes);
                }
                if (! empty($estadosCoincidentes)) {
                    $q->orWhereIn('estado', $estadosCoincidentes);
                }
            });
        }

        return $query->orderByDesc('fecha_inicio')->paginate(20);
    }

    public function render(): View
    {
        $estados = EstadoAusencia::cases();

        return view('livewire.mobile.ausencias.index', compact('estados'))
            ->layout('components.layouts.mobile', [
                'title'     => 'Mis Ausencias',
                'showBack'  => true,
                'backRoute' => route('mobile.dashboard'),
            ]);
    }
}
