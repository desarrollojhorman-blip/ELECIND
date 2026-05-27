<?php

namespace App\Livewire\Firma;

use App\Enums\EstadoAlbaran;
use App\Models\Albaran;
use App\Models\Firma;
use App\Models\TokenFirma;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.publica')]
#[Title('Firma de documento')]
class FirmarAlbaran extends Component
{
    public TokenFirma $tokenFirma;

    public bool $firmado = false;

    public string $firmaData = '';

    public string $error = '';

    public function mount(string $token): void
    {
        $tokenFirma = TokenFirma::with([
            'firmable.proyecto',
            'firmable.concepto',
            'firmable.lineasPersonal.trabajador',
            'firmable.lineasMaterial.material',
        ])->where('token', $token)->first();

        if ($tokenFirma === null) {
            $this->error = 'El enlace de firma no existe o ya no es válido.';
            return;
        }

        if (! $tokenFirma->esValido()) {
            if ($tokenFirma->usado_at !== null) {
                $this->error = 'Este enlace ya fue utilizado el ' . $tokenFirma->usado_at->format('d/m/Y H:i') . '. El documento ya está firmado.';
            } elseif ($tokenFirma->invalidado_at !== null) {
                $this->error = 'Este enlace fue anulado. Si necesitas firmar, solicita un nuevo enlace.';
            } else {
                $this->error = 'Este enlace caducó el ' . $tokenFirma->caduca_at->format('d/m/Y') . '. Solicita un nuevo enlace.';
            }
            return;
        }

        $yaFirmado = Firma::where('firmable_type', $tokenFirma->firmable_type)
            ->where('firmable_id', $tokenFirma->firmable_id)
            ->where('tipo', $tokenFirma->tipo_firmante)
            ->exists();

        if ($yaFirmado) {
            $this->firmado = true;
            return;
        }

        $this->tokenFirma = $tokenFirma;
    }

    public function firmar(): void
    {
        if ($this->firmado || $this->error !== '' || ! isset($this->tokenFirma)) {
            return;
        }

        $this->validate([
            'firmaData' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ], [
            'firmaData.required'    => 'Debes dibujar tu firma antes de continuar.',
            'firmaData.starts_with' => 'La firma no es válida.',
        ]);

        $firmable = $this->tokenFirma->firmable;

        $binario = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->firmaData));
        $slug    = strtolower(class_basename($firmable));
        $path    = sprintf('firmas/%s-%d-%s-%s.png', $slug, $firmable->id, $this->tokenFirma->tipo_firmante->value, now()->format('YmdHis'));
        Storage::disk('public')->put($path, $binario);

        Firma::create([
            'firmable_type' => $this->tokenFirma->firmable_type,
            'firmable_id'   => $this->tokenFirma->firmable_id,
            'tipo'          => $this->tokenFirma->tipo_firmante,
            'token_id'      => $this->tokenFirma->id,
            'firma_path'    => $path,
            'ip'            => request()->ip(),
            'user_agent'    => request()->userAgent(),
            'firmado_at'    => now(),
        ]);

        $this->tokenFirma->update(['usado_at' => now()]);

        // Actualizar estado si es un Albarán
        if ($firmable instanceof Albaran) {
            $firmable->load('firmas');

            /** @var \App\Models\Firma $f */
            $tieneTrabajador  = $firmable->firmas->contains(fn ($f) => $f->tipo->value === 'trabajador');
            $tieneResponsable = $firmable->firmas->contains(fn ($f) => $f->tipo->value === 'responsable');

            $necesitaTrabajador  = $firmable->firma_trabajador_user_id || $firmable->firma_trabajador_otro_correo;
            $necesitaResponsable = $firmable->responsable_id || $firmable->firma_responsable_otro_correo;

            $todoFirmado = (! $necesitaTrabajador || $tieneTrabajador)
                        && (! $necesitaResponsable || $tieneResponsable);

            $firmable->update([
                'estado' => $todoFirmado ? EstadoAlbaran::FIRMADO : EstadoAlbaran::PENDIENTE_FIRMA,
            ]);
        }

        $this->firmado = true;
    }

    public function render(): View
    {
        $firmable = isset($this->tokenFirma) ? $this->tokenFirma->firmable : null;

        return view('livewire.firma.firmar-albaran', compact('firmable'));
    }
}
