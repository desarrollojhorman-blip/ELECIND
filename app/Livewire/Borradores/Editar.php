<?php

namespace App\Livewire\Borradores;

use App\Livewire\Forms\BorradorForm;
use App\Mail\SolicitudFirmaEmail;
use App\Models\Borrador;
use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\Empresa;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\TokenFirma;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'borradores'])]
#[Title('Borrador')]
class Editar extends Component
{
    public BorradorForm $form;

    public ?Borrador $borrador = null;

    public ?int $confirmarEliminarId = null;

    public function mount(?Borrador $borrador = null): void
    {
        if ($borrador !== null && $borrador->exists) {
            Gate::authorize('update', $borrador);
            $this->borrador = $borrador;
            $this->borrador->load(['lineasPersonal.trabajador', 'lineasMaterial.material', 'firmas', 'tokensFirma']);
            $this->form->fromModel($borrador);
        } else {
            Gate::authorize('create', Borrador::class);
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    public function deshacer(): void
    {
        if ($this->borrador !== null) {
            $this->borrador->loadMissing(['lineasPersonal.trabajador', 'lineasMaterial.material']);
            $this->form->fromModel($this->borrador);
        } else {
            $this->form->reset();
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    public function guardar(): void
    {
        $esNuevo = $this->borrador === null;

        if ($esNuevo) {
            Gate::authorize('create', Borrador::class);
        } else {
            Gate::authorize('update', $this->borrador);
        }

        $borrador = $this->form->save();

        session()->flash('status', $esNuevo
            ? "Borrador «{$borrador->numero_borrador}» creado correctamente."
            : "Borrador «{$borrador->numero_borrador}» actualizado correctamente.");

        $this->redirectRoute('borradores.editar', ['borrador' => $borrador->getKey()]);
    }

    public function confirmarEliminar(): void
    {
        if ($this->borrador === null) {
            return;
        }
        Gate::authorize('delete', $this->borrador);
        $this->confirmarEliminarId = $this->borrador->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        if ($this->borrador === null) {
            return;
        }
        Gate::authorize('delete', $this->borrador);
        $numero = $this->borrador->numero_borrador;
        $this->borrador->delete();

        session()->flash('status', "Borrador «{$numero}» eliminado.");
        $this->redirectRoute('borradores.index', navigate: false);
    }

    // ── Notificaciones de firma ───────────────────────────────────────────────

    public function notificarFirmantes(bool $trabajador, bool $responsable): void
    {
        if ($this->borrador === null) {
            return;
        }

        $empresa = Empresa::actual();

        if (! $empresa->mail_host) {
            $this->addError('firma', 'Configura el servidor de correo en Ajustes → Correo antes de enviar notificaciones.');
            return;
        }

        config(['mail.mailers.empresa_smtp' => [
            'transport'  => 'smtp',
            'host'       => $empresa->mail_host,
            'port'       => $empresa->mail_port,
            'encryption' => $empresa->mail_encryption,
            'username'   => $empresa->mail_username,
            'password'   => $empresa->mail_password,
            'timeout'    => 15,
        ]]);

        $enviados      = 0;
        $caducidadDias = $empresa->token_caducidad_dias ?? 7;

        if ($trabajador) {
            $email  = $this->form->firma_trabajador_otro_correo;
            $nombre = $this->form->firma_trabajador_otro_nombre;

            if (! $email && $this->form->firma_trabajador_user_id) {
                $user   = User::find($this->form->firma_trabajador_user_id);
                $email  = $user?->email;
                $nombre = $user ? trim($user->nombre . ' ' . $user->apellidos) : null;
            }

            if ($email) {
                $this->enviarTokenFirma('trabajador', $email, $nombre, $caducidadDias, $empresa);
                $enviados++;
            } else {
                $this->addError('firma', 'El empleado firmante no tiene correo configurado.');
            }
        }

        if ($responsable) {
            $email  = $this->form->firma_responsable_otro_correo;
            $nombre = $this->form->firma_responsable_otro_nombre;

            if (! $email && $this->form->responsable_id) {
                $user   = User::find($this->form->responsable_id);
                $email  = $user?->email;
                $nombre = $user ? trim($user->nombre . ' ' . $user->apellidos) : null;
            }

            if ($email) {
                $this->enviarTokenFirma('responsable', $email, $nombre, $caducidadDias, $empresa);
                $enviados++;
            } else {
                $this->addError('firma', 'El responsable firmante no tiene correo configurado.');
            }
        }

        $this->borrador->load('tokensFirma');

        if ($enviados > 0) {
            session()->flash('status', $enviados === 1 ? 'Notificación enviada correctamente.' : 'Notificaciones enviadas correctamente.');
        }
    }

    private function enviarTokenFirma(string $tipo, string $email, ?string $nombre, int $caducidadDias, Empresa $empresa): void
    {
        $this->borrador->tokensFirma()
            ->where('tipo_firmante', $tipo)
            ->whereNull('usado_at')
            ->whereNull('invalidado_at')
            ->update(['invalidado_at' => now()]);

        /** @var TokenFirma $tokenFirma */
        $tokenFirma = $this->borrador->tokensFirma()->create([
            'tipo_firmante'        => $tipo,
            'token'                => Str::random(64),
            'email_destino'        => $email,
            'nombre_destino'       => $nombre,
            'caduca_at'            => now()->addDays($caducidadDias),
            'generado_por_user_id' => Auth::id(),
        ]);

        $tokenFirma->load([
            'firmable.proyecto',
            'firmable.cliente',
            'firmable.concepto',
            'firmable.lineasPersonal.trabajador',
        ]);

        Mail::mailer('empresa_smtp')
            ->to($email)
            ->send(new SolicitudFirmaEmail($tokenFirma));
    }

    /* ── Computeds ─────────────────────────────────────────────── */

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        return Proyecto::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);
    }

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /** @return Collection<int, Concepto> */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        return Concepto::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function trabajadoresDisponibles(): Collection
    {
        return User::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, Material> */
    #[Computed]
    public function materialesDisponibles(): Collection
    {
        return Material::query()
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'unidad_medida']);
    }

    public function render(): View
    {
        $titulo = $this->borrador
            ? "Borrador {$this->borrador->numero_borrador}"
            : 'Nuevo borrador';

        $tokenTrabajador  = $this->borrador?->tokensFirma->where('tipo_firmante.value', 'trabajador')->sortByDesc('created_at')->first();
        $tokenResponsable = $this->borrador?->tokensFirma->where('tipo_firmante.value', 'responsable')->sortByDesc('created_at')->first();
        $firmaTrabajador  = $this->borrador?->firmas->where('tipo.value', 'trabajador')->first();
        $firmaResponsable = $this->borrador?->firmas->where('tipo.value', 'responsable')->first();

        return view('livewire.borradores.editar', compact(
            'titulo',
            'tokenTrabajador', 'tokenResponsable',
            'firmaTrabajador', 'firmaResponsable',
        ));
    }
}
