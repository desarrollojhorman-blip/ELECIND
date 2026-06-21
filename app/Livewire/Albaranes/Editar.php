<?php

namespace App\Livewire\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Livewire\Forms\AlbaranForm;
use App\Models\Albaran;
use App\Mail\SolicitudFirmaEmail;
use App\Models\TokenFirma;
use App\Models\Concepto;
use App\Models\Empresa;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.web', ['active' => 'albaranes'])]
#[Title('Albarán')]
class Editar extends Component
{
    use WithFileUploads;

    public AlbaranForm $form;

    public ?Albaran $albaran = null;

    public ?int $confirmarEliminarId = null;

    // ── Inline trabajador ────────────────────────────────────────────────────
    // null = ninguna fila en edición; 0 = fila nueva; >0 = id de línea existente
    public ?int $editandoLineaPersonalId = null;
    public ?int $modalTrabajadorUserId = null;
    public string $modalTrabajadorHoras = '8.00';
    public string $modalTrabajadorHorasExtra = '0.00';
    public ?int $confirmarEliminarLineaPersonalId = null;

    // ── Inline material ───────────────────────────────────────────────────────
    // null = ninguna fila en edición; 0 = fila nueva; >0 = id de línea existente
    public ?int $editandoLineaMaterialId = null;
    public ?int $modalMaterialId = null;
    public string $modalMaterialCantidad = '1.00';
    public string $modalMaterialPrecioVenta = '0';
    public ?int $confirmarEliminarLineaMaterialId = null;

    // ── Precio material en Costes/Gastos ─────────────────────────────────────
    public ?int $editandoPrecioMaterialId = null;
    public string $modalPrecioMaterial = '0';
    public string $modalPrecioCoste = '0';

    // ── Costes / Gastos ──────────────────────────────────────────────────────
    public ?int $editandoCostesLineaId = null;
    public string $modalCosteTarifaHora = '0';
    public string $modalCosteTarifaExtra = '0';
    public string $modalCosteTarifaPlusReten = '0';
    public string $modalCosteTasaHora = '0';
    public string $modalCosteTasaExtra = '0';
    public string $modalCosteTasaPlusReten = '0';

    // ── Inline archivo ───────────────────────────────────────────────────────
    public bool $subiendoArchivo = false;
    public string $modalArchivoNombre = '';
    public ?TemporaryUploadedFile $modalArchivoFichero = null;
    public ?int $confirmarEliminarArchivoId = null;

    public function mount(?Albaran $albaran = null): void
    {
        // Modo web: todas las líneas de personal son iguales, sin línea fija del creador.
        $this->form->omitirLineaCreador = true;

        if ($albaran !== null && $albaran->exists) {
            Gate::authorize('update', $albaran);
            $this->albaran = $albaran;
            $this->albaran->load(['lineasPersonal.trabajador', 'lineasMaterial.material', 'firmas', 'tokensFirma', 'archivos']);
            $this->form->fromModel($albaran);
        } else {
            Gate::authorize('create', Albaran::class);
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    public function deshacer(): void
    {
        if ($this->albaran !== null) {
            $this->albaran->loadMissing(['lineasPersonal', 'lineasMaterial', 'parte:id,numero']);
            $this->form->fromModel($this->albaran);
        } else {
            $this->form->reset();
            $this->form->omitirLineaCreador = true;
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    public function updatedFormProyectoId(): void
    {
        $this->form->sincronizarClienteDesdeProyecto();
    }

    // ── CRUD trabajadores ────────────────────────────────────────────────────

    public function abrirModalTrabajador(?int $lineaId = null): void
    {
        $this->modalTrabajadorUserId     = null;
        $this->modalTrabajadorHoras      = '8.00';
        $this->modalTrabajadorHorasExtra = '0.00';
        $this->editandoLineaPersonalId   = 0; // 0 = fila nueva

        if ($lineaId !== null && $this->albaran !== null) {
            $linea = $this->albaran->lineasPersonal->find($lineaId);
            if ($linea !== null) {
                $this->editandoLineaPersonalId   = $linea->id;
                $this->modalTrabajadorUserId     = $linea->trabajador_id;
                $this->modalTrabajadorHoras      = (string) $linea->horas;
                $this->modalTrabajadorHorasExtra = (string) $linea->horas_extra;
            }
        }

        $this->resetErrorBag();
    }

    public function cerrarModalTrabajador(): void
    {
        $this->editandoLineaPersonalId = null;
        $this->resetErrorBag();
    }

    public function guardarTrabajador(): void
    {
        $this->validate([
            'modalTrabajadorUserId'     => ['required', 'integer', 'exists:users,id'],
            'modalTrabajadorHoras'      => ['required', 'numeric', 'min:0', 'max:24'],
            'modalTrabajadorHorasExtra' => ['required', 'numeric', 'min:0', 'max:24'],
        ], [
            'modalTrabajadorUserId.required' => 'Selecciona un trabajador.',
            'modalTrabajadorHoras.required'  => 'Las horas son obligatorias.',
        ]);

        if ($this->editandoLineaPersonalId > 0) {
            $linea = $this->albaran?->lineasPersonal()->find($this->editandoLineaPersonalId);
            $linea?->update([
                'trabajador_id' => $this->modalTrabajadorUserId,
                'horas'         => $this->modalTrabajadorHoras,
                'horas_extra'   => $this->modalTrabajadorHorasExtra,
            ]);
        } else {
            $this->albaran?->lineasPersonal()->create([
                'trabajador_id' => $this->modalTrabajadorUserId,
                'horas'         => $this->modalTrabajadorHoras,
                'horas_extra'   => $this->modalTrabajadorHorasExtra,
            ]);
        }

        $this->albaran?->load('lineasPersonal.trabajador');
        $this->editandoLineaPersonalId = null;
    }

    public function confirmarEliminarTrabajador(int $lineaId): void
    {
        $this->confirmarEliminarLineaPersonalId = $lineaId;
    }

    public function cancelarEliminarTrabajador(): void
    {
        $this->confirmarEliminarLineaPersonalId = null;
    }

    public function eliminarTrabajador(): void
    {
        if ($this->confirmarEliminarLineaPersonalId === null) {
            return;
        }

        $this->albaran?->lineasPersonal()
            ->find($this->confirmarEliminarLineaPersonalId)
            ?->delete();

        $this->albaran?->load('lineasPersonal.trabajador');
        $this->confirmarEliminarLineaPersonalId = null;
    }

    // ── CRUD materiales ───────────────────────────────────────────────────────

    public function abrirModalMaterial(?int $lineaId = null): void
    {
        $this->modalMaterialId          = null;
        $this->modalMaterialCantidad    = '1.00';
        $this->modalMaterialPrecioVenta = '0';
        $this->editandoLineaMaterialId  = 0; // 0 = fila nueva

        if ($lineaId !== null && $this->albaran !== null) {
            $linea = $this->albaran->lineasMaterial->find($lineaId);
            if ($linea !== null) {
                $this->editandoLineaMaterialId  = $linea->id;
                $this->modalMaterialId          = $linea->material_id;
                $this->modalMaterialCantidad    = (string) $linea->cantidad;
                $this->modalMaterialPrecioVenta = (string) ((float) $linea->material_precio_venta_snapshot);
            }
        }

        $this->resetErrorBag();
    }

    public function cerrarModalMaterial(): void
    {
        $this->editandoLineaMaterialId = null;
        $this->resetErrorBag();
    }

    public function guardarMaterial(): void
    {
        $this->validate([
            'modalMaterialId'          => ['required', 'integer', 'exists:materiales,id'],
            'modalMaterialCantidad'    => ['required', 'numeric', 'min:0.01'],
            'modalMaterialPrecioVenta' => ['required', 'numeric', 'min:0'],
        ], [
            'modalMaterialId.required'       => 'Selecciona un material.',
            'modalMaterialCantidad.required' => 'La cantidad es obligatoria.',
            'modalMaterialCantidad.min'      => 'La cantidad debe ser mayor que 0.',
        ]);

        if ($this->editandoLineaMaterialId > 0) {
            $linea = $this->albaran?->lineasMaterial()->find($this->editandoLineaMaterialId);
            $linea?->update([
                'material_id'                    => $this->modalMaterialId,
                'cantidad'                       => $this->modalMaterialCantidad,
                'material_precio_venta_snapshot' => $this->modalMaterialPrecioVenta,
            ]);
        } else {
            $this->albaran?->lineasMaterial()->create([
                'material_id' => $this->modalMaterialId,
                'cantidad'    => $this->modalMaterialCantidad,
                // precio_venta_snapshot lo rellena el observer desde el catálogo
            ]);
        }

        $this->albaran?->load('lineasMaterial.material');
        $this->editandoLineaMaterialId = null;
    }

    public function confirmarEliminarMaterial(int $lineaId): void
    {
        $this->confirmarEliminarLineaMaterialId = $lineaId;
    }

    public function cancelarEliminarMaterial(): void
    {
        $this->confirmarEliminarLineaMaterialId = null;
    }

    public function eliminarMaterial(): void
    {
        if ($this->confirmarEliminarLineaMaterialId === null) {
            return;
        }

        $this->albaran?->lineasMaterial()
            ->find($this->confirmarEliminarLineaMaterialId)
            ?->delete();

        $this->albaran?->load('lineasMaterial.material');
        $this->confirmarEliminarLineaMaterialId = null;
    }

    // ── Costes / Gastos ──────────────────────────────────────────────────────

    public function abrirModalCostes(int $lineaId): void
    {
        if ($this->albaran === null) {
            return;
        }
        Gate::authorize('update', $this->albaran);

        $linea = $this->albaran->lineasPersonal->find($lineaId);
        if ($linea === null) {
            return;
        }

        $this->editandoCostesLineaId      = $lineaId;
        $this->modalCosteTarifaHora       = (string) ((float) $linea->tarifa_hora_snapshot);
        $this->modalCosteTarifaExtra      = (string) ((float) $linea->tarifa_extra_snapshot);
        $this->modalCosteTarifaPlusReten  = (string) ((float) $linea->tarifa_plus_retencion_snapshot);
        $this->modalCosteTasaHora         = (string) ((float) $linea->trabajador_tasa_hora_snapshot);
        $this->modalCosteTasaExtra        = (string) ((float) $linea->trabajador_tasa_extra_snapshot);
        $this->modalCosteTasaPlusReten    = (string) ((float) $linea->trabajador_tasa_plus_retencion_snapshot);
        $this->resetErrorBag();
    }

    public function cerrarModalCostes(): void
    {
        $this->editandoCostesLineaId = null;
        $this->resetErrorBag();
    }

    public function guardarCostesLinea(): void
    {
        if ($this->albaran === null || $this->editandoCostesLineaId === null) {
            return;
        }

        $this->validate([
            'modalCosteTarifaHora'      => ['required', 'numeric', 'min:0', 'max:9999.9999'],
            'modalCosteTarifaExtra'     => ['required', 'numeric', 'min:0', 'max:9999.9999'],
            'modalCosteTarifaPlusReten' => ['required', 'numeric', 'min:0', 'max:9999.9999'],
            'modalCosteTasaHora'        => ['required', 'numeric', 'min:0', 'max:9999.999'],
            'modalCosteTasaExtra'       => ['required', 'numeric', 'min:0', 'max:9999.999'],
            'modalCosteTasaPlusReten'   => ['required', 'numeric', 'min:0', 'max:9999.999'],
        ]);

        $linea = $this->albaran->lineasPersonal()->find($this->editandoCostesLineaId);
        $linea?->update([
            'tarifa_hora_snapshot'                    => $this->modalCosteTarifaHora,
            'tarifa_extra_snapshot'                   => $this->modalCosteTarifaExtra,
            'tarifa_plus_retencion_snapshot'          => $this->modalCosteTarifaPlusReten,
            'trabajador_tasa_hora_snapshot'           => $this->modalCosteTasaHora,
            'trabajador_tasa_extra_snapshot'          => $this->modalCosteTasaExtra,
            'trabajador_tasa_plus_retencion_snapshot' => $this->modalCosteTasaPlusReten,
        ]);

        $this->albaran->load('lineasPersonal.trabajador');
        $this->editandoCostesLineaId = null;
    }

    public function abrirEditarPrecioMaterial(int $lineaId): void
    {
        if ($this->albaran === null) {
            return;
        }
        Gate::authorize('update', $this->albaran);

        $linea = $this->albaran->lineasMaterial->find($lineaId);
        if ($linea === null) {
            return;
        }

        $this->editandoPrecioMaterialId = $lineaId;
        $this->modalPrecioMaterial = (string) ((float) $linea->material_precio_venta_snapshot);
        $this->modalPrecioCoste    = (string) ((float) $linea->material_precio_coste_snapshot);
        $this->resetErrorBag();
    }

    public function cerrarEditarPrecioMaterial(): void
    {
        $this->editandoPrecioMaterialId = null;
        $this->resetErrorBag();
    }

    public function guardarPrecioMaterial(): void
    {
        if ($this->albaran === null || $this->editandoPrecioMaterialId === null) {
            return;
        }
        Gate::authorize('update', $this->albaran);

        $this->validate([
            'modalPrecioMaterial' => ['required', 'numeric', 'min:0'],
            'modalPrecioCoste'    => ['required', 'numeric', 'min:0'],
        ]);

        $linea = $this->albaran->lineasMaterial()->find($this->editandoPrecioMaterialId);
        $linea?->update([
            'material_precio_venta_snapshot' => $this->modalPrecioMaterial,
            'material_precio_coste_snapshot' => $this->modalPrecioCoste,
        ]);

        $this->albaran->load('lineasMaterial.material');
        $this->editandoPrecioMaterialId = null;
    }

    // ── CRUD archivos ─────────────────────────────────────────────────────────

    public function abrirModalArchivo(): void
    {
        $this->modalArchivoNombre  = '';
        $this->modalArchivoFichero = null;
        $this->resetErrorBag();
        $this->subiendoArchivo = true;
    }

    public function cerrarModalArchivo(): void
    {
        $this->subiendoArchivo     = false;
        $this->modalArchivoFichero = null;
        $this->modalArchivoNombre  = '';
    }

    public function guardarArchivo(): void
    {
        $empresa    = \App\Models\Empresa::actual();
        $maxMb      = $empresa->archivo_tamano_max_mb ?? 10;
        $maxArchivos = $empresa->archivo_cantidad_max ?? 20;

        if ($this->albaran !== null && $this->albaran->archivos->count() >= $maxArchivos) {
            $this->addError('modalArchivoFichero', "Este albarán ya tiene el máximo de {$maxArchivos} archivos permitidos.");
            return;
        }

        $this->validate([
            'modalArchivoFichero' => [
                'required', 'file',
                'max:' . ($maxMb * 1024),
                'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,csv,txt',
            ],
            'modalArchivoNombre'  => ['nullable', 'string', 'max:200'],
        ], [
            'modalArchivoFichero.required' => 'Selecciona un archivo.',
            'modalArchivoFichero.max'      => "El archivo no puede superar {$maxMb} MB.",
            'modalArchivoFichero.mimes'    => 'Tipo de archivo no permitido. Se aceptan: PDF, JPG, PNG, GIF, WEBP, DOC, DOCX, XLS, XLSX, CSV, TXT.',
        ]);

        if ($this->albaran === null || $this->modalArchivoFichero === null) {
            return;
        }

        $ruta = $this->modalArchivoFichero->store(
            "albaranes/{$this->albaran->id}",
            'public'
        );

        $nombre = trim($this->modalArchivoNombre) !== ''
            ? $this->modalArchivoNombre
            : $this->modalArchivoFichero->getClientOriginalName();

        $this->albaran->archivos()->create([
            'nombre'          => $nombre,
            'ruta'            => $ruta,
            'nombre_original' => $this->modalArchivoFichero->getClientOriginalName(),
            'mime_type'       => $this->modalArchivoFichero->getMimeType(),
            'tamano'          => $this->modalArchivoFichero->getSize(),
            'subido_por'      => Auth::id(),
        ]);

        $this->albaran->load('archivos');
        $this->subiendoArchivo     = false;
        $this->modalArchivoFichero = null;
        $this->modalArchivoNombre  = '';
    }

    public function confirmarEliminarArchivo(int $archivoId): void
    {
        $this->confirmarEliminarArchivoId = $archivoId;
    }

    public function cancelarEliminarArchivo(): void
    {
        $this->confirmarEliminarArchivoId = null;
    }

    public function eliminarArchivo(): void
    {
        if ($this->confirmarEliminarArchivoId === null || $this->albaran === null) {
            return;
        }

        $archivo = $this->albaran->archivos()->find($this->confirmarEliminarArchivoId);

        if ($archivo !== null) {
            Storage::disk('public')->delete($archivo->ruta);
            $archivo->delete();
        }

        $this->albaran->load('archivos');
        $this->confirmarEliminarArchivoId = null;
    }

public function guardar(): void
    {
        $esNuevo = $this->albaran === null;

        if ($esNuevo) {
            Gate::authorize('create', Albaran::class);
        } else {
            Gate::authorize('update', $this->albaran);
        }

        $albaran = $this->form->save();

        session()->flash('status', $esNuevo
            ? "Albarán «{$albaran->numero}» creado correctamente."
            : "Albarán «{$albaran->numero}» actualizado correctamente.");

        $this->redirectRoute('albaranes.editar', ['albaran' => $albaran->getKey()]);
    }

    public function confirmarEliminar(): void
    {
        if ($this->albaran === null) {
            return;
        }

        Gate::authorize('delete', $this->albaran);
        $this->confirmarEliminarId = $this->albaran->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function notificarFirmantes(bool $trabajador, bool $responsable): void
    {
        if ($this->albaran === null) {
            return;
        }

        $empresa = Empresa::actual();

        if (! $empresa->mail_host) {
            session()->flash('error', 'Configura el servidor de correo en Ajustes → Correo antes de enviar notificaciones.');
            return;
        }

        // Configurar mailer con las credenciales de la empresa
        config(['mail.mailers.empresa_smtp' => [
            'transport'  => 'smtp',
            'host'       => $empresa->mail_host,
            'port'       => $empresa->mail_port,
            'encryption' => $empresa->mail_encryption,
            'username'   => $empresa->mail_username,
            'password'   => $empresa->mail_password,
            'timeout'    => 15,
        ]]);

        config(['mail.from' => [
            'address' => $empresa->mail_from_address ?: $empresa->mail_username,
            'name'    => $empresa->mail_from_name ?: config('app.name'),
        ]]);

        $caducidadDias = $empresa->token_caducidad_dias ?? 7;
        $caducaEl = now()->addDays($caducidadDias)->format('d/m/Y');

        /** @var array<int, string> $destinos Lista "Rol (email)" de notificados con éxito */
        $destinos = [];

        if ($trabajador) {
            $email  = $this->form->firma_trabajador_otro_correo;
            $nombre = $this->form->firma_trabajador_otro_nombre;

            if (! $email && $this->form->firma_trabajador_user_id) {
                $user   = User::find($this->form->firma_trabajador_user_id);
                $email  = $user?->email;
                $nombre = $user ? trim($user->nombre . ' ' . $user->apellidos) : null;
            }

            if ($email) {
                if ($this->enviarTokenFirma('trabajador', $email, $nombre, $caducidadDias, $empresa)) {
                    $destinos[] = "Empleado ({$email})";
                }
            } else {
                session()->flash('error', 'El empleado firmante no tiene correo configurado.');
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
                if ($this->enviarTokenFirma('responsable', $email, $nombre, $caducidadDias, $empresa)) {
                    $destinos[] = "Responsable ({$email})";
                }
            } else {
                session()->flash('error', 'El responsable firmante no tiene correo configurado.');
            }
        }

        $this->albaran->load('tokensFirma');

        if ($destinos !== []) {
            $lista = count($destinos) === 1
                ? $destinos[0]
                : implode(' y ', $destinos);

            session()->flash('status', sprintf(
                'Solicitud de firma enviada a %s. El enlace de firma caduca el %s.',
                $lista,
                $caducaEl,
            ));
        }
    }

    private function enviarTokenFirma(string $tipo, string $email, ?string $nombre, int $caducidadDias, Empresa $empresa): bool
    {
        // Invalidar tokens anteriores activos del mismo tipo
        $this->albaran->tokensFirma()
            ->where('tipo_firmante', $tipo)
            ->whereNull('usado_at')
            ->whereNull('invalidado_at')
            ->update(['invalidado_at' => now()]);

        /** @var TokenFirma $tokenFirma */
        $tokenFirma = $this->albaran->tokensFirma()->create([
            'tipo_firmante'       => $tipo,
            'token'               => Str::random(64),
            'email_destino'       => $email,
            'nombre_destino'      => $nombre,
            'caduca_at'           => now()->addDays($caducidadDias),
            'generado_por_user_id' => Auth::id(),
        ]);

        $tokenFirma->load([
            'firmable.proyecto',
            'firmable.cliente',
            'firmable.concepto',
            'firmable.lineasPersonal.trabajador',
        ]);

        try {
            Mail::mailer('empresa_smtp')
                ->to($email, $nombre)
                ->send(new SolicitudFirmaEmail($tokenFirma));
        } catch (\Throwable $e) {
            // Invalidar el token recién creado: no se ha podido notificar.
            $tokenFirma->update(['invalidado_at' => now()]);

            \Log::warning('Fallo enviando notificación de firma', [
                'albaran_id' => $this->albaran->id,
                'tipo'       => $tipo,
                'email'      => $email,
                'mensaje'    => $e->getMessage(),
            ]);

            session()->flash('error', sprintf(
                'No se pudo enviar el correo al %s (%s). Revisa la configuración SMTP en Ajustes → Correo. Detalle: %s',
                $tipo,
                $email,
                $e->getMessage(),
            ));

            return false;
        }

        return true;
    }

    public function eliminar(): void
    {
        if ($this->albaran === null) {
            return;
        }

        Gate::authorize('delete', $this->albaran);
        $numero = $this->albaran->numero;
        $this->albaran->delete();

        session()->flash('status', "Albarán «{$numero}» enviado a papelera.");
        $this->redirectRoute('albaranes.index', navigate: false);
    }

    /* ───────────────────────── Computeds ────────────────────── */

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        $actual = $this->form->proyecto_id;

        return Proyecto::query()
            ->where(function ($q) use ($actual): void {
                $q->where('estado', 'activo');
                if ($actual !== null) {
                    $q->orWhere('id', $actual);
                }
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'cliente_id', 'estado']);
    }

    /** @return Collection<int, Concepto> */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with('conceptos:id,nombre')
            ->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->conceptos->toBase();
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function firmantesInternosDisponibles(): Collection
    {
        $lineaIds  = $this->albaran?->lineasPersonal->pluck('trabajador_id')->filter()->unique()->values()->toArray() ?? [];
        $creadorId = $this->albaran?->creado_por;

        $ids = array_values(array_filter(array_unique(
            array_merge($lineaIds, $creadorId ? [$creadorId] : [])
        )));

        if (empty($ids)) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get(['id', 'numero_empleado', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function responsablesDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        return User::query()
            ->where('activo', true)
            ->role('responsable')
            ->whereHas('proyectos', fn ($q) => $q->where('proyectos.id', $this->form->proyecto_id))
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function trabajadoresDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        $yaEnParte = $this->albaran?->lineasPersonal
            ->when($this->editandoLineaPersonalId > 0, fn ($c) => $c->where('id', '!=', $this->editandoLineaPersonalId))
            ->pluck('trabajador_id')
            ->all() ?? [];

        return User::query()
            ->where('activo', true)
            ->role('trabajador')
            ->whereHas('proyectos', fn ($q) => $q->where('proyectos.id', $this->form->proyecto_id))
            ->when($yaEnParte, fn ($q) => $q->whereNotIn('id', $yaEnParte))
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'numero_empleado', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, Material> */
    #[Computed]
    public function materialesDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with(['materiales' => fn ($q) => $q->where('activo', true)->with('numeroPedido:id,numero')->select('materiales.id', 'materiales.numero_pedido_id', 'materiales.descripcion', 'materiales.unidad_medida', 'materiales.stock')])
            ->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->materiales->toBase();
    }

    public function render(): View
    {
        $titulo   = $this->albaran ? 'Editar albarán' : 'Nuevo albarán';
        $tiposHora = TipoHora::cases();
        $estados   = EstadoAlbaran::cases();

        $tokenTrabajador   = $this->albaran?->tokensFirma->where('tipo_firmante.value', 'trabajador')->sortByDesc('created_at')->first();
        $tokenResponsable  = $this->albaran?->tokensFirma->where('tipo_firmante.value', 'responsable')->sortByDesc('created_at')->first();
        $firmaTrabajador   = $this->albaran?->firmas->where('tipo.value', 'trabajador')->first();
        $firmaResponsable  = $this->albaran?->firmas->where('tipo.value', 'responsable')->first();

        return view('livewire.albaranes.editar', compact(
            'titulo', 'tiposHora', 'estados',
            'tokenTrabajador', 'tokenResponsable',
            'firmaTrabajador', 'firmaResponsable',
        ));
    }
}
