<?php

namespace App\Livewire\Mobile\Incidencias;

use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\TipoIncidencia;
use App\Models\Incidencia;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Crear extends Component
{
    public string $tipo       = '';
    public string $prioridad  = 'media';
    public string $titulo     = '';
    public string $descripcion = '';

    public function mount(): void
    {
        Gate::authorize('incidencias.crear');
    }

    public function guardar(): void
    {
        Gate::authorize('incidencias.crear');

        $data = $this->validate([
            'tipo'        => ['required', 'in:' . implode(',', array_column(TipoIncidencia::cases(), 'value'))],
            'prioridad'   => ['required', 'in:' . implode(',', array_column(PrioridadIncidencia::cases(), 'value'))],
            'titulo'      => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ], [
            'tipo.required'    => 'Selecciona el tipo de incidencia.',
            'titulo.required'  => 'El título es obligatorio.',
        ]);

        Incidencia::create([
            'trabajador_id' => Auth::id(),
            'tipo'          => $data['tipo'],
            'prioridad'     => $data['prioridad'],
            'titulo'        => $data['titulo'],
            'descripcion'   => $data['descripcion'] ?: null,
            'estado'        => EstadoIncidencia::PENDIENTE->value,
        ]);

        $this->redirectRoute('mobile.incidencias.index', navigate: true);
    }

    public function render(): View
    {
        $tipos      = TipoIncidencia::cases();
        $prioridades = PrioridadIncidencia::cases();

        return view('livewire.mobile.incidencias.crear', compact('tipos', 'prioridades'))
            ->layout('components.layouts.mobile', [
                'title'     => 'Nueva Incidencia',
                'showBack'  => true,
                'backRoute' => route('mobile.incidencias.index'),
            ]);
    }
}
