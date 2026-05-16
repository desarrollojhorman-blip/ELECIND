<?php

namespace App\Livewire\Configuracion;

use App\Models\Empresa;
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

    #[Validate(['required', 'string', 'max:60'])]
    public string $plantilla_numeracion_albaran = 'ALB-{YYYY}-{NNNN}';

    #[Validate(['required', 'string', 'max:60'])]
    public string $plantilla_numeracion_cliente = 'CLI-{NNNN}';

    #[Validate(['required', 'string', 'max:60'])]
    public string $plantilla_numeracion_pedido = 'PED-{YYYY}-{NNNN}';

    #[Validate(['required', 'string', 'max:60'])]
    public string $plantilla_numeracion_proyecto = 'PROY-{NNNN}';

    #[Validate(['required', 'integer', 'min:1', 'max:90'])]
    public int $token_caducidad_dias = 7;

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

    #[Validate(['required', 'string', 'max:20'])]
    public string $color_primario = self::COLOR_PRIMARIO_DEFAULT;

    #[Validate(['required', 'string', 'max:20'])]
    public string $color_secundario = self::COLOR_SECUNDARIO_DEFAULT;

    #[Validate(['required', 'string', 'max:20'])]
    public string $color_texto_encabezado = self::COLOR_TEXTO_ENCABEZADO_DEFAULT;

    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        Gate::authorize('configuracion.empresa');

        $empresa = Empresa::actual();
        $this->plantilla_numeracion_albaran = $empresa->plantilla_numeracion_albaran ?? 'ALB-{YYYY}-{NNNN}';
        $this->plantilla_numeracion_cliente = $empresa->plantilla_numeracion_cliente ?? 'CLI-{NNNN}';
        $this->plantilla_numeracion_pedido = $empresa->plantilla_numeracion_pedido ?? 'PED-{YYYY}-{NNNN}';
        $this->plantilla_numeracion_proyecto = $empresa->plantilla_numeracion_proyecto ?? 'PROY-{NNNN}';
        $this->token_caducidad_dias = $empresa->token_caducidad_dias ?? 7;

        $this->logo_app_path = $empresa->logo_app_path;
        $this->logo_app_ratio = $empresa->logo_app_ratio;
        $this->logo_app_zoom = $empresa->logo_app_zoom ?: 100;

        $this->color_primario = $empresa->color_primario ?? '#334155';
        $this->color_secundario = $empresa->color_secundario ?? '#f1f5f9';
        $this->color_texto_encabezado = $empresa->color_texto_encabezado ?? '#ffffff';
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


    public function guardar(): void
    {
        Gate::authorize('configuracion.empresa');

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
            'plantilla_numeracion_cliente' => $this->plantilla_numeracion_cliente,
            'plantilla_numeracion_pedido' => $this->plantilla_numeracion_pedido,
            'plantilla_numeracion_proyecto' => $this->plantilla_numeracion_proyecto,
            'token_caducidad_dias' => $this->token_caducidad_dias,
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
    }

    public function render(): View
    {
        return view('livewire.configuracion.ajustes');
    }
}
