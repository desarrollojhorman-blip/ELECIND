<?php

namespace App\Livewire\Forms;

use App\Enums\TipoHora;
use App\Models\Parte;
use App\Models\Proyecto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Formulario de creación y edición de un parte.
 *
 * Misma cabecera que AlbaranForm (cliente, proyecto, concepto, responsable,
 * fecha, tipo_hora, observaciones), sin firmas. El estado de un parte es
 * 'abierto'/'cerrado' (un parte se cierra al generar el albarán).
 *
 * Las líneas (Trabajadores y Materiales) NO se gestionan aquí — el componente
 * Partes\Editar las edita con modales independientes (mismo patrón que el
 * Albaranes\Editar web).
 */
class ParteForm extends Form
{
    public ?int $id = null;

    public ?string $numero = null;

    public ?int $cliente_id = null;

    public ?int $proyecto_id = null;

    public ?int $concepto_id = null;

    public ?int $responsable_id = null;

    public string $fecha = '';

    public string $tipo_hora = 'laboral';

    public string $estado = 'abierto';

    public ?string $observaciones = null;

    /* ── Parte personalizado (mismo patrón que albaran) ──────── */

    public bool $esPersonalizado = false;

    public bool $clienteOtro = false;

    public string $clienteTexto = '';

    public bool $proyectoOtro = false;

    public string $proyectoTexto = '';

    public bool $conceptoOtro = false;

    public string $conceptoTexto = '';

    public bool $responsableOtro = false;

    public string $responsableTexto = '';

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
            'estado' => ['required', Rule::in([Parte::ESTADO_ABIERTO, Parte::ESTADO_CERRADO])],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_id.required'        => 'Selecciona un cliente.',
            'clienteTexto.required'      => 'Escribe el nombre del cliente.',
            'proyecto_id.required'       => 'Selecciona un proyecto.',
            'proyectoTexto.required'     => 'Escribe el nombre del proyecto.',
            'conceptoTexto.required'     => 'Escribe el tipo de trabajo.',
            'responsableTexto.required'  => 'Escribe el nombre del responsable.',
            'fecha.required'             => 'La fecha es obligatoria.',
            'tipo_hora.required'         => 'Indica el tipo de jornada.',
        ];
    }

    public function fromModel(Parte $parte): void
    {
        $this->id             = (int) $parte->getKey();
        $this->numero         = $parte->numero;
        $this->cliente_id     = $parte->cliente_id;
        $this->proyecto_id    = $parte->proyecto_id;
        $this->concepto_id    = $parte->concepto_id;
        $this->responsable_id = $parte->responsable_id;
        $this->fecha          = Carbon::parse($parte->fecha)->format('Y-m-d');
        $this->tipo_hora      = $parte->tipo_hora;
        $this->estado         = $parte->estado;
        $this->observaciones  = $parte->observaciones;

        $this->esPersonalizado    = (bool) $parte->es_personalizado;
        $this->clienteTexto       = (string) ($parte->cliente_texto ?? '');
        $this->clienteOtro        = $this->clienteTexto !== '';
        $this->proyectoTexto      = (string) ($parte->proyecto_texto ?? '');
        $this->proyectoOtro       = $this->proyectoTexto !== '';
        $this->conceptoTexto      = (string) ($parte->concepto_texto ?? '');
        $this->conceptoOtro       = $this->conceptoTexto !== '';
        $this->responsableTexto   = (string) ($parte->responsable_texto ?? '');
        $this->responsableOtro    = $this->responsableTexto !== '';
    }

    /** Sincroniza cliente_id desde el proyecto seleccionado. */
    public function sincronizarClienteDesdeProyecto(): void
    {
        if ($this->proyecto_id === null) {
            $this->cliente_id = null;
            $this->responsable_id = null;
            $this->concepto_id = null;

            return;
        }

        $proyecto = Proyecto::query()->find($this->proyecto_id);
        if ($proyecto === null) {
            return;
        }

        $this->cliente_id = $proyecto->cliente_id;
        $this->responsable_id = $proyecto->responsable_principal_id;
        $this->concepto_id = null;
    }

    public function save(): Parte
    {
        $this->validate();

        return DB::transaction(function (): Parte {
            $esNuevo = $this->id === null;

            if ($esNuevo) {
                $parte = new Parte;
                // numero lo rellena el ParteObserver::creating().
                $parte->creado_por = (int) Auth::id();
                $parte->estado = Parte::ESTADO_ABIERTO;
            } else {
                /** @var Parte $parte */
                $parte = Parte::findOrFail($this->id);
            }

            $parte->fecha = Carbon::parse($this->fecha);
            $parte->cliente_id = $this->cliente_id ? (int) $this->cliente_id : null;
            $parte->proyecto_id = $this->proyecto_id;
            $parte->concepto_id = $this->concepto_id;
            $parte->responsable_id = $this->responsable_id;
            $parte->tipo_hora = $this->tipo_hora;
            $parte->observaciones = $this->observaciones;
            // El estado solo se modifica en edición (al crear se queda abierto).
            if (! $esNuevo) {
                $parte->estado = $this->estado;
            }
            $parte->es_personalizado = $this->esPersonalizado;
            $parte->cliente_texto = $this->esPersonalizado && $this->clienteOtro ? trim($this->clienteTexto) : null;
            $parte->proyecto_texto = $this->esPersonalizado && $this->proyectoOtro ? trim($this->proyectoTexto) : null;
            $parte->concepto_texto = $this->esPersonalizado && $this->conceptoOtro ? trim($this->conceptoTexto) : null;
            $parte->responsable_texto = $this->esPersonalizado && $this->responsableOtro ? trim($this->responsableTexto) : null;

            $parte->save();

            return $parte->fresh();
        });
    }
}
