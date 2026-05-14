<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Models\Albaran;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Ver extends Component
{
    public Albaran $albaran;

    public ?int $confirmarEliminarId = null;

    public function mount(Albaran $albaran): void
    {
        Gate::authorize('view', $albaran);

        $this->albaran = $albaran->load([
            'cliente:id,nombre',
            'proyecto:id,nombre',
            'concepto:id,nombre',
            'creador:id,nombre,apellidos',
            'responsable:id,nombre,apellidos',
            'lineasPersonal.trabajador:id,nombre,apellidos',
            'lineasMaterial.material:id,descripcion,unidad_medida',
        ]);
    }

    public function confirmarEliminar(): void
    {
        $this->confirmarEliminarId = $this->albaran->getKey();
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->albaran);

        $numero = $this->albaran->numero;

        // Eliminar líneas material una a una para que el Observer devuelva el stock.
        $this->albaran->lineasMaterial()->each(fn ($linea) => $linea->delete());
        $this->albaran->delete();

        session()->flash('status', "Parte «{$numero}» eliminado.");

        $this->redirectRoute('mobile.albaranes.index', navigate: false);
    }

    public function render(): View
    {
        $userId = (int) Auth::id();
        $puedeEditar = $this->albaran->estado->esEditable()
            && ($this->albaran->creado_por === $userId || Auth::user()?->can('albaranes.modificar'));
        $puedeEliminar = $this->albaran->estado->esEditable()
            && ($this->albaran->creado_por === $userId || Auth::user()?->can('albaranes.modificar_terminado'));

        return view('livewire.mobile.albaranes.ver', [
            'puedeEditar' => $puedeEditar,
            'puedeEliminar' => $puedeEliminar,
        ])->layout('components.layouts.mobile', [
            'title' => $this->albaran->numero,
            'showBack' => true,
            'backRoute' => route('mobile.albaranes.index'),
        ]);
    }
}
