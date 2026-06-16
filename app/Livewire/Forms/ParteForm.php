<?php

namespace App\Livewire\Forms;

use App\Models\Parte;
use App\Models\ParteLineaPersonal;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

/**
 * Form Object para crear/editar partes.
 *
 * Más simple que BorradorForm: sin texto libre, sin firma, sin materiales,
 * solo personal con atributos del catálogo v2.
 */
class ParteForm extends Form
{
    public ?int $id = null;

    /* ── Cabecera ─────────────────────────────────────────────── */

    public ?int $user_id = null;        // operario que captura

    public ?int $proyecto_id = null;

    public string $fecha = '';

    public ?string $hora_inicio = null;

    public ?string $hora_fin = null;

    public bool $es_albaran = false;

    public ?string $observaciones = null;

    /* ── Líneas ───────────────────────────────────────────────── */

    /**
     * @var array<int, array{user_id: ?int, atributo_id: ?int, cantidad: string, motivo_ajuste: ?string}>
     */
    public array $lineasPersonal = [];

    /* ── Reglas ───────────────────────────────────────────────── */

    public function rules(): array
    {
        return [
            'user_id'      => ['required', 'integer', 'exists:users,id'],
            'proyecto_id'  => ['required', 'integer', 'exists:proyectos,id'],
            'fecha'        => ['required', 'date'],
            'hora_inicio'  => ['nullable', 'date_format:H:i'],
            'hora_fin'     => ['nullable', 'date_format:H:i', 'after_or_equal:hora_inicio'],
            'es_albaran'   => ['boolean'],
            'observaciones' => ['nullable', 'string', 'max:2000'],

            'lineasPersonal'                 => ['array'],
            'lineasPersonal.*.user_id'       => ['nullable', 'integer', 'exists:users,id'],
            'lineasPersonal.*.atributo_id'   => ['nullable', 'integer', 'exists:atributos_hora,id'],
            'lineasPersonal.*.cantidad'      => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'lineasPersonal.*.motivo_ajuste' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'user_id'     => 'operario',
            'proyecto_id' => 'proyecto',
            'fecha'       => 'fecha',
            'hora_inicio' => 'hora inicio',
            'hora_fin'    => 'hora fin',
        ];
    }

    /* ── Fill/Save ───────────────────────────────────────────── */

    public function fromModel(Parte $parte): void
    {
        $this->id            = $parte->id;
        $this->user_id       = $parte->user_id;
        $this->proyecto_id   = $parte->proyecto_id;
        $this->fecha         = $parte->fecha?->toDateString() ?? '';
        $this->hora_inicio   = $parte->hora_inicio
            ? substr((string) $parte->hora_inicio, 0, 5)
            : null;
        $this->hora_fin      = $parte->hora_fin
            ? substr((string) $parte->hora_fin, 0, 5)
            : null;
        $this->es_albaran    = (bool) $parte->es_albaran;
        $this->observaciones = $parte->observaciones;

        $this->lineasPersonal = $parte->lineasPersonal
            ->map(fn (ParteLineaPersonal $l) => [
                'id'            => $l->id,
                'user_id'       => $l->user_id,
                'atributo_id'   => $l->atributo_id,
                'cantidad'      => (string) $l->cantidad,
                'motivo_ajuste' => $l->motivo_ajuste,
            ])
            ->values()
            ->toArray();
    }

    public function save(): Parte
    {
        $this->validate();

        return DB::transaction(function (): Parte {
            $datos = [
                'user_id'      => $this->user_id,
                'proyecto_id'  => $this->proyecto_id,
                'fecha'        => $this->fecha,
                'hora_inicio'  => $this->hora_inicio,
                'hora_fin'     => $this->hora_fin,
                'es_albaran'   => $this->es_albaran,
                'observaciones' => $this->observaciones,
            ];

            if ($this->id === null) {
                $parte = new Parte;
            } else {
                $parte = Parte::findOrFail($this->id);
            }

            $parte->fill($datos);
            $parte->save();

            // Sincronizar líneas: borrar las que ya no estén en el form.
            // El componente Editar/Crear es quien decide el valor de
            // `es_albaran` antes de llamar a save() (al cambiar el proyecto
            // se autorellena desde tipo_proyecto.genera_albaran_por_defecto).
            $idsExistentes = collect($this->lineasPersonal)->pluck('id')->filter()->all();
            if (empty($idsExistentes)) {
                ParteLineaPersonal::where('parte_id', $parte->id)->delete();
            } else {
                ParteLineaPersonal::where('parte_id', $parte->id)
                    ->whereNotIn('id', $idsExistentes)
                    ->delete();
            }

            foreach ($this->lineasPersonal as $row) {
                // Skip filas vacías (sin trabajador o atributo).
                if (empty($row['user_id']) || empty($row['atributo_id'])) {
                    continue;
                }

                $datosLinea = [
                    'parte_id'      => $parte->id,
                    'user_id'       => (int) $row['user_id'],
                    'atributo_id'   => (int) $row['atributo_id'],
                    'cantidad'      => $row['cantidad'] === null || $row['cantidad'] === ''
                        ? 0
                        : (float) str_replace(',', '.', (string) $row['cantidad']),
                    'motivo_ajuste' => $row['motivo_ajuste'] ?? null,
                ];

                if (! empty($row['id'])) {
                    $linea = ParteLineaPersonal::find($row['id']);
                    if ($linea !== null) {
                        $linea->fill($datosLinea);
                        $linea->save();

                        continue;
                    }
                }

                ParteLineaPersonal::create($datosLinea);
            }

            return $parte->fresh(['lineasPersonal']);
        });
    }

    /* ── Helpers para el blade ───────────────────────────────── */

    public function addLineaPersonal(): void
    {
        $this->lineasPersonal[] = [
            'id'            => null,
            'user_id'       => null,
            'atributo_id'   => null,
            'cantidad'      => '',
            'motivo_ajuste' => null,
        ];
    }

    public function removeLineaPersonal(int $index): void
    {
        unset($this->lineasPersonal[$index]);
        $this->lineasPersonal = array_values($this->lineasPersonal);
    }
}
