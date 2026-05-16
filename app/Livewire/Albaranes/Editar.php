<?php

namespace App\Livewire\Albaranes;

use App\Enums\TipoHora;
use App\Livewire\Forms\AlbaranForm;
use App\Models\Albaran;
use App\Models\Concepto;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'albaranes'])]
#[Title('Albarán')]
class Editar extends Component
{
    public AlbaranForm $form;

    public ?Albaran $albaran = null;

    public ?int $confirmarEliminarId = null;

    public int $trabajadorSelectKey = 0;

    public int $materialSelectKey = 0;

    public function mount(?Albaran $albaran = null): void
    {
        // Modo web: todas las líneas de personal son iguales, sin línea fija del creador.
        $this->form->omitirLineaCreador = true;

        if ($albaran !== null && $albaran->exists) {
            Gate::authorize('update', $albaran);
            $this->albaran = $albaran;
            $this->albaran->loadMissing(['lineasPersonal', 'lineasMaterial']);
            $this->form->fromModel($albaran);
        } else {
            Gate::authorize('create', Albaran::class);
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    public function updatedFormProyectoId(): void
    {
        $this->form->sincronizarClienteDesdeProyecto();
        // Al cambiar proyecto se limpian las líneas de personal y material
        // porque los disponibles cambian completamente.
        $this->form->companeros = [];
        $this->form->materiales = [];
        $this->trabajadorSelectKey++;
        $this->materialSelectKey++;
    }

    public function agregarTrabajador(): void
    {
        $this->form->addCompanero();
        $this->trabajadorSelectKey++;
    }

    public function quitarTrabajador(int $index): void
    {
        $this->form->removeCompanero($index);
    }

    public function agregarMaterial(): void
    {
        $this->form->addMaterial();
        $this->materialSelectKey++;
    }

    public function quitarMaterial(int $index): void
    {
        $this->form->removeMaterial($index);
    }

    public function guardar(): void
    {
        $esNuevo = $this->albaran === null;

        if ($esNuevo) {
            Gate::authorize('create', Albaran::class);
        } else {
            Gate::authorize('update', $this->albaran);
        }

        $albaran = $this->form->save();

        session()->flash('status', $esNuevo
            ? "Albarán «{$albaran->numero}» creado correctamente."
            : "Albarán «{$albaran->numero}» actualizado correctamente.");

        $this->redirectRoute('albaranes.editar', ['albaran' => $albaran->getKey()]);
    }

    public function confirmarEliminar(): void
    {
        if ($this->albaran === null) {
            return;
        }

        Gate::authorize('delete', $this->albaran);
        $this->confirmarEliminarId = $this->albaran->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        if ($this->albaran === null) {
            return;
        }

        Gate::authorize('delete', $this->albaran);
        $numero = $this->albaran->numero;
        $this->albaran->delete();

        session()->flash('status', "Albarán «{$numero}» enviado a papelera.");
        $this->redirectRoute('albaranes.index', navigate: true);
    }

    /* ───────────────────────── Computeds ────────────────────── */

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        return Proyecto::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'cliente_id']);
    }

    /** @return Collection<int, Concepto> */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with('conceptos:id,nombre')
            ->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->conceptos->toBase();
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function responsablesDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        return User::query()
            ->where('activo', true)
            ->whereHas('proyectos', fn ($q) => $q->where('proyectos.id', $this->form->proyecto_id))
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function trabajadoresDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        return User::query()
            ->where('activo', true)
            ->whereHas('proyectos', fn ($q) => $q
                ->where('proyectos.id', $this->form->proyecto_id)
                ->where('proyecto_usuario.rol_en_proyecto', 'trabajador'))
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, Material> */
    #[Computed]
    public function materialesDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with('materiales:id,descripcion,unidad_medida,stock')
            ->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->materiales->toBase();
    }

    public function render(): View
    {
        $titulo = $this->albaran ? "Albarán {$this->albaran->numero}" : 'Nuevo albarán';
        $tiposHora = TipoHora::cases();

        return view('livewire.albaranes.editar', compact('titulo', 'tiposHora'));
    }
}
