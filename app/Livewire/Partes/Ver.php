<?php

namespace App\Livewire\Partes;

use App\Models\Parte;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'partes'])]
#[Title('Ver parte')]
class Ver extends Component
{
    public Parte $parte;

    public ?int $confirmarEliminarId = null;

    public function mount(Parte $parte): void
    {
        Gate::authorize('view', $parte);
        $parte->load([
            'lineasPersonal.atributo:id,codigo,nombre_corto',
            'lineasPersonal.trabajador:id,nombre,apellidos',
            'user:id,nombre,apellidos',
            'proyecto:id,codigo,nombre',
        ]);
        $this->parte = $parte;
    }

    public function confirmarEliminar(): void
    {
        $this->confirmarEliminarId = $this->parte->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): mixed
    {
        Gate::authorize('delete', $this->parte);
        $codigo = $this->parte->codigo;
        $this->parte->delete();
        session()->flash('status', "Parte «{$codigo}» eliminado.");

        return $this->redirect(route('partes.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.partes.ver');
    }
}
