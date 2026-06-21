<?php

namespace App\Livewire\Forms;

use App\Models\Borrador;
use App\Models\BorradorLineaMaterial;
use App\Models\BorradorLineaPersonal;
use App\Services\NumeracionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BorradorForm extends Form
{
    public ?int $id = null;

    /* ───── Cabecera ─────────────────────────────────────────────── */

    public ?int    $proyecto_id    = null;
    public ?string $proyecto_texto = null;

    public ?int    $cliente_id    = null;
    public ?string $cliente_texto = null;

    public ?int    $concepto_id    = null;
    public ?string $concepto_texto = null;

    public ?int    $responsable_id    = null;
    public ?string $responsable_texto = null;

    public string  $fecha     = '';
    public string  $tipo_hora = 'laboral';
    public ?string $observaciones = null;
    public bool    $tienesPlusRetencion = false;
    public bool    $crearAlbaran        = false;

    /* ───── Firma ────────────────────────────────────────────────── */

    public ?int    $firma_trabajador_user_id      = null;
    public ?string $firma_trabajador_otro_nombre  = null;
    public ?string $firma_trabajador_otro_correo  = null;
    public ?string $firma_responsable_otro_nombre = null;
    public ?string $firma_responsable_otro_correo = null;

    /* ───── Líneas ───────────────────────────────────────────────── */

    /**
     * @var array<int, array{trabajador_id: ?int, trabajador_texto: ?string, horas: string, horas_extra: string}>
     */
    public array $lineasPersonal = [];

    /**
     * @var array<int, array{material_id: ?int, material_texto: ?string, cantidad: string}>
     */
    public array $lineasMaterial = [];

    /* ───── Reglas ───────────────────────────────────────────────── */

    public function rules(): array
    {
        return [
            'fecha'     => ['required', 'date'],
            'tipo_hora' => ['required', Rule::in(['laboral', 'laboral_noche', 'festivo', 'festivo_noche'])],
            'observaciones' => ['nullable', 'string', 'max:2000'],

            // Proyecto: o FK o texto libre (al menos uno si se informa)
            'proyecto_id'    => ['nullable', 'integer', 'exists:proyectos,id'],
            'proyecto_texto' => ['nullable', 'string', 'max:255'],

            // Cliente: o FK o texto libre
            'cliente_id'    => ['nullable', 'integer', 'exists:clientes,id'],
            'cliente_texto' => ['nullable', 'string', 'max:255'],

            // Concepto: o FK o texto libre
            'concepto_id'    => ['nullable', 'integer', 'exists:conceptos,id'],
            'concepto_texto' => ['nullable', 'string', 'max:255'],

            'responsable_id'    => ['nullable', 'integer', 'exists:users,id'],
            'responsable_texto' => ['nullable', 'string', 'max:255'],

            'lineasPersonal'                   => ['array'],
            'lineasPersonal.*.trabajador_id'   => ['nullable', 'integer', 'exists:users,id'],
            'lineasPersonal.*.trabajador_texto' => ['nullable', 'string', 'max:255'],
            'lineasPersonal.*.horas'           => ['required', 'numeric', 'min:0', 'max:24'],
            'lineasPersonal.*.horas_extra'     => ['required', 'numeric', 'min:0', 'max:24'],

            'lineasMaterial'                   => ['array'],
            'lineasMaterial.*.material_id'     => ['nullable', 'integer', 'exists:materiales,id'],
            'lineasMaterial.*.material_texto'  => ['nullable', 'string', 'max:255'],
            'lineasMaterial.*.cantidad'        => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required'                      => 'La fecha es obligatoria.',
            'tipo_hora.required'                  => 'Indica el tipo de jornada.',
            'lineasPersonal.*.horas.required'     => 'Indica las horas del trabajador.',
            'lineasPersonal.*.horas.max'          => 'Las horas no pueden superar 24.',
            'lineasMaterial.*.cantidad.required'  => 'Indica la cantidad.',
            'lineasMaterial.*.cantidad.min'       => 'La cantidad debe ser mayor que 0.',
        ];
    }

    /* ───── Poblar desde modelo ──────────────────────────────────── */

    public function fromModel(Borrador $borrador): void
    {
        $this->id             = (int) $borrador->getKey();
        $this->proyecto_id    = $borrador->proyecto_id;
        $this->proyecto_texto = $borrador->proyecto_texto;
        $this->cliente_id     = $borrador->cliente_id;
        $this->cliente_texto  = $borrador->cliente_texto;
        $this->concepto_id    = $borrador->concepto_id;
        $this->concepto_texto = $borrador->concepto_texto;
        $this->responsable_id    = $borrador->responsable_id;
        $this->responsable_texto = $borrador->responsable_texto;
        $this->fecha          = Carbon::parse($borrador->fecha)->format('Y-m-d');
        $this->tipo_hora      = $borrador->tipo_hora instanceof \App\Enums\TipoHora
            ? $borrador->tipo_hora->value
            : (string) $borrador->tipo_hora;
        $this->observaciones          = $borrador->observaciones;
        $this->tienesPlusRetencion    = (bool) $borrador->tiene_plus_retencion;
        $this->crearAlbaran           = (bool) $borrador->crear_albaran;

        $this->firma_trabajador_user_id      = $borrador->firma_trabajador_user_id;
        $this->firma_trabajador_otro_nombre  = $borrador->firma_trabajador_otro_nombre;
        $this->firma_trabajador_otro_correo  = $borrador->firma_trabajador_otro_correo;
        $this->firma_responsable_otro_nombre = $borrador->firma_responsable_otro_nombre;
        $this->firma_responsable_otro_correo = $borrador->firma_responsable_otro_correo;

        $this->lineasPersonal = $borrador->lineasPersonal
            ->map(fn (BorradorLineaPersonal $l): array => [
                'trabajador_id'    => $l->trabajador_id,
                'trabajador_texto' => $l->trabajador_texto,
                'horas'            => (string) $l->horas,
                'horas_extra'      => (string) $l->horas_extra,
            ])
            ->values()
            ->all();

        $this->lineasMaterial = $borrador->lineasMaterial
            ->map(fn (BorradorLineaMaterial $l): array => [
                'material_id'    => $l->material_id,
                'material_texto' => $l->material_texto,
                'cantidad'       => (string) $l->cantidad,
            ])
            ->values()
            ->all();
    }

    /* ───── Gestión de líneas ────────────────────────────────────── */

    public function addLineaPersonal(): void
    {
        $this->lineasPersonal[] = [
            'trabajador_id'    => null,
            'trabajador_texto' => null,
            'horas'            => '8.00',
            'horas_extra'      => '0.00',
        ];
    }

    public function removeLineaPersonal(int $index): void
    {
        unset($this->lineasPersonal[$index]);
        $this->lineasPersonal = array_values($this->lineasPersonal);
    }

    public function addLineaMaterial(): void
    {
        $this->lineasMaterial[] = [
            'material_id'    => null,
            'material_texto' => null,
            'cantidad'       => '1.00',
        ];
    }

    public function removeLineaMaterial(int $index): void
    {
        unset($this->lineasMaterial[$index]);
        $this->lineasMaterial = array_values($this->lineasMaterial);
    }

    /* ───── Guardar ──────────────────────────────────────────────── */

    public function save(): Borrador
    {
        $this->validate();

        return DB::transaction(function (): Borrador {
            $esNuevo = $this->id === null;

            if ($esNuevo) {
                $borrador = new Borrador;
                $borrador->numero_borrador = app(NumeracionService::class)->siguienteNumeroBorrador();
                $borrador->creado_por      = (int) Auth::id();
                $borrador->estado          = 'pendiente';
            } else {
                /** @var Borrador $borrador */
                $borrador = Borrador::findOrFail($this->id);
            }

            $borrador->proyecto_id    = $this->proyecto_id;
            $borrador->proyecto_texto = $this->proyecto_id ? null : $this->proyecto_texto;
            $borrador->cliente_id     = $this->cliente_id;
            $borrador->cliente_texto  = $this->cliente_id ? null : $this->cliente_texto;
            $borrador->concepto_id    = $this->concepto_id;
            $borrador->concepto_texto = $this->concepto_id ? null : $this->concepto_texto;
            $borrador->responsable_id    = $this->responsable_id;
            $borrador->responsable_texto = $this->responsable_id ? null : $this->responsable_texto;
            $borrador->fecha                 = Carbon::parse($this->fecha);
            $borrador->tipo_hora             = $this->tipo_hora;
            $borrador->observaciones         = $this->observaciones;
            $borrador->tiene_plus_retencion  = $this->tienesPlusRetencion;
            $borrador->crear_albaran         = $this->crearAlbaran;

            $borrador->firma_trabajador_user_id      = $this->firma_trabajador_user_id ?: null;
            $borrador->firma_trabajador_otro_nombre  = $this->firma_trabajador_otro_nombre ?: null;
            $borrador->firma_trabajador_otro_correo  = $this->firma_trabajador_otro_correo ?: null;
            $borrador->firma_responsable_otro_nombre = $this->firma_responsable_otro_nombre ?: null;
            $borrador->firma_responsable_otro_correo = $this->firma_responsable_otro_correo ?: null;

            $borrador->save();

            // Sincronizar líneas de personal
            $borrador->lineasPersonal()->delete();
            foreach ($this->lineasPersonal as $linea) {
                $borrador->lineasPersonal()->create([
                    'trabajador_id'    => $linea['trabajador_id'] ?: null,
                    'trabajador_texto' => $linea['trabajador_id'] ? null : ($linea['trabajador_texto'] ?: null),
                    'horas'            => $linea['horas'],
                    'horas_extra'      => $linea['horas_extra'],
                ]);
            }

            // Sincronizar líneas de material
            $borrador->lineasMaterial()->delete();
            foreach ($this->lineasMaterial as $linea) {
                $borrador->lineasMaterial()->create([
                    'material_id'    => $linea['material_id'] ?: null,
                    'material_texto' => $linea['material_id'] ? null : ($linea['material_texto'] ?: null),
                    'cantidad'       => $linea['cantidad'],
                ]);
            }

            return $borrador;
        });
    }
}
