<?php

namespace App\Livewire\Configuracion;

use App\Models\Empresa;
use App\Support\AjustesFields;
use App\Support\Branding;
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

    public string $plantilla_numeracion_cliente = 'CLI-{NNNN}';

    public string $plantilla_numeracion_pedido = 'PED-{YYYY}-{NNNN}';

    public string $plantilla_numeracion_proyecto = 'PROY-{NNNN}';

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

    public ?array $debug_guardar = null;

    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        Gate::authorize('configuracion.ver');

        $empresa = Empresa::actual();
        $this->plantilla_numeracion_albaran = $empresa->plantilla_numeracion_albaran ?? 'ALB-{YYYY}-{NNNN}';
        $this->plantilla_numeracion_cliente = $empresa->plantilla_numeracion_cliente ?? 'CLI-{NNNN}';
        $this->plantilla_numeracion_pedido = $empresa->plantilla_numeracion_pedido ?? 'PED-{YYYY}-{NNNN}';
        $this->plantilla_numeracion_proyecto = $empresa->plantilla_numeracion_proyecto ?? 'PROY-{NNNN}';
        $this->token_caducidad_dias = $empresa->token_caducidad_dias ?? 7;
        $this->archivo_tamano_max_mb = $empresa->archivo_tamano_max_mb ?? 10;
        $this->archivo_cantidad_max = $empresa->archivo_cantidad_max ?? 20;

        $this->logo_app_path = $empresa->logo_app_path;
        $this->logo_app_ratio = $empresa->logo_app_ratio;
        $this->logo_app_zoom = $empresa->logo_app_zoom ?: 100;

        $this->color_primario = $empresa->color_primario ?? '#334155';
        $this->color_secundario = $empresa->color_secundario ?? '#f1f5f9';
        $this->color_texto_encabezado = $empresa->color_texto_encabezado ?? '#ffffff';
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
            'plantilla_numeracion_cliente' => 'Codigo cliente',
            'plantilla_numeracion_pedido' => 'Numero de pedido',
            'plantilla_numeracion_proyecto' => 'Codigo de proyecto',
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
        $this->plantilla_numeracion_cliente = $empresa->plantilla_numeracion_cliente ?? 'CLI-{NNNN}';
        $this->plantilla_numeracion_pedido = $empresa->plantilla_numeracion_pedido ?? 'PED-{YYYY}-{NNNN}';
        $this->plantilla_numeracion_proyecto = $empresa->plantilla_numeracion_proyecto ?? 'PROY-{NNNN}';
        $this->token_caducidad_dias = $empresa->token_caducidad_dias ?? 7;
        $this->archivo_tamano_max_mb = $empresa->archivo_tamano_max_mb ?? 10;
        $this->archivo_cantidad_max = $empresa->archivo_cantidad_max ?? 20;
        $this->logo_app_zoom = $empresa->logo_app_zoom ?: 100;
        $this->color_primario = $empresa->color_primario ?? '#334155';
        $this->color_secundario = $empresa->color_secundario ?? '#f1f5f9';
        $this->color_texto_encabezado = $empresa->color_texto_encabezado ?? '#ffffff';
        $this->nuevoLogoApp = null;
        $this->eliminarLogoApp = false;
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

        $this->debug_guardar = [
            'momento' => now()->format('Y-m-d H:i:s'),
            'fase' => 'invocado',
            'plantilla_numeracion_cliente' => $this->plantilla_numeracion_cliente,
            'plantilla_numeracion_albaran' => $this->plantilla_numeracion_albaran,
            'plantilla_numeracion_pedido' => $this->plantilla_numeracion_pedido,
            'plantilla_numeracion_proyecto' => $this->plantilla_numeracion_proyecto,
            'token_caducidad_dias' => $this->token_caducidad_dias,
            'archivo_cantidad_max' => $this->archivo_cantidad_max,
            'color_primario' => $this->color_primario,
            'color_secundario' => $this->color_secundario,
            'color_texto_encabezado' => $this->color_texto_encabezado,
            'origen' => 'Livewire::guardar()',
        ];

        // [AJUSTES DEBUG] — rastro temporal para diagnosticar el botón Guardar.
        \Log::info('[AJUSTES DEBUG] guardar() INVOCADO', [
            'user_id' => auth()->id(),
            'plantilla_numeracion_cliente' => $this->plantilla_numeracion_cliente,
            'plantilla_numeracion_albaran' => $this->plantilla_numeracion_albaran,
            'plantilla_numeracion_pedido' => $this->plantilla_numeracion_pedido,
            'plantilla_numeracion_proyecto' => $this->plantilla_numeracion_proyecto,
            'token_caducidad_dias' => $this->token_caducidad_dias,
            'archivo_cantidad_max' => $this->archivo_cantidad_max,
            'color_primario' => $this->color_primario,
            'color_secundario' => $this->color_secundario,
            'color_texto_encabezado' => $this->color_texto_encabezado,
        ]);

        try {
            $this->validate();
            $this->debug_guardar['fase'] = 'validado';
            \Log::info('[AJUSTES DEBUG] validate() OK');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->debug_guardar['fase'] = 'error_validacion';
            $this->debug_guardar['errores'] = $e->errors();
            \Log::warning('[AJUSTES DEBUG] validate() FALLÓ', ['errors' => $e->errors()]);
            throw $e;
        }

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
            'plantilla_numeracion_cliente' => $this->plantilla_numeracion_cliente,
            'plantilla_numeracion_pedido' => $this->plantilla_numeracion_pedido,
            'plantilla_numeracion_proyecto' => $this->plantilla_numeracion_proyecto,
            'token_caducidad_dias' => $this->token_caducidad_dias,
            'archivo_tamano_max_mb' => $this->archivo_tamano_max_mb,
            'archivo_cantidad_max' => $this->archivo_cantidad_max,
            'logo_app_zoom' => $this->logo_app_zoom,
            'color_primario' => $this->color_primario,
            'color_secundario' => $this->color_secundario,
            'color_texto_encabezado' => $this->color_texto_encabezado,
        ]);

        $empresa->save();
        $this->debug_guardar['fase'] = 'guardado';
        $this->debug_guardar['persistido'] = [
            'empresa_id' => $empresa->id,
            'plantilla_numeracion_cliente' => $empresa->plantilla_numeracion_cliente,
            'plantilla_numeracion_albaran' => $empresa->plantilla_numeracion_albaran,
            'plantilla_numeracion_pedido' => $empresa->plantilla_numeracion_pedido,
            'plantilla_numeracion_proyecto' => $empresa->plantilla_numeracion_proyecto,
            'token_caducidad_dias' => $empresa->token_caducidad_dias,
            'archivo_cantidad_max' => $empresa->archivo_cantidad_max,
            'color_primario' => $empresa->color_primario,
            'color_secundario' => $empresa->color_secundario,
            'color_texto_encabezado' => $empresa->color_texto_encabezado,
        ];
        \Log::info('[AJUSTES DEBUG] empresa->save() EJECUTADO', ['empresa_id' => $empresa->id]);

        $this->logo_app_path = $empresa->logo_app_path;
        $this->logo_app_ratio = $empresa->logo_app_ratio;
        $this->nuevoLogoApp = null;
        $this->eliminarLogoApp = false;

        Branding::limpiarCache();

        session()->flash('status', 'Ajustes guardados correctamente. [' . now()->format('H:i:s') . ']');
        $this->redirectRoute('configuracion.ajustes');
    }

    public function render(): View
    {
        // Mostrar solo líneas relevantes de Ajustes en la vista para depuración
        $logPath = storage_path('logs/laravel.log');
        $logLines = [];
        if (file_exists($logPath)) {
            $lines = file($logPath);
            $ajustesLines = array_values(array_filter($lines, static fn (string $line): bool => str_contains($line, '[AJUSTES DEBUG]')));
            $logLines = array_slice($ajustesLines, -30); // Últimas 30 líneas de Ajustes
        }
        return view('livewire.configuracion.ajustes', [
            'laravel_log' => $logLines,
            'fieldsConfig' => AjustesFields::class,  // Pasar la clase para usarla en Blade
        ]);
    }
}
