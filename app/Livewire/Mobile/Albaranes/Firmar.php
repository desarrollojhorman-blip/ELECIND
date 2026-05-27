<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoFirma;
use App\Models\Albaran;
use App\Models\Firma;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Firmar extends Component
{
    public Albaran $albaran;

    public string $firmaTrabajadorData = '';

    public string $firmaResponsableData = '';

    /** true cuando el proceso ha terminado (éxito o ya estaba firmado) */
    public bool $firmado = false;

    /** El usuario logueado es el creador del parte */
    public bool $esTrabajador = false;

    /** El campo de trabajador ya tiene firma guardada en BD */
    public bool $trabajadorYaFirmo = false;

    /** El campo de responsable ya tiene firma guardada en BD */
    public bool $responsableYaFirmo = false;

    public function mount(Albaran $albaran): void
    {
        Gate::authorize('firmar', $albaran);

        $this->albaran = $albaran->loadMissing([
            'cliente',
            'proyecto',
            'concepto',
            'creador',
            'responsable',
            'lineasPersonal.trabajador',
            'lineasMaterial.material',
            'firmas',
        ]);

        $this->esTrabajador = $albaran->creado_por === Auth::id();

        $this->trabajadorYaFirmo = $this->albaran->firmas
            ->contains(fn ($f) => $f->tipo->value === 'trabajador');

        $this->responsableYaFirmo = $this->albaran->firmas
            ->contains(fn ($f) => $f->tipo->value === 'responsable');

        if (\in_array($albaran->estado, [
            EstadoAlbaran::FIRMADO,
            EstadoAlbaran::FACTURADO,
        ], true)) {
            $this->firmado = true;
        }
    }

    public function firmar(): void
    {
        Gate::authorize('firmar', $this->albaran);

        if ($this->esTrabajador) {
            $this->validate([
                'firmaTrabajadorData' => ['required', 'string', 'starts_with:data:image/png;base64,'],
            ], [
                'firmaTrabajadorData.required'    => 'El trabajador debe dibujar su firma.',
                'firmaTrabajadorData.starts_with' => 'La firma del trabajador no es válida.',
            ]);

            $pathTrab = $this->guardarImagenFirma($this->firmaTrabajadorData, 'trabajador');

            Firma::create([
                'firmable_type'       => Albaran::class,
                'firmable_id'         => $this->albaran->id,
                'tipo'                => TipoFirma::TRABAJADOR,
                'firmado_por_user_id' => Auth::id(),
                'firma_path'          => $pathTrab,
                'ip'                  => request()->ip(),
                'user_agent'          => request()->userAgent(),
                'firmado_at'          => now(),
            ]);

            $this->trabajadorYaFirmo = true;

            // El trabajador también puede firmar por el responsable en el mismo acto
            if ($this->firmaResponsableData !== '' && $this->albaran->responsable_id) {
                $pathResp = $this->guardarImagenFirma($this->firmaResponsableData, 'responsable');

                Firma::create([
                    'firmable_type'       => Albaran::class,
                    'firmable_id'         => $this->albaran->id,
                    'tipo'                => TipoFirma::RESPONSABLE,
                    'firmado_por_user_id' => $this->albaran->responsable_id,
                    'firma_path'          => $pathResp,
                    'ip'                  => request()->ip(),
                    'user_agent'          => request()->userAgent(),
                    'firmado_at'          => now(),
                ]);

                $this->responsableYaFirmo = true;
            }
        } else {
            // Responsable: solo firma su campo
            $this->validate([
                'firmaResponsableData' => ['required', 'string', 'starts_with:data:image/png;base64,'],
            ], [
                'firmaResponsableData.required'    => 'El responsable debe dibujar su firma.',
                'firmaResponsableData.starts_with' => 'La firma del responsable no es válida.',
            ]);

            $pathResp = $this->guardarImagenFirma($this->firmaResponsableData, 'responsable');

            Firma::create([
                'firmable_type'       => Albaran::class,
                'firmable_id'         => $this->albaran->id,
                'tipo'                => TipoFirma::RESPONSABLE,
                'firmado_por_user_id' => Auth::id(),
                'firma_path'          => $pathResp,
                'ip'                  => request()->ip(),
                'user_agent'          => request()->userAgent(),
                'firmado_at'          => now(),
            ]);

            $this->responsableYaFirmo = true;
        }

        // Transición de estado
        $tieneResponsable = (bool) $this->albaran->responsable_id;
        $estadoFinal = (! $tieneResponsable || $this->responsableYaFirmo)
            ? EstadoAlbaran::FIRMADO
            : EstadoAlbaran::PENDIENTE_FIRMA;

        $this->albaran->update(['estado' => $estadoFinal]);

        $this->firmado = true;
    }

    public function irAlListado(): void
    {
        $this->redirectRoute('mobile.albaranes.index', navigate: false);
    }

    private function guardarImagenFirma(string $dataUrl, string $tipo): string
    {
        $binario = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $dataUrl));
        $path    = \sprintf(
            'firmas/albaran-%d-%s-%s.png',
            $this->albaran->id,
            $tipo,
            now()->format('YmdHis')
        );
        Storage::put($path, $binario);

        return $path;
    }

    public function render(): View
    {
        return view('livewire.mobile.albaranes.firmar')->layout('components.layouts.mobile', [
            'title'     => 'Firma del parte',
            'showBack'  => true,
            'backRoute' => route('mobile.albaranes.index'),
        ]);
    }
}
