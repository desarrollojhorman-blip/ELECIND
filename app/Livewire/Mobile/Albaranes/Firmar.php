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

    /** Mensaje de éxito tras guardar firma parcial */
    public ?string $mensajeGuardado = null;

    /** El usuario logueado actúa como firmante del slot trabajador */
    public bool $esTrabajador = false;

    /** Sin permiso para firmar este albarán */
    public bool $sinPermiso = false;

    /** El campo de trabajador ya tiene firma guardada en BD */
    public bool $trabajadorYaFirmo = false;

    /** El campo de responsable ya tiene firma guardada en BD */
    public bool $responsableYaFirmo = false;

    public function mount(Albaran $albaran): void
    {
        // Comprobación sin lanzar excepción — mostramos pantalla amigable
        if (! Gate::check('firmar', $albaran)) {
            $this->albaran    = $albaran;
            $this->sinPermiso = true;
            return;
        }

        $this->albaran = $albaran->loadMissing([
            'cliente',
            'proyecto',
            'concepto',
            'creador',
            'firmaTrabajador',
            'responsable',
            'lineasPersonal.trabajador',
            'lineasMaterial.material',
            'firmas',
        ]);

        $uid = Auth::id();

        // Es "trabajador" si es el creador del parte O el firmante asignado explícitamente
        $this->esTrabajador = $albaran->creado_por === $uid
            || ($albaran->firma_trabajador_user_id !== null && $albaran->firma_trabajador_user_id === $uid);

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
        if (! Gate::check('firmar', $this->albaran)) {
            $this->sinPermiso = true;
            return;
        }

        if ($this->esTrabajador) {
            // Debe haber al menos una firma dibujada
            if ($this->firmaTrabajadorData === '' && $this->firmaResponsableData === '') {
                $this->addError('firmaTrabajadorData', 'Dibuja al menos una firma para continuar.');
                return;
            }

            // Guardar firma del trabajador si la ha dibujado y no estaba ya firmada
            if ($this->firmaTrabajadorData !== '' && ! $this->trabajadorYaFirmo) {
                $this->validate([
                    'firmaTrabajadorData' => ['string', 'starts_with:data:image/png;base64,'],
                ], [
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
            }

            // Guardar firma del responsable si la ha dibujado y no estaba ya firmada
            if ($this->firmaResponsableData !== '' && $this->albaran->responsable_id && ! $this->responsableYaFirmo) {
                $this->validate([
                    'firmaResponsableData' => ['string', 'starts_with:data:image/png;base64,'],
                ], [
                    'firmaResponsableData.starts_with' => 'La firma del responsable no es válida.',
                ]);

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
            // Responsable: solo puede firmar su propio campo
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

        // FIRMADO solo cuando ambas partes han firmado.
        // Si no hay responsable asignado, basta con la firma del trabajador.
        $tieneResponsable = (bool) $this->albaran->responsable_id;
        $estadoFinal = ($this->trabajadorYaFirmo && (! $tieneResponsable || $this->responsableYaFirmo))
            ? EstadoAlbaran::FIRMADO
            : EstadoAlbaran::PENDIENTE_FIRMA;

        $this->albaran->update(['estado' => $estadoFinal]);

        if ($estadoFinal === EstadoAlbaran::FIRMADO) {
            $this->firmado = true;
        } else {
            $this->mensajeGuardado = 'Firma guardada. Pendiente de completar el resto de firmas.';
        }
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
        Storage::disk('public')->put($path, $binario);

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
