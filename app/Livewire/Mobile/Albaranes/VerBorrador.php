<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Models\Borrador;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class VerBorrador extends Component
{
    public Borrador $borrador;

    public function mount(Borrador $borrador): void
    {
        Gate::authorize('view', $borrador);

        $this->borrador = $borrador->load([
            'cliente:id,nombre',
            'proyecto:id,nombre',
            'concepto:id,nombre',
            'creador:id,nombre,apellidos',
            'lineasPersonal.trabajador:id,nombre,apellidos',
            'lineasMaterial.material:id,descripcion,unidad_medida',
            'parteConvertido:id,numero',
        ]);
    }

    public function render(): View
    {
        return view('livewire.mobile.albaranes.ver-borrador')
            ->layout('components.layouts.mobile', [
                'title'     => $this->borrador->numero_borrador,
                'showBack'  => true,
                'backRoute' => route('mobile.albaranes.index'),
            ]);
    }
}
