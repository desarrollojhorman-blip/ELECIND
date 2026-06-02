<?php

namespace App\Livewire\Borradores;

use App\Livewire\Forms\BorradorForm;
use App\Models\Borrador;
use App\Models\Cliente;
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

#[Layout('components.layouts.web', ['active' => 'borradores'])]
#[Title('Borrador')]
class Editar extends Component
{
    public BorradorForm $form;

    public ?Borrador $borrador = null;

    public ?int $confirmarEliminarId = null;

    public function mount(?Borrador $borrador = null): void
    {
        if ($borrador !== null && $borrador->exists) {
            Gate::authorize('update', $borrador);
            $this->borrador = $borrador;
            $this->borrador->load(['lineasPersonal.trabajador', 'lineasMaterial.material']);
            $this->form->fromModel($borrador);
        } else {
            Gate::authorize('create', Borrador::class);
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    public function deshacer(): void
    {
        if ($this->borrador !== null) {
            $this->borrador->loadMissing(['lineasPersonal.trabajador', 'lineasMaterial.material']);
            $this->form->fromModel($this->borrador);
        } else {
            $this->form->reset();
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    public function guardar(): void
    {
        $esNuevo = $this->borrador === null;

        if ($esNuevo) {
            Gate::authorize('create', Borrador::class);
        } else {
            Gate::authorize('update', $this->borrador);
        }

        $borrador = $this->form->save();

        session()->flash('status', $esNuevo
            ? "Borrador «{$borrador->numero_borrador}» creado correctamente."
            : "Borrador «{$borrador->numero_borrador}» actualizado correctamente.");

        $this->redirectRoute('borradores.editar', ['borrador' => $borrador->getKey()]);
    }

    public function confirmarEliminar(): void
    {
        if ($this->borrador === null) {
            return;
        }
        Gate::authorize('delete', $this->borrador);
        $this->confirmarEliminarId = $this->borrador->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        if ($this->borrador === null) {
            return;
        }
        Gate::authorize('delete', $this->borrador);
        $numero = $this->borrador->numero_borrador;
        $this->borrador->delete();

        session()->flash('status', "Borrador «{$numero}» eliminado.");
        $this->redirectRoute('borradores.index', navigate: false);
    }

    /* ── Computeds ─────────────────────────────────────────────── */

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        return Proyecto::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);
    }

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /** @return Collection<int, Concepto> */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        return Concepto::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function trabajadoresDisponibles(): Collection
    {
        return User::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, Material> */
    #[Computed]
    public function materialesDisponibles(): Collection
    {
        return Material::query()
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'unidad_medida']);
    }

    public function render(): View
    {
        $titulo = $this->borrador
            ? "Borrador {$this->borrador->numero_borrador}"
            : 'Nuevo borrador';

        return view('livewire.borradores.editar', compact('titulo'));
    }
}
