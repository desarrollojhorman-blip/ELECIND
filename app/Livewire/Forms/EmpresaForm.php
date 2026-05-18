<?php

namespace App\Livewire\Forms;

use App\Models\Empresa;
use App\Support\Branding;
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

    /** Logo principal actual ya guardado (sólo lectura, para previsualización). */
    public ?string $logo_path = null;

    /** Ratio detectado (ancho/alto) del logo principal. */
    public ?float $logo_ratio = null;

    /** Nuevo logo principal subido aún en estado temporal (Livewire). */
    #[Validate]
    public ?TemporaryUploadedFile $nuevoLogo = null;

    public bool $eliminarLogo = false;

    #[Validate]
    public int $logo_zoom = 100;


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
            'logo_zoom' => ['required', 'integer', 'in:80,90,100,110,120,130'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required'              => 'El nombre es obligatorio.',
            'nombre.max'                   => 'El nombre no puede superar 150 caracteres.',
            'nombre_comercial.max'         => 'El nombre comercial no puede superar 150 caracteres.',
            'cif.max'                      => 'El CIF no puede superar 20 caracteres.',
            'direccion.max'                => 'La dirección no puede superar 255 caracteres.',
            'codigo_postal.max'            => 'El código postal no puede superar 10 caracteres.',
            'poblacion.max'                => 'La población no puede superar 100 caracteres.',
            'provincia.max'                => 'La provincia no puede superar 100 caracteres.',
            'telefono.max'                 => 'El teléfono no puede superar 30 caracteres.',
            'email_contacto.email'         => 'El email de contacto no tiene un formato válido.',
            'email_contacto.max'           => 'El email de contacto no puede superar 150 caracteres.',
            'email_notificaciones.email'   => 'El email de notificaciones no tiene un formato válido.',
            'email_notificaciones.max'     => 'El email de notificaciones no puede superar 150 caracteres.',
            'nuevoLogo.image'              => 'El logo debe ser una imagen.',
            'nuevoLogo.max'                => 'El logo no puede superar 2 MB.',
            'nuevoLogo.mimes'              => 'El logo debe ser PNG, JPG, SVG o WebP.',
            'logo_zoom.required'           => 'El zoom del logo es obligatorio.',
            'logo_zoom.in'                 => 'El zoom debe ser uno de los valores permitidos.',
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
            'nuevoLogo' => 'logo de empresa',
            'logo_zoom' => 'zoom del logo',
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
        $this->logo_ratio = $empresa->logo_ratio;
        $this->logo_zoom = $empresa->logo_zoom ?: 100;
        $this->nuevoLogo = null;
        $this->eliminarLogo = false;


    }

    public function save(): Empresa
    {
        $this->validate();

        $empresa = $this->id === null
            ? Empresa::actual()
            : Empresa::findOrFail($this->id);

        // Logo principal
        if ($this->eliminarLogo && $empresa->logo_path) {
            Storage::disk('public')->delete($empresa->logo_path);
            $empresa->logo_path = null;
            $empresa->logo_ratio = null;
        }

        if ($this->nuevoLogo !== null) {
            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }

            $empresa->logo_path = $this->nuevoLogo->store('branding', 'public');
            $empresa->logo_ratio = Branding::detectarRatio(
                Storage::disk('public')->path($empresa->logo_path)
            );
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
            'logo_zoom' => $this->logo_zoom,
        ]);

        $empresa->save();

        $this->id = (int) $empresa->getKey();
        $this->logo_path = $empresa->logo_path;
        $this->logo_ratio = $empresa->logo_ratio;
        $this->nuevoLogo = null;
        $this->eliminarLogo = false;

        return $empresa;
    }
}
