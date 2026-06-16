<?php

namespace App\Observers;

use App\Models\AtributoHora;
use App\Models\TarifaHistorial;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Observer de tasas del trabajador.
 *
 * Cada vez que se modifique alguno de los 8 campos `tasa_*` de un User,
 * registra una fila en `tarifas_historial` con tipo='trabajador' por cada
 * campo cambiado.
 *
 * El `atributo_id` se obtiene del catálogo via mapeo_tasa (mapeo inverso).
 * Para evitar consultas N+1 en bulk updates, cachea los atributos por mapeo.
 */
class UserTasasObserver
{
    /** @var array<string, int> map de campo → atributo_id (cargado bajo demanda) */
    private array $cacheAtributos = [];

    private const CAMPOS_TASA = [
        'tasa_hora',
        'tasa_lab_noche',
        'tasa_festivo',
        'tasa_fest_noche',
        'tasa_extra',
        'tasa_ex_lab_noc',
        'tasa_ex_fes',
        'tasa_ex_fes_noct',
    ];

    public function updated(User $user): void
    {
        $userId = Auth::id();

        foreach (self::CAMPOS_TASA as $campo) {
            if (! $user->isDirty($campo)) {
                continue;
            }

            $atributoId = $this->atributoIdParaCampo($campo);
            if ($atributoId === null) {
                continue;
            }

            TarifaHistorial::create([
                'tipo' => TarifaHistorial::TIPO_TRABAJADOR,
                'referencia_id' => $user->id,
                'atributo_id' => $atributoId,
                'importe_anterior' => (float) $user->getOriginal($campo),
                'importe_nuevo' => (float) $user->{$campo},
                'cambiado_por' => $userId,
            ]);
        }
    }

    private function atributoIdParaCampo(string $campo): ?int
    {
        if (! isset($this->cacheAtributos[$campo])) {
            $atributo = AtributoHora::porMapeoTasa($campo);
            if ($atributo === null) {
                return null;
            }
            $this->cacheAtributos[$campo] = $atributo->id;
        }

        return $this->cacheAtributos[$campo];
    }
}
