<?php

namespace App\Livewire\Forms;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoDia;
use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
use App\Models\Proyecto;
use App\Services\NumeracionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Formulario de creación y edición de un albarán desde móvil.
 *
 * Estructura:
 *  - Cabecera: cliente, proyecto, concepto, responsable, fecha, tipo_dia, observaciones.
 *  - Mis horas (línea de personal del creador, siempre presente).
 *    Tiene "horas" (normales) y "horas_extra".
 *  - Compañeros: array de líneas adicionales (cada una con horas + horas_extra).
 *  - Materiales: array de líneas (material + cantidad).
 *
 * El stock se descuenta automáticamente vía AlbaranLineaMaterialObserver
 * (que ajusta directamente material.stock — los lotes ya no existen).
 */
class AlbaranForm extends Form
{
    public ?int $id = null;

    /* ───── Cabecera ────────────────────────────────────────────────── */

    public ?int $proyecto_id = null;

    public ?int $cliente_id = null;

    public ?int $concepto_id = null;

    public ?int $responsable_id = null;

    public string $fecha = '';

    public string $tipo_dia = 'laborable';

    public ?string $observaciones = null;

    /* ───── Mis horas (línea personal del creador) ──────────────────── */

    public string $mi_horas = '8.00';

    public string $mi_horas_extra = '0.00';

    public ?string $mi_observaciones = null;

    /**
     * Líneas de personal de los compañeros (sin contar al creador).
     *
     * @var array<int, array{trabajador_id: ?int, horas: string, horas_extra: string, observaciones: ?string}>
     */
    public array $companeros = [];

    /**
     * Líneas de material.
     *
     * @var array<int, array{material_id: ?int, cantidad: string, observaciones: ?string}>
     */
    public array $materiales = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'proyecto_id' => ['required', 'integer', 'exists:proyectos,id'],
            'concepto_id' => ['nullable', 'integer', 'exists:conceptos,id'],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'fecha' => ['required', 'date'],
            'tipo_dia' => ['required', Rule::in(array_column(TipoDia::cases(), 'value'))],
            'observaciones' => ['nullable', 'string', 'max:2000'],

            'mi_horas' => ['required', 'numeric', 'min:0', 'max:24'],
            'mi_horas_extra' => ['required', 'numeric', 'min:0', 'max:24'],
            'mi_observaciones' => ['nullable', 'string', 'max:255'],

            'companeros' => ['array'],
            'companeros.*.trabajador_id' => [
                'required', 'integer', 'exists:users,id',
                Rule::notIn([(int) Auth::id()]),
            ],
            'companeros.*.horas' => ['required', 'numeric', 'min:0', 'max:24'],
            'companeros.*.horas_extra' => ['required', 'numeric', 'min:0', 'max:24'],
            'companeros.*.observaciones' => ['nullable', 'string', 'max:255'],

            'materiales' => ['array'],
            'materiales.*.material_id' => ['required', 'integer', 'exists:materiales,id'],
            'materiales.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'materiales.*.observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proyecto_id.required' => 'Selecciona un proyecto.',
            'fecha.required' => 'La fecha es obligatoria.',
            'tipo_dia.required' => 'Indica si es laborable o festivo.',
            'mi_horas.max' => 'Las horas no pueden superar 24.',
            'mi_horas_extra.max' => 'Las horas extra no pueden superar 24.',
            'companeros.*.trabajador_id.not_in' => 'Tú ya estás incluido como creador del parte; añade otros compañeros.',
            'companeros.*.trabajador_id.required' => 'Selecciona un compañero o elimina la línea.',
            'companeros.*.horas.required' => 'Indica las horas del compañero.',
            'materiales.*.material_id.required' => 'Selecciona un material.',
            'materiales.*.cantidad.required' => 'Indica la cantidad de material.',
        ];
    }

    public function fromModel(Albaran $albaran): void
    {
        $this->id = (int) $albaran->getKey();
        $this->proyecto_id = $albaran->proyecto_id;
        $this->cliente_id = $albaran->cliente_id;
        $this->concepto_id = $albaran->concepto_id;
        $this->responsable_id = $albaran->responsable_id;
        $this->fecha = Carbon::parse($albaran->fecha)->format('Y-m-d');
        $this->tipo_dia = $albaran->tipo_dia->value;
        $this->observaciones = $albaran->observaciones;

        $miId = (int) Auth::id();
        $miLinea = $albaran->lineasPersonal->firstWhere('trabajador_id', $miId);
        if ($miLinea !== null) {
            $this->mi_horas = (string) $miLinea->horas;
            $this->mi_horas_extra = (string) $miLinea->horas_extra;
            $this->mi_observaciones = $miLinea->observaciones;
        }

        $this->companeros = $albaran->lineasPersonal
            ->where('trabajador_id', '!=', $miId)
            ->map(fn (AlbaranLineaPersonal $linea): array => [
                'trabajador_id' => $linea->trabajador_id,
                'horas' => (string) $linea->horas,
                'horas_extra' => (string) $linea->horas_extra,
                'observaciones' => $linea->observaciones,
            ])
            ->values()
            ->all();

        $this->materiales = $albaran->lineasMaterial
            ->map(fn (AlbaranLineaMaterial $linea): array => [
                'material_id' => $linea->material_id,
                'cantidad' => (string) $linea->cantidad,
                'observaciones' => $linea->observaciones,
            ])
            ->values()
            ->all();
    }

    public function addCompanero(): void
    {
        $this->companeros[] = [
            'trabajador_id' => null,
            'horas' => '8.00',
            'horas_extra' => '0.00',
            'observaciones' => null,
        ];
    }

    public function removeCompanero(int $index): void
    {
        unset($this->companeros[$index]);
        $this->companeros = array_values($this->companeros);
    }

    public function addMaterial(): void
    {
        $this->materiales[] = [
            'material_id' => null,
            'cantidad' => '1.00',
            'observaciones' => null,
        ];
    }

    public function removeMaterial(int $index): void
    {
        unset($this->materiales[$index]);
        $this->materiales = array_values($this->materiales);
    }

    /**
     * Sincroniza cliente_id desde el proyecto seleccionado.
     */
    public function sincronizarClienteDesdeProyecto(): void
    {
        if ($this->proyecto_id === null) {
            $this->cliente_id = null;
            $this->responsable_id = null;
            $this->concepto_id = null;
            $this->materiales = [];
            $this->companeros = [];

            return;
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()->find($this->proyecto_id);

        if ($proyecto === null) {
            return;
        }

        $this->cliente_id = $proyecto->cliente_id;

        if ($this->responsable_id === null) {
            $this->responsable_id = $proyecto->responsable_principal_id;
        }
    }

    public function save(): Albaran
    {
        $this->validate();

        return DB::transaction(function (): Albaran {
            $esNuevo = $this->id === null;

            if ($esNuevo) {
                $albaran = new Albaran;
                $albaran->numero = app(NumeracionService::class)->siguienteNumeroAlbaran(Carbon::parse($this->fecha));
                $albaran->creado_por = (int) Auth::id();
                $albaran->estado = EstadoAlbaran::BORRADOR;
            } else {
                /** @var Albaran $albaran */
                $albaran = Albaran::findOrFail($this->id);
            }

            $albaran->fecha = Carbon::parse($this->fecha);
            $albaran->cliente_id = (int) $this->cliente_id;
            $albaran->proyecto_id = $this->proyecto_id;
            $albaran->concepto_id = $this->concepto_id;
            $albaran->responsable_id = $this->responsable_id;
            $albaran->tipo_dia = TipoDia::from($this->tipo_dia);
            $albaran->observaciones = $this->observaciones;
            $albaran->save();

            // Líneas personal: borramos todas y recreamos para simplicidad.
            // El Observer no actúa sobre lineas personal, así que es seguro.
            $albaran->lineasPersonal()->delete();

            $albaran->lineasPersonal()->create([
                'trabajador_id' => Auth::id(),
                'horas' => $this->mi_horas,
                'horas_extra' => $this->mi_horas_extra,
                'observaciones' => $this->mi_observaciones,
            ]);

            foreach ($this->companeros as $companero) {
                $albaran->lineasPersonal()->create([
                    'trabajador_id' => $companero['trabajador_id'],
                    'horas' => $companero['horas'],
                    'horas_extra' => $companero['horas_extra'] ?? '0.00',
                    'observaciones' => $companero['observaciones'] ?? null,
                ]);
            }

            // Líneas material: borramos una a una para que el Observer
            // devuelva stock al material correspondiente.
            $albaran->lineasMaterial()->each(fn ($linea) => $linea->delete());

            foreach ($this->materiales as $material) {
                $albaran->lineasMaterial()->create([
                    'material_id' => $material['material_id'],
                    'cantidad' => $material['cantidad'],
                    'observaciones' => $material['observaciones'] ?? null,
                ]);
            }

            return $albaran->fresh(['lineasPersonal', 'lineasMaterial']);
        });
    }
}
