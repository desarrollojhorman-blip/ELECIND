<?php

namespace App\Livewire\Configuracion;

use App\Models\Empresa;
use App\Support\AjustesFields;
use App\Support\Branding;
use App\Support\Modulos;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.web', ['active' => 'ajustes'])]
#[Title('Ajustes')]
class Ajustes extends Component
{
    use WithFileUploads;

    // ── Plantillas ───────────────────────────────────────────────────────────
    // Sin validación de sintaxis: el usuario puede poner lo que quiera.
    // Si es incorrecto, lo verá al generar el primer número.

    public string $plantilla_numeracion_albaran = 'ALB-{YYYY}-{NNNN}';

    public string $prefijo_proyecto = 'PR';

    public int $token_caducidad_dias = 7;

    // ── Límites de archivos adjuntos ─────────────────────────────────────────

    public int $archivo_tamano_max_mb = 10;

    public int $archivo_cantidad_max = 20;

    // ── Logo de la aplicación (prioridad absoluta en UI) ─────────────────────

    public ?string $logo_app_path = null;

    public ?float $logo_app_ratio = null;

    #[Validate(['required', 'integer', 'in:80,90,100,110,120,130'])]
    public int $logo_app_zoom = 100;

    #[Validate(['nullable', 'image', 'max:2048', 'mimes:png,jpg,jpeg,svg,webp'])]
    public ?TemporaryUploadedFile $nuevoLogoApp = null;

    public bool $eliminarLogoApp = false;

    // ── Colores de la aplicación ─────────────────────────────────────────────

    public const COLOR_PRIMARIO_DEFAULT        = '#334155';
    public const COLOR_SECUNDARIO_DEFAULT      = '#f1f5f9';
    public const COLOR_TEXTO_ENCABEZADO_DEFAULT = '#ffffff';

    public string $color_primario = self::COLOR_PRIMARIO_DEFAULT;

    public string $color_secundario = self::COLOR_SECUNDARIO_DEFAULT;

    public string $color_texto_encabezado = self::COLOR_TEXTO_ENCABEZADO_DEFAULT;

    // ── Módulos ──────────────────────────────────────────────────────────────

    public bool $modulo_materiales_avanzado = true;

    // ── Correo SMTP ──────────────────────────────────────────────────────────

    public string $mail_host = '';

    public string $mail_port = '587';

    public string $mail_encryption = 'tls';

    public string $mail_username = '';

    public string $mail_password = '';

    public string $mail_from_address = '';

    public string $mail_from_name = '';

    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        Gate::authorize('configuracion.ver');

        $empresa = Empresa::actual();
        $this->plantilla_numeracion_albaran = $empresa->plantilla_numeracion_albaran ?? 'ALB-{YYYY}-{NNNN}';
        $this->prefijo_proyecto = $empresa->prefijo_proyecto ?? 'PR';
        $this->token_caducidad_dias = $empresa->token_caducidad_dias ?? 7;
        $this->archivo_tamano_max_mb = $empresa->archivo_tamano_max_mb ?? 10;
        $this->archivo_cantidad_max = $empresa->archivo_cantidad_max ?? 20;

        $this->logo_app_path = $empresa->logo_app_path;
        $this->logo_app_ratio = $empresa->logo_app_ratio;
        $this->logo_app_zoom = $empresa->logo_app_zoom ?: 100;

        $this->color_primario = $empresa->color_primario ?? '#334155';
        $this->color_secundario = $empresa->color_secundario ?? '#f1f5f9';
        $this->color_texto_encabezado = $empresa->color_texto_encabezado ?? '#ffffff';

        $this->modulo_materiales_avanzado = $empresa->modulo_materiales_avanzado ?? true;

        $this->mail_host          = $empresa->mail_host ?? '';
        $this->mail_port          = (string) ($empresa->mail_port ?? '587');
        $this->mail_encryption    = $empresa->mail_encryption ?? 'tls';
        $this->mail_username      = $empresa->mail_username ?? '';
        $this->mail_password      = $empresa->mail_password ?? '';
        $this->mail_from_address  = $empresa->mail_from_address ?? '';
        $this->mail_from_name     = $empresa->mail_from_name ?? '';
    }

    /**
     * Retorna las reglas de validación desde AjustesFields.
     *
     * Centraliza todas las reglas en App\Support\AjustesFields para facilitar
     * mantenimiento y reutilización.
     */
    #[\Livewire\Attributes\Validate]
    public function rules(): array
    {
        return AjustesFields::getValidationRules();
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'integer' => 'El campo :attribute debe ser un numero entero.',
            'min' => 'El campo :attribute debe ser al menos :min.',
            'max' => 'El campo :attribute no puede superar :max.',
            'in' => 'El valor seleccionado para :attribute no es valido.',
            'regex' => 'El formato de :attribute no es valido.',
            'nuevoLogoApp.image' => 'El logo debe ser una imagen valida.',
            'nuevoLogoApp.max' => 'El logo no puede superar 2 MB.',
            'nuevoLogoApp.mimes' => 'El logo debe ser de tipo: png, jpg, jpeg, svg o webp.',
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'plantilla_numeracion_albaran' => 'Numero de albaran',
            'prefijo_proyecto' => 'Prefijo de proyecto',
            'token_caducidad_dias' => 'Caducidad del token de firma',
            'archivo_tamano_max_mb' => 'Tamano maximo por archivo',
            'archivo_cantidad_max' => 'Cantidad maxima de archivos por albaran',
            'logo_app_zoom' => 'Zoom del logo',
            'nuevoLogoApp' => 'Logo de la aplicacion',
            'color_primario' => 'Color primario',
            'color_secundario' => 'Color secundario',
            'color_texto_encabezado' => 'Color de texto del encabezado',
        ];
    }

    public function quitarLogoApp(): void
    {
        $this->eliminarLogoApp = true;
        $this->nuevoLogoApp = null;
    }

    public function cancelarQuitarLogoApp(): void
    {
        $this->eliminarLogoApp = false;
    }

    public function descartarNuevoLogoApp(): void
    {
        $this->nuevoLogoApp = null;
    }


    public function deshacer(): void
    {
        $this->resetValidation();
        $empresa = Empresa::actual();
        $this->plantilla_numeracion_albaran = $empresa->plantilla_numeracion_albaran ?? 'ALB-{YYYY}-{NNNN}';
        $this->plantilla_numeracion_proyecto = $empresa->plantilla_numeracion_proyecto ?? 'PROY-{NNNN}';
        $this->token_caducidad_dias = $empresa->token_caducidad_dias ?? 7;
        $this->archivo_tamano_max_mb = $empresa->archivo_tamano_max_mb ?? 10;
        $this->archivo_cantidad_max = $empresa->archivo_cantidad_max ?? 20;
        $this->logo_app_zoom = $empresa->logo_app_zoom ?: 100;
        $this->color_primario = $empresa->color_primario ?? '#334155';
        $this->color_secundario = $empresa->color_secundario ?? '#f1f5f9';
        $this->color_texto_encabezado = $empresa->color_texto_encabezado ?? '#ffffff';
        $this->modulo_materiales_avanzado = $empresa->modulo_materiales_avanzado ?? true;
        $this->mail_host         = $empresa->mail_host ?? '';
        $this->mail_port         = (string) ($empresa->mail_port ?? '587');
        $this->mail_encryption   = $empresa->mail_encryption ?? 'tls';
        $this->mail_username     = $empresa->mail_username ?? '';
        $this->mail_password     = $empresa->mail_password ?? '';
        $this->mail_from_address = $empresa->mail_from_address ?? '';
        $this->mail_from_name    = $empresa->mail_from_name ?? '';
        $this->nuevoLogoApp = null;
        $this->eliminarLogoApp = false;
    }

    public function toggleModuloMateriales(): void
    {
        abort_unless(auth()->user()->hasRole('superadmin'), 403);

        $empresa = Empresa::actual();
        $empresa->modulo_materiales_avanzado = $this->modulo_materiales_avanzado;
        $empresa->save();

        Modulos::limpiarCache();

        $estado = $this->modulo_materiales_avanzado ? 'activado' : 'desactivado';
        session()->flash('status', "Módulo de materiales {$estado} correctamente.");
        $this->redirectRoute('configuracion.ajustes', navigate: false);
    }

    public function guardarCorreo(): void
    {
        abort_unless(auth()->user()->hasRole('superadmin'), 403);

        $this->validate([
            'mail_host'         => ['required', 'string', 'max:255'],
            'mail_port'         => ['required', 'integer', 'in:25,465,587,2525'],
            'mail_encryption'   => ['required', 'string', 'in:tls,ssl,starttls,none'],
            'mail_username'     => ['required', 'string', 'max:255'],
            'mail_password'     => ['nullable', 'string', 'max:500'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name'    => ['required', 'string', 'max:255'],
        ]);

        $empresa = Empresa::actual();
        $empresa->mail_host         = $this->mail_host;
        $empresa->mail_port         = (int) $this->mail_port;
        $empresa->mail_encryption   = $this->mail_encryption === 'none' ? null : $this->mail_encryption;
        $empresa->mail_username     = $this->mail_username;
        $empresa->mail_password     = $this->mail_password !== '' ? $this->mail_password : $empresa->mail_password;
        $empresa->mail_from_address = $this->mail_from_address;
        $empresa->mail_from_name    = $this->mail_from_name;
        $empresa->save();

        session()->flash('correo_status', 'Configuración de correo guardada correctamente.');
    }

    public function probarConexionCorreo(): void
    {
        abort_unless(auth()->user()->hasRole('superadmin'), 403);

        $this->validate([
            'mail_host'         => ['required', 'string'],
            'mail_port'         => ['required', 'integer'],
            'mail_encryption'   => ['required', 'string'],
            'mail_username'     => ['required', 'string'],
            'mail_from_address' => ['required', 'email'],
        ]);

        config(['mail.mailers.prueba_smtp' => [
            'transport'  => 'smtp',
            'host'       => $this->mail_host,
            'port'       => (int) $this->mail_port,
            'encryption' => $this->mail_encryption === 'none' ? null : $this->mail_encryption,
            'username'   => $this->mail_username,
            'password'   => $this->mail_password,
            'timeout'    => 15,
        ]]);

        try {
            \Illuminate\Support\Facades\Mail::mailer('prueba_smtp')
                ->raw(
                    'Este es un correo de prueba enviado desde la configuración SMTP de la aplicación.',
                    function ($m) {
                        $m->to(auth()->user()->email)
                          ->from(
                              $this->mail_from_address ?: $this->mail_username,
                              $this->mail_from_name ?: null
                          )
                          ->subject('Prueba de correo · ' . (\App\Support\Branding::nombre()));
                    }
                );

            session()->flash('correo_status', 'Correo de prueba enviado a ' . auth()->user()->email . '. Revisa tu bandeja.');
        } catch (\Exception $e) {
            $this->addError('mail_host', 'Error de conexión: ' . $e->getMessage());
        }
    }

    public function guardar(): void
    {
        // ─ Aplicar valores por defecto si los colores están vacíos
        if (empty($this->color_primario)) {
            $this->color_primario = self::COLOR_PRIMARIO_DEFAULT;
        }
        if (empty($this->color_secundario)) {
            $this->color_secundario = self::COLOR_SECUNDARIO_DEFAULT;
        }
        if (empty($this->color_texto_encabezado)) {
            $this->color_texto_encabezado = self::COLOR_TEXTO_ENCABEZADO_DEFAULT;
        }

        $this->validate();

        $empresa = Empresa::actual();

        // Logo app
        if ($this->eliminarLogoApp && $empresa->logo_app_path) {
            Storage::disk('public')->delete($empresa->logo_app_path);
            $empresa->logo_app_path = null;
            $empresa->logo_app_ratio = null;
        }

        if ($this->nuevoLogoApp !== null) {
            if ($empresa->logo_app_path) {
                Storage::disk('public')->delete($empresa->logo_app_path);
            }

            $empresa->logo_app_path = $this->nuevoLogoApp->store('branding', 'public');
            $empresa->logo_app_ratio = Branding::detectarRatio(
                Storage::disk('public')->path($empresa->logo_app_path)
            );
        }

        $empresa->fill([
            'plantilla_numeracion_albaran' => $this->plantilla_numeracion_albaran,
            'prefijo_proyecto' => strtoupper(trim($this->prefijo_proyecto)),
            'token_caducidad_dias' => $this->token_caducidad_dias,
            'archivo_tamano_max_mb' => $this->archivo_tamano_max_mb,
            'archivo_cantidad_max' => $this->archivo_cantidad_max,
            'logo_app_zoom' => $this->logo_app_zoom,
            'color_primario' => $this->color_primario,
            'color_secundario' => $this->color_secundario,
            'color_texto_encabezado' => $this->color_texto_encabezado,
        ]);

        $empresa->save();

        $this->logo_app_path = $empresa->logo_app_path;
        $this->logo_app_ratio = $empresa->logo_app_ratio;
        $this->nuevoLogoApp = null;
        $this->eliminarLogoApp = false;

        Branding::limpiarCache();

        session()->flash('status', 'Ajustes guardados correctamente.');
        $this->redirectRoute('configuracion.ajustes');
    }

    public function render(): View
    {
        return view('livewire.configuracion.ajustes', [
            'fieldsConfig' => AjustesFields::class,
        ]);
    }
}
