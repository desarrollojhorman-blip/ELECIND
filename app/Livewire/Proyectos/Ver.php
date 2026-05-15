<?php

namespace App\Livewire\Proyectos;

use App\Models\Concepto;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'proyectos_lista'])]
class Ver extends Component
{
    public Proyecto $proyecto;

    public ?int $confirmarEliminarId = null;

    public function mount(Proyecto $proyecto): void
    {
        Gate::authorize('view', $proyecto);
        $this->proyecto = $proyecto;
    }

    public function confirmarEliminar(): void
    {
        Gate::authorize('delete', $this->proyecto);
        $this->confirmarEliminarId = $this->proyecto->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->proyecto);
        $nombre = $this->proyecto->nombre;
        $this->proyecto->delete();
        session()->flash('status', "Proyecto «{$nombre}» enviado a papelera.");
        $this->redirectRoute('proyectos.index', navigate: true);
    }

    /* ───────────────────────── Computeds ────────────────────── */

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function trabajadoresProyecto(): Collection
    {
        return $this->usuariosProyectoPorRol('trabajador');
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesProyecto(): Collection
    {
        return $this->usuariosProyectoPorRol('responsable');
    }

    /**
     * @return Collection<int, Concepto>
     */
    #[Computed]
    public function conceptosProyecto(): Collection
    {
        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with(['conceptos' => function ($q): void {
                $q->orderBy('nombre');
            }])
            ->find($this->proyecto->id);

        if (! $proyecto instanceof Proyecto) {
            return collect();
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Concepto> $conceptos */
        $conceptos = $proyecto->conceptos;

        return $conceptos->toBase();
    }

    /**
     * @return Collection<int, Material>
     */
    #[Computed]
    public function materialesProyecto(): Collection
    {
        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with(['materiales' => function ($q): void {
                $q->orderBy('descripcion');
            }])
            ->find($this->proyecto->id);

        if (! $proyecto instanceof Proyecto) {
            return collect();
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Material> $materiales */
        $materiales = $proyecto->materiales;

        return $materiales->toBase();
    }

    public function render(): View
    {
        return view('livewire.proyectos.ver');
    }

    /* ───────────────────────── Privados ────────────────────── */

    /**
     * @return Collection<int, User>
     */
    private function usuariosProyectoPorRol(string $rol): Collection
    {
        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with(['usuarios' => function ($q) use ($rol): void {
                $q->wherePivot('rol_en_proyecto', $rol)
                    ->orderBy('nombre')
                    ->orderBy('apellidos');
            }])
            ->find($this->proyecto->id);

        if (! $proyecto instanceof Proyecto) {
            return collect();
        }

        /** @var EloquentCollection<int, User> $usuarios */
        $usuarios = $proyecto->usuarios;

        return $usuarios->toBase();
    }
}
