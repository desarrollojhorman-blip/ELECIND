<?php

namespace App\Livewire\Borradores;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Models\Albaran;
use App\Models\Borrador;
use App\Models\Parte;
use App\Services\GeneradorAlbaran;
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
            'parteConvertido:id,numero',
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

        DB::transaction(function (): void {
            $fecha    = Carbon::parse($this->borrador->fecha);
            $plusReten = (bool) $this->borrador->tiene_plus_retencion;

            $tipoHora = $this->borrador->tipo_hora;

            // ── Regla: SIEMPRE se crea el parte primero (la base) ──────────
            $parte = new Parte;
            $parte->creado_por           = $this->borrador->creado_por ?? (int) Auth::id();
            $parte->estado               = Parte::ESTADO_ABIERTO;
            $parte->fecha                = $this->borrador->fecha;
            $parte->cliente_id           = $this->borrador->cliente_id;
            $parte->proyecto_id          = $this->borrador->proyecto_id;
            $parte->concepto_id          = $this->borrador->concepto_id;
            $parte->responsable_id       = $this->borrador->responsable_id;
            $parte->tipo_hora            = $tipoHora instanceof TipoHora ? $tipoHora->value : (string) $tipoHora;
            $parte->tiene_plus_retencion = $plusReten;
            $parte->observaciones        = $this->borrador->observaciones;
            $parte->save();

            foreach ($this->borrador->lineasPersonal as $linea) {
                if ($linea->trabajador_id !== null) {
                    $parte->lineasPersonal()->create([
                        'trabajador_id' => $linea->trabajador_id,
                        'horas'         => $linea->horas,
                        'horas_extra'   => $linea->horas_extra,
                    ]);
                }
            }
            foreach ($this->borrador->lineasMaterial as $linea) {
                if ($linea->material_id !== null) {
                    $parte->lineasMaterial()->create([
                        'material_id' => $linea->material_id,
                        'cantidad'    => $linea->cantidad,
                    ]);
                }
            }

            $datosBorrador = [
                'estado'                => 'convertido',
                'convertido_a_parte_id' => $parte->id,
            ];

            if ($this->borrador->crear_albaran) {
                // ── …y de él nace el albarán, enlazados (servicio único) ───
                $albaran = app(GeneradorAlbaran::class)->desdeParte($parte);
                $datosBorrador['convertido_a_albaran_id'] = $albaran->id;
                $this->borrador->update($datosBorrador);

                session()->flash('status', "Borrador convertido. Parte «{$parte->numero}» y albarán «{$albaran->numero}» creados.");
                $this->redirectRoute('albaranes.editar', ['albaran' => $albaran->getKey()], navigate: true);
            } else {
                $this->borrador->update($datosBorrador);

                session()->flash('status', "Borrador convertido. Parte «{$parte->numero}» creado correctamente.");
                $this->redirectRoute('partes.editar', ['parte' => $parte->getKey()], navigate: true);
            }
        });
    }

    public function render(): View
    {
        return view('livewire.borradores.ver');
    }
}
