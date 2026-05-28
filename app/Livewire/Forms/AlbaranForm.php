<?php

namespace App\Livewire\Forms;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
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
 *  - Cabecera: cliente, proyecto, concepto, responsable, fecha, tipo_hora
 *    (laboral / laboral_noche / festivo / festivo_noche), observaciones.
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

    public ?string $numero = null;

    public ?int $proyecto_id = null;

    public ?int $cliente_id = null;

    public ?int $concepto_id = null;

    /* responsable_id también actúa como firmante responsable */
    public ?int $responsable_id = null;

    public string $fecha = '';

    public string $tipo_hora = 'laboral';

    public string $estado = 'pendiente_firma';

    public ?string $observaciones = null;

    /* ───── Firmantes ──────────────────────────────────────────────────── */

    public ?int $firma_trabajador_user_id = null;

    public ?string $firma_trabajador_otro_nombre = null;

    public ?string $firma_trabajador_otro_correo = null;

    public ?string $firma_responsable_otro_nombre = null;

    public ?string $firma_responsable_otro_correo = null;

    /* ───── Mis horas (línea personal del creador — solo móvil) ────────
     * En modo web ($omitirLineaCreador = true) estos campos se ignoran
     * y todas las líneas de personal van en $companeros.
     * ──────────────────────────────────────────────────────────────── */

    /* ───── Parte personalizado ──────────────────────────────────────── */

    public bool $esPersonalizado = false;

    public bool $clienteOtro = false;

    public string $clienteTexto = '';

    public bool $proyectoOtro = false;

    public string $proyectoTexto = '';

    public bool $conceptoOtro = false;

    public string $conceptoTexto = '';

    public bool $responsableOtro = false;

    public string $responsableTexto = '';

    /* ───────────────────────────────────────────────────────────────── */

    public bool $omitirLineaCreador = false;

    public string $mi_horas = '8.00';

    public string $mi_horas_extra = '0.00';

    /**
     * Líneas de personal.
     * - Móvil: compañeros (excluye al creador, que tiene mi_horas/mi_horas_extra).
     * - Web:   todos los trabajadores del parte (ninguno es "el creador" automático).
     *
     * @var array<int, array{trabajador_id: ?int, horas: string, horas_extra: string}>
     */
    public array $companeros = [];

    /**
     * Líneas de material.
     *
     * @var array<int, array{material_id: ?int, cantidad: string}>
     */
    public array $materiales = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => $this->esPersonalizado && ! $this->clienteOtro
                ? ['required', 'integer', 'exists:clientes,id']
                : ['nullable', 'integer', 'exists:clientes,id'],
            'clienteTexto' => $this->esPersonalizado && $this->clienteOtro
                ? ['required', 'string', 'max:200']
                : ['nullable'],
            'proyecto_id' => $this->esPersonalizado && $this->proyectoOtro
                ? ['nullable', 'integer', 'exists:proyectos,id']
                : ['required', 'integer', 'exists:proyectos,id'],
            'proyectoTexto' => $this->esPersonalizado && $this->proyectoOtro
                ? ['required', 'string', 'max:200']
                : ['nullable'],
            'concepto_id' => ['nullable', 'integer', 'exists:conceptos,id'],
            'conceptoTexto' => $this->esPersonalizado && $this->conceptoOtro
                ? ['required', 'string', 'max:200']
                : ['nullable'],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'responsableTexto' => $this->esPersonalizado && $this->responsableOtro
                ? ['required', 'string', 'max:200']
                : ['nullable'],
            'fecha' => ['required', 'date'],
            'tipo_hora' => ['required', Rule::in(array_column(TipoHora::cases(), 'value'))],
            'estado'    => ['required', Rule::in(array_column(EstadoAlbaran::cases(), 'value'))],
            'observaciones' => ['nullable', 'string', 'max:2000'],

            'firma_trabajador_user_id'    => ['nullable', 'integer', 'exists:users,id'],
            'firma_trabajador_otro_nombre' => ['nullable', 'string', 'max:150'],
            'firma_trabajador_otro_correo' => ['nullable', 'email', 'max:150'],
            'firma_responsable_otro_nombre' => ['nullable', 'string', 'max:150'],
            'firma_responsable_otro_correo' => ['nullable', 'email', 'max:150'],

            'mi_horas' => $this->omitirLineaCreador ? ['nullable'] : ['required', 'numeric', 'min:0', 'max:24'],
            'mi_horas_extra' => $this->omitirLineaCreador ? ['nullable'] : ['required', 'numeric', 'min:0', 'max:24'],

            'companeros' => ['array'],
            'companeros.*.trabajador_id' => array_filter([
                'required', 'integer', 'exists:users,id',
                $this->omitirLineaCreador ? null : Rule::notIn([(int) Auth::id()]),
            ]),
            'companeros.*.horas' => ['required', 'numeric', 'min:0', 'max:24'],
            'companeros.*.horas_extra' => ['required', 'numeric', 'min:0', 'max:24'],

            'materiales' => ['array'],
            'materiales.*.material_id' => ['required', 'integer', 'exists:materiales,id'],
            'materiales.*.cantidad' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_id.required'     => 'Selecciona un cliente.',
            'cliente_texto.required'  => 'Escribe el nombre del cliente.',
            'proyecto_id.required'    => 'Selecciona un proyecto.',
            'proyecto_texto.required' => 'Escribe el nombre del proyecto.',
            'concepto_texto.required' => 'Escribe el tipo de trabajo.',
            'responsable_texto.required' => 'Escribe el nombre del responsable.',
            'fecha.required' => 'La fecha es obligatoria.',
            'tipo_hora.required' => 'Indica el tipo de hora del parte.',
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
        $this->numero = $albaran->numero;
        $this->proyecto_id = $albaran->proyecto_id;
        $this->cliente_id = $albaran->cliente_id;
        $this->concepto_id = $albaran->concepto_id;
        $this->responsable_id = $albaran->responsable_id;
        $this->fecha = Carbon::parse($albaran->fecha)->format('Y-m-d');
        $this->tipo_hora = $albaran->tipo_hora->value;
        $this->estado = $albaran->estado->value;
        $this->observaciones = $albaran->observaciones;
        $this->firma_trabajador_user_id    = $albaran->firma_trabajador_user_id;
        $this->firma_trabajador_otro_nombre = $albaran->firma_trabajador_otro_nombre;
        $this->firma_trabajador_otro_correo = $albaran->firma_trabajador_otro_correo;
        $this->firma_responsable_otro_nombre = $albaran->firma_responsable_otro_nombre;
        $this->firma_responsable_otro_correo = $albaran->firma_responsable_otro_correo;

        $miId = (int) Auth::id();

        if ($this->omitirLineaCreador) {
            // Web: todas las líneas van como companeros, sin distinción de creador.
            $this->companeros = $albaran->lineasPersonal
                ->map(fn (AlbaranLineaPersonal $linea): array => [
                    'trabajador_id' => $linea->trabajador_id,
                    'horas' => (string) $linea->horas,
                    'horas_extra' => (string) $linea->horas_extra,
                ])
                ->values()
                ->all();
        } else {
            // Móvil: la línea del creador va en mi_horas; el resto en companeros.
            $miLinea = $albaran->lineasPersonal->firstWhere('trabajador_id', $miId);
            if ($miLinea !== null) {
                $this->mi_horas = (string) $miLinea->horas;
                $this->mi_horas_extra = (string) $miLinea->horas_extra;
            }

            $this->companeros = $albaran->lineasPersonal
                ->where('trabajador_id', '!=', $miId)
                ->map(fn (AlbaranLineaPersonal $linea): array => [
                    'trabajador_id' => $linea->trabajador_id,
                    'horas' => (string) $linea->horas,
                    'horas_extra' => (string) $linea->horas_extra,
                ])
                ->values()
                ->all();
        }

        $this->materiales = $albaran->lineasMaterial
            ->map(fn (AlbaranLineaMaterial $linea): array => [
                'material_id' => $linea->material_id,
                'cantidad' => (string) $linea->cantidad,
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
            $this->firma_trabajador_user_id = null;
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
        $this->responsable_id = $proyecto->responsable_principal_id;
        $this->concepto_id = null;
        $this->companeros = [];
        $this->materiales = [];
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
                $albaran->estado = EstadoAlbaran::PENDIENTE_FIRMA;
                // En móvil el creador es siempre el firmante trabajador
                if (! $this->omitirLineaCreador) {
                    $this->firma_trabajador_user_id = (int) Auth::id();
                }
            } else {
                /** @var Albaran $albaran */
                $albaran = Albaran::findOrFail($this->id);
            }

            $albaran->fecha = Carbon::parse($this->fecha);
            $albaran->cliente_id = $this->cliente_id ? (int) $this->cliente_id : null;
            $albaran->proyecto_id = $this->proyecto_id;
            $albaran->es_personalizado = $this->esPersonalizado;
            $albaran->cliente_texto = $this->esPersonalizado && $this->clienteOtro ? trim($this->clienteTexto) : null;
            $albaran->proyecto_texto = $this->esPersonalizado && $this->proyectoOtro ? trim($this->proyectoTexto) : null;
            $albaran->concepto_texto = $this->esPersonalizado && $this->conceptoOtro ? trim($this->conceptoTexto) : null;
            $albaran->responsable_texto = $this->esPersonalizado && $this->responsableOtro ? trim($this->responsableTexto) : null;
            $albaran->concepto_id = $this->concepto_id;
            $albaran->responsable_id = $this->responsable_id;
            $albaran->firma_trabajador_user_id    = $this->firma_trabajador_user_id;
            $albaran->firma_trabajador_otro_nombre = $this->firma_trabajador_otro_nombre ?: null;
            $albaran->firma_trabajador_otro_correo = $this->firma_trabajador_otro_correo ?: null;
            $albaran->firma_responsable_otro_nombre = $this->firma_responsable_otro_nombre ?: null;
            $albaran->firma_responsable_otro_correo = $this->firma_responsable_otro_correo ?: null;
            $albaran->tipo_hora = TipoHora::from($this->tipo_hora);
            $albaran->estado = EstadoAlbaran::from($this->estado);
            $albaran->observaciones = $this->observaciones;
            $albaran->save();

            if (! $this->omitirLineaCreador) {
                // Móvil: gestiona las líneas como parte del formulario.
                $albaran->lineasPersonal()->delete();

                $albaran->lineasPersonal()->create([
                    'trabajador_id' => Auth::id(),
                    'horas' => $this->mi_horas,
                    'horas_extra' => $this->mi_horas_extra,
                ]);

                foreach ($this->companeros as $companero) {
                    $albaran->lineasPersonal()->create([
                        'trabajador_id' => $companero['trabajador_id'],
                        'horas' => $companero['horas'],
                        'horas_extra' => $companero['horas_extra'] ?? '0.00',
                    ]);
                }

                // Líneas material: borramos una a una para que el Observer
                // devuelva stock al material correspondiente.
                $albaran->lineasMaterial()->each(fn ($linea) => $linea->delete());

                foreach ($this->materiales as $material) {
                    $albaran->lineasMaterial()->create([
                        'material_id' => $material['material_id'],
                        'cantidad' => $material['cantidad'],
                    ]);
                }
            }
            // Web: líneas gestionadas directamente desde los modales de cada tab.

            return $albaran->fresh();
        });
    }
}
