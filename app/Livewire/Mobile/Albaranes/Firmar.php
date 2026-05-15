<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoFirma;
use App\Models\Albaran;
use App\Models\AlbaranFirma;
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

    public function mount(Albaran $albaran): void
    {
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

        if (\in_array($albaran->estado, [
            EstadoAlbaran::FIRMADO,
            EstadoAlbaran::FACTURADO,
            EstadoAlbaran::ARCHIVADO,
        ], true)) {
            $this->firmado = true;
        }
    }

    public function firmar(): void
    {
        Gate::authorize('update', $this->albaran);

        $this->validate([
            'firmaTrabajadorData' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ], [
            'firmaTrabajadorData.required'     => 'El trabajador debe dibujar su firma.',
            'firmaTrabajadorData.starts_with'  => 'La firma del trabajador no es válida.',
        ]);

        // Guardar firma del trabajador
        $pathTrab = $this->guardarImagenFirma($this->firmaTrabajadorData, 'trabajador');

        AlbaranFirma::create([
            'albaran_id'          => $this->albaran->id,
            'tipo'                => TipoFirma::TRABAJADOR->value,
            'firmado_por_user_id' => Auth::id(),
            'firma_path'          => $pathTrab,
            'ip'                  => request()->ip(),
            'user_agent'          => request()->userAgent(),
            'firmado_at'          => now(),
        ]);

        // Guardar firma del responsable si la proporcionó
        $responsableFirmo = false;

        if ($this->firmaResponsableData !== '' && $this->albaran->responsable_id) {
            $pathResp = $this->guardarImagenFirma($this->firmaResponsableData, 'responsable');

            AlbaranFirma::create([
                'albaran_id'          => $this->albaran->id,
                'tipo'                => TipoFirma::RESPONSABLE->value,
                'firmado_por_user_id' => $this->albaran->responsable_id,
                'firma_path'          => $pathResp,
                'ip'                  => request()->ip(),
                'user_agent'          => request()->userAgent(),
                'firmado_at'          => now(),
            ]);

            $responsableFirmo = true;
        }

        // Transición de estado
        $estadoFinal = (! $this->albaran->responsable_id || $responsableFirmo)
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
