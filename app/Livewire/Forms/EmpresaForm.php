<?php

namespace App\Livewire\Forms;

use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class EmpresaForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public string $nombre = '';

    #[Validate]
    public ?string $nombre_comercial = null;

    #[Validate]
    public ?string $cif = null;

    #[Validate]
    public ?string $direccion = null;

    #[Validate]
    public ?string $codigo_postal = null;

    #[Validate]
    public ?string $poblacion = null;

    #[Validate]
    public ?string $provincia = null;

    #[Validate]
    public ?string $telefono = null;

    #[Validate]
    public ?string $email_contacto = null;

    #[Validate]
    public ?string $email_notificaciones = null;

    /** Logo actual ya guardado (sólo lectura, para previsualización). */
    public ?string $logo_path = null;

    /** Nuevo logo subido aún en estado temporal (Livewire) — sólo en sesión. */
    #[Validate]
    public ?TemporaryUploadedFile $nuevoLogo = null;

    public bool $eliminarLogo = false;

    #[Validate]
    public string $color_primario = '#871f1f';

    #[Validate]
    public string $color_secundario = '#f5e6e6';

    #[Validate]
    public string $plantilla_numeracion_albaran = 'ALB-{YYYY}-{NNNN}';

    #[Validate]
    public int $token_caducidad_dias = 7;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'nombre_comercial' => ['nullable', 'string', 'max:150'],
            'cif' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'poblacion' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email_contacto' => ['nullable', 'email', 'max:150'],
            'email_notificaciones' => ['nullable', 'email', 'max:150'],
            'nuevoLogo' => ['nullable', 'image', 'max:2048', 'mimes:png,jpg,jpeg,svg,webp'],
            'color_primario' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_secundario' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'plantilla_numeracion_albaran' => ['required', 'string', 'max:60'],
            'token_caducidad_dias' => ['required', 'integer', 'min:1', 'max:90'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'nombre' => 'nombre',
            'nombre_comercial' => 'nombre comercial',
            'cif' => 'CIF',
            'direccion' => 'dirección',
            'codigo_postal' => 'código postal',
            'poblacion' => 'población',
            'provincia' => 'provincia',
            'telefono' => 'teléfono',
            'email_contacto' => 'email de contacto',
            'email_notificaciones' => 'email de notificaciones',
            'nuevoLogo' => 'logo',
            'color_primario' => 'color primario',
            'color_secundario' => 'color secundario',
            'plantilla_numeracion_albaran' => 'plantilla de numeración',
            'token_caducidad_dias' => 'caducidad del token de firma',
        ];
    }

    public function fromModel(Empresa $empresa): void
    {
        $this->id = (int) $empresa->getKey();
        $this->nombre = $empresa->nombre;
        $this->nombre_comercial = $empresa->nombre_comercial;
        $this->cif = $empresa->cif;
        $this->direccion = $empresa->direccion;
        $this->codigo_postal = $empresa->codigo_postal;
        $this->poblacion = $empresa->poblacion;
        $this->provincia = $empresa->provincia;
        $this->telefono = $empresa->telefono;
        $this->email_contacto = $empresa->email_contacto;
        $this->email_notificaciones = $empresa->email_notificaciones;
        $this->logo_path = $empresa->logo_path;
        $this->color_primario = $empresa->color_primario;
        $this->color_secundario = $empresa->color_secundario;
        $this->plantilla_numeracion_albaran = $empresa->plantilla_numeracion_albaran;
        $this->token_caducidad_dias = $empresa->token_caducidad_dias;
        $this->nuevoLogo = null;
        $this->eliminarLogo = false;
    }

    public function save(): Empresa
    {
        $this->validate();

        $empresa = $this->id === null
            ? Empresa::actual()
            : Empresa::findOrFail($this->id);

        if ($this->eliminarLogo && $empresa->logo_path) {
            Storage::disk('public')->delete($empresa->logo_path);
            $empresa->logo_path = null;
        }

        if ($this->nuevoLogo !== null) {
            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $empresa->logo_path = $this->nuevoLogo->store('branding', 'public');
        }

        $empresa->fill([
            'nombre' => $this->nombre,
            'nombre_comercial' => $this->nombre_comercial,
            'cif' => $this->cif,
            'direccion' => $this->direccion,
            'codigo_postal' => $this->codigo_postal,
            'poblacion' => $this->poblacion,
            'provincia' => $this->provincia,
            'telefono' => $this->telefono,
            'email_contacto' => $this->email_contacto,
            'email_notificaciones' => $this->email_notificaciones,
            'color_primario' => $this->color_primario,
            'color_secundario' => $this->color_secundario,
            'plantilla_numeracion_albaran' => $this->plantilla_numeracion_albaran,
            'token_caducidad_dias' => $this->token_caducidad_dias,
        ]);

        $empresa->save();

        $this->id = (int) $empresa->getKey();
        $this->logo_path = $empresa->logo_path;
        $this->nuevoLogo = null;
        $this->eliminarLogo = false;

        return $empresa;
    }
}
