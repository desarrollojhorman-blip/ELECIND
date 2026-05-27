<?php

namespace App\Livewire\Materiales;

use App\Models\Albaran;
use App\Models\Material;
use App\Models\Proyecto;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'materiales_lista'])]
#[Title('Ver material')]
class Ver extends Component
{
    public Material $material;

    public ?int $confirmarEliminarId = null;

    public string $ordenAlbaranes = 'fecha';
    public string $dirAlbaranes = 'desc';

    public string $ordenProyectos = 'nombre';
    public string $dirProyectos = 'asc';

    public function mount(Material $material): void
    {
        Gate::authorize('view', $material);
        $this->material = $material->load(['numeroPedido', 'familia']);
    }

    public function confirmarEliminar(): void
    {
        Gate::authorize('delete', $this->material);
        $this->confirmarEliminarId = $this->material->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->material);
        $descripcion = $this->material->descripcion;
        $this->material->delete();
        session()->flash('status', "Material «{$descripcion}» enviado a papelera.");
        $this->redirectRoute('materiales.index', navigate: false);
    }

    public function ordenarAlbaranes(string $campo): void
    {
        if ($this->ordenAlbaranes === $campo) {
            $this->dirAlbaranes = $this->dirAlbaranes === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenAlbaranes = $campo;
            $this->dirAlbaranes = 'asc';
        }
    }

    public function ordenarProyectos(string $campo): void
    {
        if ($this->ordenProyectos === $campo) {
            $this->dirProyectos = $this->dirProyectos === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenProyectos = $campo;
            $this->dirProyectos = 'asc';
        }
    }

    /** @return Collection<int, Albaran> */
    #[Computed]
    public function albaranesDelMaterial(): Collection
    {
        $albaranes = Albaran::query()
            ->whereHas('lineasMaterial', fn ($q) => $q->where('material_id', $this->material->id))
            ->with(['proyecto:id,nombre', 'cliente:id,nombre'])
            ->get(['id', 'numero', 'fecha', 'estado', 'proyecto_id', 'cliente_id']);

        $clave = match ($this->ordenAlbaranes) {
            'numero'   => fn (Albaran $a): string => (string) ($a->numero ?? ''),
            'proyecto' => fn (Albaran $a): string => (string) ($a->proyecto?->nombre ?? ''),
            'cliente'  => fn (Albaran $a): string => (string) ($a->cliente?->nombre ?? ''),
            'estado'   => fn (Albaran $a): string => $a->estado instanceof \BackedEnum ? $a->estado->value : (string) $a->estado,
            default    => fn (Albaran $a): string => (string) ($a->fecha ?? ''),
        };

        return $this->dirAlbaranes === 'desc'
            ? $albaranes->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $albaranes->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDelMaterial(): Collection
    {
        $proyectos = $this->material->proyectos()
            ->with(['cliente:id,nombre', 'tipoProyecto:id,nombre'])
            ->get(['proyectos.id', 'proyectos.nombre', 'proyectos.codigo', 'proyectos.estado', 'proyectos.cliente_id', 'proyectos.tipo_proyecto_id']);

        $clave = match ($this->ordenProyectos) {
            'codigo'  => fn (Proyecto $p): string => (string) ($p->codigo ?? ''),
            'cliente' => fn (Proyecto $p): string => (string) ($p->cliente?->nombre ?? ''),
            'estado'  => fn (Proyecto $p): string => (string) ($p->estado ?? ''),
            default   => fn (Proyecto $p): string => (string) $p->nombre,
        };

        return $this->dirProyectos === 'desc'
            ? $proyectos->sortByDesc($clave, SORT_NATURAL | SORT_FLAG_CASE)->values()
            : $proyectos->sortBy($clave, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function render(): View
    {
        return view('livewire.materiales.ver');
    }
}
