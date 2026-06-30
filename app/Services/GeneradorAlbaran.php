<?php

namespace App\Services;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
use App\Models\Parte;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Fuente ÚNICA de verdad para crear un albarán a partir de un parte.
 *
 * Regla de negocio: ningún albarán existe sin su parte. Siempre se crea el
 * parte primero (la BASE congelada) y de él nace el albarán, enlazados. A
 * partir de ese momento evolucionan por separado: el albarán es el documento
 * vivo y editable, el parte queda cerrado e inmutable mientras tenga albarán.
 *
 * Toda vía que genere un albarán (parte → albarán, borrador → albarán, alta
 * web de albarán) debe pasar por aquí para que la regla se cumpla por
 * construcción y no se repita la lógica en cada pantalla.
 */
class GeneradorAlbaran
{
    public function desdeParte(Parte $parte): Albaran
    {
        // Idempotente: si el parte ya tiene albarán, no duplicar.
        if ($parte->albaran_id !== null) {
            return $parte->albaran()->firstOrFail();
        }

        return DB::transaction(function () use ($parte): Albaran {
            $parte->load(['lineasPersonal', 'lineasMaterial']);

            $albaran = Albaran::create([
                'numero'                   => app(NumeracionService::class)->siguienteNumeroAlbaran(Carbon::parse($parte->fecha)),
                'fecha'                    => $parte->fecha,
                'cliente_id'               => $parte->cliente_id,
                'proyecto_id'              => $parte->proyecto_id,
                'concepto_id'              => $parte->concepto_id,
                'creado_por'               => $parte->creado_por ?? (int) Auth::id(),
                'responsable_id'           => $parte->responsable_id,
                'estado'                   => EstadoAlbaran::PENDIENTE_FIRMA,
                'tipo_hora'                => $parte->tipo_hora ?? TipoHora::LABORAL,
                'tiene_plus_retencion'     => (bool) $parte->tiene_plus_retencion,
                'observaciones'            => $parte->observaciones,
                'es_personalizado'         => (bool) $parte->es_personalizado,
                'cliente_texto'            => $parte->cliente_texto,
                'proyecto_texto'           => $parte->proyecto_texto,
                'concepto_texto'           => $parte->concepto_texto,
                'responsable_texto'        => $parte->responsable_texto,
                'firma_trabajador_user_id' => $parte->creado_por,
            ]);

            foreach ($parte->lineasPersonal as $linea) {
                AlbaranLineaPersonal::create([
                    'albaran_id'    => $albaran->id,
                    'trabajador_id' => $linea->trabajador_id,
                    'horas'         => $linea->horas,
                    'horas_extra'   => $linea->horas_extra,
                ]);
            }

            foreach ($parte->lineasMaterial as $linea) {
                AlbaranLineaMaterial::create([
                    'albaran_id'  => $albaran->id,
                    'material_id' => $linea->material_id,
                    'cantidad'    => $linea->cantidad,
                ]);
            }

            $parte->albaran_id = $albaran->id;
            $parte->estado     = Parte::ESTADO_CERRADO;
            $parte->save();

            return $albaran;
        });
    }
}
