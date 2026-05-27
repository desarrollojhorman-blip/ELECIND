<?php

namespace App\Livewire\Borradores;

use App\Models\Albaran;
use App\Models\Borrador;
use App\Services\NumeracionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'borradores'])]
#[Title('Ver borrador')]
class Ver extends Component
{
    public Borrador $borrador;

    public ?int $confirmarEliminarId    = null;
    public bool $confirmarConvertir     = false;

    public function mount(Borrador $borrador): void
    {
        Gate::authorize('view', $borrador);
        $this->borrador->loadMissing([
            'proyecto:id,nombre,codigo',
            'cliente:id,nombre',
            'concepto:id,nombre',
            'responsable:id,nombre,apellidos',
            'creador:id,nombre,apellidos',
            'albaranConvertido:id,numero',
            'lineasPersonal.trabajador:id,nombre,apellidos',
            'lineasMaterial.material:id,descripcion,unidad_medida',
        ]);
    }

    /* ── Eliminar ──────────────────────────────────────────────── */

    public function confirmarEliminar(): void
    {
        Gate::authorize('delete', $this->borrador);
        $this->confirmarEliminarId = $this->borrador->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        Gate::authorize('delete', $this->borrador);
        $numero = $this->borrador->numero_borrador;
        $this->borrador->delete();

        session()->flash('status', "Borrador «{$numero}» eliminado.");
        $this->redirectRoute('borradores.index', navigate: false);
    }

    /* ── Convertir a albarán ───────────────────────────────────── */

    public function abrirConfirmarConvertir(): void
    {
        Gate::authorize('convertir', $this->borrador);
        $this->confirmarConvertir = true;
    }

    public function cancelarConvertir(): void
    {
        $this->confirmarConvertir = false;
    }

    public function convertirAAlbaran(): void
    {
        Gate::authorize('convertir', $this->borrador);

        // Validar que los campos clave estén resueltos como FK
        if ($this->borrador->proyecto_id === null) {
            session()->flash('error', 'El borrador debe tener un proyecto seleccionado (no texto libre) para convertirse en albarán.');
            $this->confirmarConvertir = false;
            return;
        }

        if ($this->borrador->cliente_id === null) {
            session()->flash('error', 'El borrador debe tener un cliente seleccionado (no texto libre) para convertirse en albarán.');
            $this->confirmarConvertir = false;
            return;
        }

        $albaran = DB::transaction(function (): Albaran {
            $albaran = new Albaran;
            $albaran->numero       = app(NumeracionService::class)->siguienteNumeroAlbaran(Carbon::parse($this->borrador->fecha));
            $albaran->creado_por   = (int) Auth::id();
            $albaran->estado       = \App\Enums\EstadoAlbaran::PENDIENTE_FIRMA;
            $albaran->fecha        = $this->borrador->fecha;
            $albaran->cliente_id   = $this->borrador->cliente_id;
            $albaran->proyecto_id  = $this->borrador->proyecto_id;
            $albaran->concepto_id  = $this->borrador->concepto_id;
            $albaran->responsable_id = $this->borrador->responsable_id;
            $albaran->tipo_hora    = \App\Enums\TipoHora::from($this->borrador->tipo_hora);
            $albaran->observaciones = $this->borrador->observaciones;
            $albaran->save();

            // Copiar líneas de personal
            foreach ($this->borrador->lineasPersonal as $linea) {
                if ($linea->trabajador_id !== null) {
                    $albaran->lineasPersonal()->create([
                        'trabajador_id' => $linea->trabajador_id,
                        'horas'         => $linea->horas,
                        'horas_extra'   => $linea->horas_extra,
                    ]);
                }
            }

            // Copiar líneas de material (solo las que tienen FK resuelta)
            foreach ($this->borrador->lineasMaterial as $linea) {
                if ($linea->material_id !== null) {
                    $albaran->lineasMaterial()->create([
                        'material_id' => $linea->material_id,
                        'cantidad'    => $linea->cantidad,
                    ]);
                }
            }

            // Marcar borrador como convertido
            $this->borrador->update([
                'estado'                   => 'convertido',
                'convertido_a_albaran_id'  => $albaran->id,
            ]);

            return $albaran;
        });

        session()->flash('status', "Borrador convertido. Albarán «{$albaran->numero}» creado correctamente.");
        $this->redirectRoute('albaranes.editar', ['albaran' => $albaran->getKey()], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.borradores.ver');
    }
}
