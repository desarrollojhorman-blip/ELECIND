<?php

namespace App\Livewire\Albaranes;

use App\Models\Albaran;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'albaranes'])]
#[Title('Ver albarán')]
class Ver extends Component
{
    public Albaran $albaran;

    public ?int $confirmarEliminarId = null;

    public function mount(Albaran $albaran): void
    {
        Gate::authorize('view', $albaran);
        $this->albaran->loadMissing([
            'cliente:id,nombre',
            'proyecto:id,nombre,codigo',
            'concepto:id,nombre',
            'creador:id,nombre,apellidos',
            'responsable:id,nombre,apellidos',
            'lineasPersonal.trabajador:id,nombre,apellidos',
            'lineasMaterial.material:id,descripcion,unidad_medida',
            'firmas',
            'archivos',
            'parte:id,numero',
        ]);
    }

    public function confirmarEliminar(): void
    {
        Gate::authorize('delete', $this->albaran);
        $this->confirmarEliminarId = $this->albaran->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->albaran);
        $numero = $this->albaran->numero;
        $this->albaran->delete();

        session()->flash('status', "Albarán «{$numero}» enviado a papelera.");
        $this->redirectRoute('albaranes.index', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.albaranes.ver');
    }
}
