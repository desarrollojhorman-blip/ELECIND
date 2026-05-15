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

    /** Logo de albarán actual ya guardado. */
    public ?string $logo_albaran_path = null;

    /** Ratio detectado del logo de albarán. */
    public ?float $logo_albaran_ratio = null;

    #[Validate]
    public ?TemporaryUploadedFile $nuevoLogoAlbaran = null;

    public bool $eliminarLogoAlbaran = false;

    #[Validate]
    public int $logo_albaran_zoom = 100;

    #[Validate]
    public string $color_primario = '#871f1f';

    #[Validate]
    public string $color_secundario = '#f5e6e6';

    #[Validate]
    public string $color_texto_encabezado = '#ffffff';

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
            'nuevoLogoAlbaran' => ['nullable', 'image', 'max:2048', 'mimes:png,jpg,jpeg,svg,webp'],
            'logo_zoom' => ['required', 'integer', 'in:80,90,100,110,120,130'],
            'logo_albaran_zoom' => ['required', 'integer', 'in:80,90,100,110,120,130'],
            'color_primario' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_secundario' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_texto_encabezado' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
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
            'nuevoLogo' => 'logo principal',
            'nuevoLogoAlbaran' => 'logo de albarán',
            'logo_zoom' => 'zoom del logo principal',
            'logo_albaran_zoom' => 'zoom del logo de albarán',
            'color_primario' => 'color primario',
            'color_secundario' => 'color secundario',
            'color_texto_encabezado' => 'color texto encabezado',
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

        $this->logo_albaran_path = $empresa->logo_albaran_path;
        $this->logo_albaran_ratio = $empresa->logo_albaran_ratio;
        $this->logo_albaran_zoom = $empresa->logo_albaran_zoom ?: 100;
        $this->nuevoLogoAlbaran = null;
        $this->eliminarLogoAlbaran = false;

        $this->color_primario = $empresa->color_primario;
        $this->color_secundario = $empresa->color_secundario;
        $this->color_texto_encabezado = $empresa->color_texto_encabezado ?? '#ffffff';
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

        // Logo de albarán
        if ($this->eliminarLogoAlbaran && $empresa->logo_albaran_path) {
            Storage::disk('public')->delete($empresa->logo_albaran_path);
            $empresa->logo_albaran_path = null;
            $empresa->logo_albaran_ratio = null;
        }

        if ($this->nuevoLogoAlbaran !== null) {
            if ($empresa->logo_albaran_path) {
                Storage::disk('public')->delete($empresa->logo_albaran_path);
            }

            $empresa->logo_albaran_path = $this->nuevoLogoAlbaran->store('branding', 'public');
            $empresa->logo_albaran_ratio = Branding::detectarRatio(
                Storage::disk('public')->path($empresa->logo_albaran_path)
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
            'logo_albaran_zoom' => $this->logo_albaran_zoom,
            'color_primario' => $this->color_primario,
            'color_secundario' => $this->color_secundario,
            'color_texto_encabezado' => $this->color_texto_encabezado,
        ]);

        $empresa->save();

        $this->id = (int) $empresa->getKey();
        $this->logo_path = $empresa->logo_path;
        $this->logo_ratio = $empresa->logo_ratio;
        $this->logo_albaran_path = $empresa->logo_albaran_path;
        $this->logo_albaran_ratio = $empresa->logo_albaran_ratio;
        $this->nuevoLogo = null;
        $this->nuevoLogoAlbaran = null;
        $this->eliminarLogo = false;
        $this->eliminarLogoAlbaran = false;

        return $empresa;
    }
}
