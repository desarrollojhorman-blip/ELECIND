<?php

namespace App\Livewire\Mobile\Ausencias;

use App\Enums\EstadoAusencia;
use App\Enums\TipoAusencia;
use App\Models\Ausencia;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Crear extends Component
{
    public string $tipo        = '';
    public string $fechaInicio = '';
    public string $fechaFin    = '';
    public string $motivo      = '';

    public ?Ausencia $ausencia = null;

    public bool $soloLectura = false;

    public function mount(?Ausencia $ausencia = null): void
    {
        Gate::authorize('ausencias.solicitar');

        if ($ausencia !== null && $ausencia->exists) {
            if ($ausencia->trabajador_id !== (int) Auth::id()) {
                abort(403);
            }

            $this->ausencia   = $ausencia;
            $this->soloLectura = $ausencia->estado !== EstadoAusencia::PENDIENTE;

            $this->tipo        = $ausencia->tipo->value;
            $this->fechaInicio = $ausencia->fecha_inicio->format('Y-m-d');
            $this->fechaFin    = $ausencia->fecha_fin->format('Y-m-d');
            $this->motivo      = $ausencia->motivo ?? '';
        } else {
            $this->fechaInicio = now()->format('Y-m-d');
            $this->fechaFin    = now()->format('Y-m-d');
        }
    }

    public function guardar(): void
    {
        if ($this->soloLectura) {
            return;
        }

        Gate::authorize('ausencias.solicitar');

        $data = $this->validate([
            'tipo'        => ['required', 'in:' . implode(',', array_column(TipoAusencia::cases(), 'value'))],
            'fechaInicio' => ['required', 'date'],
            'fechaFin'    => ['required', 'date', 'gte:fechaInicio'],
            'motivo'      => ['nullable', 'string', 'max:500'],
        ], [
            'tipo.required'        => 'Selecciona el tipo de ausencia.',
            'fechaInicio.required' => 'La fecha de inicio es obligatoria.',
            'fechaFin.required'    => 'La fecha de fin es obligatoria.',
            'fechaFin.gte'         => 'La fecha de fin no puede ser anterior al inicio.',
        ]);

        $payload = [
            'tipo'        => $data['tipo'],
            'fecha_inicio' => $data['fechaInicio'],
            'fecha_fin'   => $data['fechaFin'],
            'motivo'      => $data['motivo'] ?: null,
        ];

        if ($this->ausencia !== null) {
            $this->ausencia->update($payload);
        } else {
            Ausencia::create(array_merge($payload, [
                'trabajador_id' => Auth::id(),
                'estado'        => EstadoAusencia::PENDIENTE->value,
            ]));
        }

        $this->redirectRoute('mobile.ausencias.index', navigate: true);
    }

    public function render(): View
    {
        $tipos    = TipoAusencia::cases();
        $esEditar = $this->ausencia !== null;

        $title = $this->soloLectura ? 'Ver Ausencia' : ($esEditar ? 'Editar Ausencia' : 'Solicitar Ausencia');

        return view('livewire.mobile.ausencias.crear', compact('tipos', 'esEditar'))
            ->layout('components.layouts.mobile', [
                'title'     => $title,
                'showBack'  => true,
                'backRoute' => route('mobile.ausencias.index'),
            ]);
    }
}
