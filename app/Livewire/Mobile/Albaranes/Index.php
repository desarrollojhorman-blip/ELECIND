<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Models\Albaran;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /** Filtros: todos | borrador | pendiente_firma | firmado */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'todos';

    public function mount(): void
    {
        Gate::authorize('viewAny', Albaran::class);
    }

    public function setFiltro(string $estado): void
    {
        $this->filtroEstado = $estado;
        $this->resetPage();
    }

    public function render(): View
    {
        $userId = (int) Auth::id();
        $puedeVerTodos = Auth::user()?->can('albaranes.ver_todos') ?? false;

        $query = Albaran::query()->with(['cliente:id,nombre', 'proyecto:id,nombre']);

        if (! $puedeVerTodos) {
            $query->where(function (Builder $q) use ($userId): void {
                $q->where('creado_por', $userId)
                    ->orWhereHas('lineasPersonal', fn ($qp) => $qp->where('trabajador_id', $userId));
            });
        }

        if ($this->filtroEstado !== 'todos') {
            $query->where('estado', $this->filtroEstado);
        }

        $query->orderByDesc('fecha')->orderByDesc('id');

        return view('livewire.mobile.albaranes.index', [
            'albaranes' => $query->paginate(20),
        ])->layout('components.layouts.mobile', [
            'title' => 'Mis albaranes',
            'showBack' => true,
            'backRoute' => route('mobile.dashboard'),
        ]);
    }
}
