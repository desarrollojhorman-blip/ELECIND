<?php

namespace Database\Seeders;

use App\Models\AtributoHora;
use Illuminate\Database\Seeder;

/**
 * Siembra los 11 atributos imputables fijos del sistema.
 *
 * Idempotente: usa updateOrCreate por código, así que se puede ejecutar
 * varias veces sin duplicar.
 *
 * Los 11 son los confirmados en el Excel del cliente (hoja TARIFAS + INPUT):
 *   - 4 normales (Labor, Lab Noche, Fest, Fest Noct)
 *   - 4 extras (Ex Lab, Ex Lab Noc, Ex Fes, Ex Fes Noct)
 *   - 3 pluses (PLUS RETEN, PLUS FESTIVO, PLUS NOCHE)
 *
 * `mapeo_tasa` apunta al campo de users.tasa_* que aplica para calcular el
 * coste al trabajador. Los pluses lo dejan en NULL porque su coste se decidirá
 * en Fase 1.5 (pendiente de respuesta del cliente).
 */
class AtributosHoraSeeder extends Seeder
{
    public function run(): void
    {
        $atributos = [
            // ─── Normales ─────────────────────────────────────────────
            [
                'codigo' => AtributoHora::COD_LABOR,
                'nombre_corto' => 'Labor',
                'nombre_largo' => 'Hora laboral diurna',
                'grupo' => AtributoHora::GRUPO_NORMAL,
                'mapeo_tasa' => 'tasa_hora',
                'orden' => 1,
            ],
            [
                'codigo' => AtributoHora::COD_LAB_NOCHE,
                'nombre_corto' => 'Lab Noche',
                'nombre_largo' => 'Hora laboral nocturna',
                'grupo' => AtributoHora::GRUPO_NORMAL,
                'mapeo_tasa' => 'tasa_lab_noche',
                'orden' => 2,
            ],
            [
                'codigo' => AtributoHora::COD_FEST,
                'nombre_corto' => 'Fest',
                'nombre_largo' => 'Hora festiva diurna',
                'grupo' => AtributoHora::GRUPO_NORMAL,
                'mapeo_tasa' => 'tasa_festivo',
                'orden' => 3,
            ],
            [
                'codigo' => AtributoHora::COD_FEST_NOCT,
                'nombre_corto' => 'Fest Noct',
                'nombre_largo' => 'Hora festiva nocturna',
                'grupo' => AtributoHora::GRUPO_NORMAL,
                'mapeo_tasa' => 'tasa_fest_noche',
                'orden' => 4,
            ],

            // ─── Extras ───────────────────────────────────────────────
            [
                'codigo' => AtributoHora::COD_EX_LAB,
                'nombre_corto' => 'Ex Lab',
                'nombre_largo' => 'Hora extra laboral diurna',
                'grupo' => AtributoHora::GRUPO_EXTRA,
                'mapeo_tasa' => 'tasa_extra',
                'orden' => 5,
            ],
            [
                'codigo' => AtributoHora::COD_EX_LAB_NOC,
                'nombre_corto' => 'Ex Lab Noc',
                'nombre_largo' => 'Hora extra laboral nocturna',
                'grupo' => AtributoHora::GRUPO_EXTRA,
                'mapeo_tasa' => 'tasa_ex_lab_noc',
                'orden' => 6,
            ],
            [
                'codigo' => AtributoHora::COD_EX_FES,
                'nombre_corto' => 'Ex Fes',
                'nombre_largo' => 'Hora extra festiva diurna',
                'grupo' => AtributoHora::GRUPO_EXTRA,
                'mapeo_tasa' => 'tasa_ex_fes',
                'orden' => 7,
            ],
            [
                'codigo' => AtributoHora::COD_EX_FES_NOCT,
                'nombre_corto' => 'Ex Fes Noct',
                'nombre_largo' => 'Hora extra festiva nocturna',
                'grupo' => AtributoHora::GRUPO_EXTRA,
                'mapeo_tasa' => 'tasa_ex_fes_noct',
                'orden' => 8,
            ],

            // ─── Pluses (mapeo_tasa NULL — pendiente confirmar) ──────
            [
                'codigo' => AtributoHora::COD_PLUS_RETEN,
                'nombre_corto' => 'Plus Retén',
                'nombre_largo' => 'Plus de retén (guardia/disponibilidad)',
                'grupo' => AtributoHora::GRUPO_PLUS,
                'mapeo_tasa' => 'tasa_plus_reten',
                'orden' => 9,
            ],
            [
                'codigo' => AtributoHora::COD_PLUS_FESTIVO,
                'nombre_corto' => 'Plus Festivo',
                'nombre_largo' => 'Plus por trabajar en festivo',
                'grupo' => AtributoHora::GRUPO_PLUS,
                'mapeo_tasa' => null,
                'orden' => 10,
            ],
            [
                'codigo' => AtributoHora::COD_PLUS_NOCHE,
                'nombre_corto' => 'Plus Noche',
                'nombre_largo' => 'Plus por trabajar en horario nocturno',
                'grupo' => AtributoHora::GRUPO_PLUS,
                'mapeo_tasa' => null,
                'orden' => 11,
            ],
        ];

        foreach ($atributos as $atr) {
            AtributoHora::updateOrCreate(
                ['codigo' => $atr['codigo']],
                $atr,
            );
        }
    }
}
