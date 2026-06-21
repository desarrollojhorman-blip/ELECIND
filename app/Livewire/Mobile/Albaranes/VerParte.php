<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
use App\Models\Parte;
use App\Services\NumeracionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class VerParte extends Component
{
    public Parte $parte;

    public bool $confirmarGenerar = false;

    public function abrirConfirmarGenerar(): void
    {
        $this->confirmarGenerar = true;
    }

    public function cancelarGenerar(): void
    {
        $this->confirmarGenerar = false;
    }

    public function generarAlbaran(): void
    {
        if ($this->parte->tieneAlbaran()) {
            $this->confirmarGenerar = false;
            return;
        }

        $albaran = DB::transaction(function (): Albaran {
            $numero = app(NumeracionService::class)->siguienteNumeroAlbaran(Carbon::parse($this->parte->fecha));

            $albaran = Albaran::create([
                'numero'               => $numero,
                'fecha'                => $this->parte->fecha,
                'cliente_id'           => $this->parte->cliente_id,
                'proyecto_id'          => $this->parte->proyecto_id,
                'concepto_id'          => $this->parte->concepto_id,
                'creado_por'           => $this->parte->creado_por ?? (int) Auth::id(),
                'responsable_id'       => $this->parte->responsable_id,
                'estado'               => EstadoAlbaran::PENDIENTE_FIRMA,
                'tipo_hora'            => TipoHora::from($this->parte->tipo_hora->value ?? 'laboral'),
                'tiene_plus_retencion' => (bool) $this->parte->tiene_plus_retencion,
                'observaciones'        => $this->parte->observaciones,
                'es_personalizado'     => $this->parte->es_personalizado,
                'cliente_texto'        => $this->parte->cliente_texto,
                'proyecto_texto'       => $this->parte->proyecto_texto,
                'concepto_texto'       => $this->parte->concepto_texto,
                'responsable_texto'    => $this->parte->responsable_texto,
            ]);

            foreach ($this->parte->lineasPersonal as $linea) {
                AlbaranLineaPersonal::create([
                    'albaran_id'    => $albaran->id,
                    'trabajador_id' => $linea->trabajador_id,
                    'horas'         => $linea->horas,
                    'horas_extra'   => $linea->horas_extra,
                ]);
            }

            foreach ($this->parte->lineasMaterial as $linea) {
                AlbaranLineaMaterial::create([
                    'albaran_id' => $albaran->id,
                    'material_id' => $linea->material_id,
                    'cantidad'    => $linea->cantidad,
                ]);
            }

            $this->parte->albaran_id = $albaran->id;
            $this->parte->estado     = Parte::ESTADO_CERRADO;
            $this->parte->save();

            return $albaran;
        });

        $this->redirectRoute('mobile.albaranes.firmar', ['albaran' => $albaran->id], navigate: false);
    }

    public function mount(Parte $parte): void
    {
        Gate::authorize('view', $parte);

        $this->parte = $parte->load([
            'cliente:id,nombre',
            'proyecto:id,nombre',
            'concepto:id,nombre',
            'creador:id,nombre,apellidos',
            'lineasPersonal.trabajador:id,nombre,apellidos',
            'lineasMaterial.material:id,descripcion,unidad_medida',
        ]);
    }

    public function render(): View
    {
        return view('livewire.mobile.albaranes.ver-parte')
            ->layout('components.layouts.mobile', [
                'title'     => $this->parte->numero,
                'showBack'  => true,
                'backRoute' => route('mobile.albaranes.index'),
            ]);
    }
}
