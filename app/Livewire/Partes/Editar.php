<?php

namespace App\Livewire\Partes;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Livewire\Forms\ParteForm;
use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\Material;
use App\Models\Parte;
use App\Models\Proyecto;
use App\Models\User;
use App\Services\NumeracionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Crear/editar partes.
 *
 * Clon estructural de Albaranes/Editar:
 *  - Tab "Parte" → cabecera (proyecto, concepto, responsable, fecha, tipo
 *    de jornada, observaciones).
 *  - Tab "Trabajadores" → CRUD inline con modal (trabajador, horas,
 *    horas_extra).
 *  - Tab "Materiales" → CRUD inline con modal (material, cantidad).
 *  - SIN firmas ni archivos.
 *  - Botón "Generar albarán" → clona cabecera + líneas al modelo Albaran y
 *    vincula `partes.albaran_id`.
 */
#[Layout('components.layouts.web', ['active' => 'partes'])]
#[Title('Editar parte')]
class Editar extends Component
{
    public ?Parte $parte = null;

    public ParteForm $form;

    public bool $modoCrear = true;

    /* ── Estado de modal "Trabajador" ─────────────────────────── */

    public ?int $editandoLineaPersonalId = null;

    public ?int $modalTrabajadorUserId = null;

    public string $modalTrabajadorHoras = '8.00';

    public string $modalTrabajadorHorasExtra = '0.00';

    public ?int $confirmarEliminarLineaPersonalId = null;

    /* ── Estado de modal "Material" ───────────────────────────── */

    public ?int $editandoLineaMaterialId = null;

    public ?int $modalMaterialId = null;

    public string $modalMaterialCantidad = '1.00';

    public ?int $confirmarEliminarLineaMaterialId = null;

    /* ── Confirmar eliminación del parte ──────────────────────── */

    public ?int $confirmarEliminarId = null;

    public function mount(?Parte $parte = null): void
    {
        $this->parte = $parte;
        $this->modoCrear = $parte === null;

        if ($parte !== null) {
            // Si el parte tiene albarán generado, el form pasa a modo lectura
            // (solo se necesita `view`). El usuario debe eliminar el albarán
            // primero si quiere desbloquear el parte.
            if ($parte->tieneAlbaran()) {
                Gate::authorize('view', $parte);
            } else {
                Gate::authorize('update', $parte);
            }
            $parte->load([
                'lineasPersonal.trabajador',
                'lineasMaterial.material',
            ]);
            $this->form->fromModel($parte);
        } else {
            Gate::authorize('create', Parte::class);
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    /** Helper para el blade: ¿está el parte bloqueado (porque tiene albarán)? */
    public function isBloqueado(): bool
    {
        return $this->parte?->tieneAlbaran() ?? false;
    }

    public function updatedFormProyectoId(): void
    {
        $this->form->sincronizarClienteDesdeProyecto();
    }

    public function guardar(): mixed
    {
        if ($this->parte !== null) {
            Gate::authorize('update', $this->parte);
        } else {
            Gate::authorize('create', Parte::class);
        }

        $parte = $this->form->save();
        session()->flash('status', "Parte «{$parte->numero}» guardado correctamente.");

        return $this->redirect(route('partes.editar', $parte), navigate: true);
    }

    public function deshacer(): mixed
    {
        if ($this->parte !== null) {
            return $this->redirect(route('partes.editar', $this->parte), navigate: true);
        }

        return $this->redirect(route('partes.crear'), navigate: true);
    }

    public function confirmarEliminar(): void
    {
        if ($this->parte === null) {
            return;
        }
        Gate::authorize('delete', $this->parte);
        $this->confirmarEliminarId = $this->parte->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): mixed
    {
        if ($this->parte === null) {
            return null;
        }
        Gate::authorize('delete', $this->parte);
        $numero = $this->parte->numero;
        $this->parte->delete();
        session()->flash('status', "Parte «{$numero}» eliminado.");

        return $this->redirect(route('partes.index'), navigate: true);
    }

    /* ─────────────────────────────────────────────────────────────
     * CRUD trabajadores (partes_lineas_personal)
     * ──────────────────────────────────────────────────────────── */

    public function abrirModalTrabajador(?int $lineaId = null): void
    {
        if ($this->parte === null) {
            return;
        }
        Gate::authorize('update', $this->parte);

        $this->resetErrorBag();
        $this->modalTrabajadorUserId     = null;
        $this->modalTrabajadorHoras      = '8.00';
        $this->modalTrabajadorHorasExtra = '0.00';
        $this->editandoLineaPersonalId   = 0;

        if ($lineaId !== null) {
            $linea = $this->parte->lineasPersonal->find($lineaId);
            if ($linea !== null) {
                $this->editandoLineaPersonalId   = $linea->id;
                $this->modalTrabajadorUserId     = $linea->trabajador_id;
                $this->modalTrabajadorHoras      = (string) $linea->horas;
                $this->modalTrabajadorHorasExtra = (string) $linea->horas_extra;
            }
        }
    }

    public function cerrarModalTrabajador(): void
    {
        $this->editandoLineaPersonalId = null;
        $this->resetErrorBag();
    }

    public function guardarTrabajador(): void
    {
        if ($this->parte === null) {
            return;
        }
        Gate::authorize('update', $this->parte);

        $this->validate([
            'modalTrabajadorUserId'     => ['required', 'integer', 'exists:users,id'],
            'modalTrabajadorHoras'      => ['required', 'numeric', 'min:0', 'max:24'],
            'modalTrabajadorHorasExtra' => ['required', 'numeric', 'min:0', 'max:24'],
        ], [
            'modalTrabajadorUserId.required' => 'Selecciona un trabajador.',
            'modalTrabajadorHoras.required'  => 'Las horas son obligatorias.',
        ]);

        if ($this->editandoLineaPersonalId > 0) {
            $linea = $this->parte->lineasPersonal()->find($this->editandoLineaPersonalId);
            $linea?->update([
                'trabajador_id' => $this->modalTrabajadorUserId,
                'horas'         => $this->modalTrabajadorHoras,
                'horas_extra'   => $this->modalTrabajadorHorasExtra,
            ]);
        } else {
            $this->parte->lineasPersonal()->create([
                'trabajador_id' => $this->modalTrabajadorUserId,
                'horas'         => $this->modalTrabajadorHoras,
                'horas_extra'   => $this->modalTrabajadorHorasExtra,
            ]);
        }

        $this->parte->load('lineasPersonal.trabajador');
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
        if ($this->parte === null || $this->confirmarEliminarLineaPersonalId === null) {
            return;
        }
        Gate::authorize('update', $this->parte);

        $this->parte->lineasPersonal()
            ->find($this->confirmarEliminarLineaPersonalId)
            ?->delete();
        $this->parte->load('lineasPersonal.trabajador');
        $this->confirmarEliminarLineaPersonalId = null;
    }

    /* ─────────────────────────────────────────────────────────────
     * CRUD materiales (partes_lineas_material)
     * ──────────────────────────────────────────────────────────── */

    public function abrirModalMaterial(?int $lineaId = null): void
    {
        if ($this->parte === null) {
            return;
        }
        Gate::authorize('update', $this->parte);

        $this->resetErrorBag();
        $this->modalMaterialId         = null;
        $this->modalMaterialCantidad   = '1.00';
        $this->editandoLineaMaterialId = 0;

        if ($lineaId !== null) {
            $linea = $this->parte->lineasMaterial->find($lineaId);
            if ($linea !== null) {
                $this->editandoLineaMaterialId = $linea->id;
                $this->modalMaterialId         = $linea->material_id;
                $this->modalMaterialCantidad   = (string) $linea->cantidad;
            }
        }
    }

    public function cerrarModalMaterial(): void
    {
        $this->editandoLineaMaterialId = null;
        $this->resetErrorBag();
    }

    public function guardarMaterial(): void
    {
        if ($this->parte === null) {
            return;
        }
        Gate::authorize('update', $this->parte);

        $this->validate([
            'modalMaterialId'       => ['required', 'integer', 'exists:materiales,id'],
            'modalMaterialCantidad' => ['required', 'numeric', 'min:0.01'],
        ], [
            'modalMaterialId.required'       => 'Selecciona un material.',
            'modalMaterialCantidad.required' => 'La cantidad es obligatoria.',
            'modalMaterialCantidad.min'      => 'La cantidad debe ser mayor que 0.',
        ]);

        if ($this->editandoLineaMaterialId > 0) {
            $linea = $this->parte->lineasMaterial()->find($this->editandoLineaMaterialId);
            $linea?->update([
                'material_id' => $this->modalMaterialId,
                'cantidad'    => $this->modalMaterialCantidad,
            ]);
        } else {
            $this->parte->lineasMaterial()->create([
                'material_id' => $this->modalMaterialId,
                'cantidad'    => $this->modalMaterialCantidad,
            ]);
        }

        $this->parte->load('lineasMaterial.material');
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
        if ($this->parte === null || $this->confirmarEliminarLineaMaterialId === null) {
            return;
        }
        Gate::authorize('update', $this->parte);

        $this->parte->lineasMaterial()
            ->find($this->confirmarEliminarLineaMaterialId)
            ?->delete();
        $this->parte->load('lineasMaterial.material');
        $this->confirmarEliminarLineaMaterialId = null;
    }

    /* ─────────────────────────────────────────────────────────────
     * Generar albarán desde el parte
     * ──────────────────────────────────────────────────────────── */

    public function generarAlbaran(): mixed
    {
        if ($this->parte === null) {
            return null;
        }
        Gate::authorize('update', $this->parte);

        if ($this->parte->tieneAlbaran()) {
            session()->flash('status', 'Este parte ya tiene un albarán generado.');

            return null;
        }

        if ($this->parte->lineasPersonal->isEmpty() && $this->parte->lineasMaterial->isEmpty()) {
            session()->flash('status', 'Añade al menos una línea (trabajador o material) antes de generar el albarán.');

            return null;
        }

        $albaran = DB::transaction(function (): Albaran {
            $numero = app(NumeracionService::class)->siguienteNumeroAlbaran(Carbon::parse($this->parte->fecha));

            $albaran = Albaran::create([
                'numero' => $numero,
                'fecha' => $this->parte->fecha,
                'cliente_id' => $this->parte->cliente_id,
                'proyecto_id' => $this->parte->proyecto_id,
                'concepto_id' => $this->parte->concepto_id,
                'creado_por' => $this->parte->creado_por ?? (int) Auth::id(),
                'responsable_id' => $this->parte->responsable_id,
                'estado' => EstadoAlbaran::PENDIENTE_FIRMA,
                'tipo_hora' => TipoHora::from($this->parte->tipo_hora ?? 'laboral'),
                'observaciones' => $this->parte->observaciones,
                'es_personalizado' => $this->parte->es_personalizado,
                'cliente_texto' => $this->parte->cliente_texto,
                'proyecto_texto' => $this->parte->proyecto_texto,
                'concepto_texto' => $this->parte->concepto_texto,
                'responsable_texto' => $this->parte->responsable_texto,
            ]);

            foreach ($this->parte->lineasPersonal as $linea) {
                AlbaranLineaPersonal::create([
                    'albaran_id' => $albaran->id,
                    'trabajador_id' => $linea->trabajador_id,
                    'horas' => $linea->horas,
                    'horas_extra' => $linea->horas_extra,
                ]);
            }

            foreach ($this->parte->lineasMaterial as $linea) {
                AlbaranLineaMaterial::create([
                    'albaran_id' => $albaran->id,
                    'material_id' => $linea->material_id,
                    'cantidad' => $linea->cantidad,
                ]);
            }

            $this->parte->albaran_id = $albaran->id;
            $this->parte->estado = Parte::ESTADO_CERRADO;
            $this->parte->save();

            return $albaran;
        });

        session()->flash('status', "Albarán «{$albaran->numero}» generado correctamente.");

        return $this->redirect(route('partes.editar', $this->parte), navigate: true);
    }

    /* ── Computeds ────────────────────────────────────────────── */

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

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return Cliente::query()->orderBy('nombre')->get(['id', 'nombre']);
    }

    /** @return Collection<int, Concepto> */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }
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

        $yaEnParte = $this->parte?->lineasPersonal
            ->when($this->editandoLineaPersonalId > 0, fn ($c) => $c->where('id', '!=', $this->editandoLineaPersonalId))
            ->pluck('trabajador_id')
            ->filter()
            ->all() ?? [];

        return User::query()
            ->where('activo', true)
            ->whereDoesntHave('roles', fn ($q) => $q->where('es_externo', true))
            ->whereHas('proyectos', fn ($q) => $q->where('proyectos.id', $this->form->proyecto_id))
            ->when(! empty($yaEnParte), fn ($q) => $q->whereNotIn('id', $yaEnParte))
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos', 'numero_empleado']);
    }

    /** @return Collection<int, Material> */
    #[Computed]
    public function materialesDisponibles(): Collection
    {
        return Material::query()
            ->where('activo', true)
            ->with(['numeroPedido:id,numero', 'familia:id,nombre'])
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'unidad_medida', 'stock', 'numero_pedido_id', 'familia_id']);
    }

    public function render(): View
    {
        return view('livewire.partes.editar', [
            'titulo' => $this->modoCrear ? 'Nuevo parte' : 'Editar parte',
            'tiposHora' => TipoHora::cases(),
        ]);
    }
}
