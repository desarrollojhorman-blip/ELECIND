<?php

namespace App\Livewire\Proyectos;

use App\Livewire\Forms\ProyectoForm;
use App\Livewire\Forms\TipoProyectoQuickForm;
use App\Models\Concepto;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'proyectos_lista'])]
class Editar extends Component
{
    public ProyectoForm $form;

    public TipoProyectoQuickForm $tipoForm;

    public ?Proyecto $proyecto = null;

    /** Selector de grupo en UI: '' | id | '__otro__' */
    public string $selectorGrupo = '';

    public ?string $nuevoGrupoNombre = null;

    public ?int $trabajadorAAgregar = null;

    public ?int $responsableAAgregar = null;

    public ?int $materialAAgregar = null;

    public ?int $conceptoAAgregar = null;

    public int $trabajadorSelectKey = 0;

    public int $responsableSelectKey = 0;

    public int $materialSelectKey = 0;

    public int $conceptoSelectKey = 0;

    public bool $modalTipoAbierto = false;

    public ?int $confirmarEliminarId = null;

    public function mount(?Proyecto $proyecto = null): void
    {
        if ($proyecto !== null && $proyecto->exists) {
            Gate::authorize('update', $proyecto);
            $this->proyecto = $proyecto;
            $this->form->fromModel($proyecto);
            $this->selectorGrupo = $this->form->tipo_proyecto_id !== null
                ? (string) $this->form->tipo_proyecto_id
                : '';
        } else {
            Gate::authorize('create', Proyecto::class);
            $this->form->estado = 'activo';
        }
    }

    public function guardar(): void
    {
        $esNuevo = $this->proyecto === null;

        if ($esNuevo) {
            Gate::authorize('create', Proyecto::class);
        } else {
            Gate::authorize('update', $this->proyecto);
        }

        $this->resolverGrupoSeleccionado();

        $proyecto = $this->form->save();

        session()->flash('status', $esNuevo
            ? "Proyecto «{$proyecto->nombre}» creado correctamente."
            : "Proyecto «{$proyecto->nombre}» actualizado correctamente.");

        $this->redirectRoute('proyectos.editar', ['proyecto' => $proyecto->getKey()]);
    }

    /* ───────────────────── Trabajadores ───────────────────── */

    public function agregarTrabajador(): void
    {
        if ($this->proyecto === null) {
            return;
        }

        $this->validate([
            'trabajadorAAgregar' => ['required', 'integer', Rule::exists('users', 'id')],
        ], [], [
            'trabajadorAAgregar' => 'trabajador',
        ]);

        Gate::authorize('update', $this->proyecto);

        $yaExiste = $this->proyecto->usuarios()
            ->where('users.id', $this->trabajadorAAgregar)
            ->exists();

        if ($yaExiste) {
            $this->addError('trabajadorAAgregar', 'Este usuario ya está asignado al proyecto.');

            return;
        }

        $this->proyecto->usuarios()->attach($this->trabajadorAAgregar, ['rol_en_proyecto' => 'trabajador']);
        $this->trabajadorAAgregar = null;
        $this->trabajadorSelectKey++;
    }

    public function quitarTrabajador(int $userId): void
    {
        if ($this->proyecto === null) {
            return;
        }

        Gate::authorize('update', $this->proyecto);

        $this->proyecto->usuarios()
            ->wherePivot('rol_en_proyecto', 'trabajador')
            ->detach($userId);
    }

    /* ───────────────────── Responsables ───────────────────── */

    public function agregarResponsableProyecto(): void
    {
        if ($this->proyecto === null) {
            return;
        }

        $this->validate([
            'responsableAAgregar' => ['required', 'integer', Rule::exists('users', 'id')],
        ], [], [
            'responsableAAgregar' => 'responsable',
        ]);

        Gate::authorize('update', $this->proyecto);

        $yaExiste = $this->proyecto->usuarios()
            ->where('users.id', $this->responsableAAgregar)
            ->exists();

        if ($yaExiste) {
            $this->addError('responsableAAgregar', 'Este usuario ya está asignado al proyecto.');

            return;
        }

        $this->proyecto->usuarios()->attach($this->responsableAAgregar, ['rol_en_proyecto' => 'responsable']);
        $this->responsableAAgregar = null;
        $this->responsableSelectKey++;
    }

    public function quitarResponsableProyecto(int $userId): void
    {
        if ($this->proyecto === null) {
            return;
        }

        Gate::authorize('update', $this->proyecto);

        $this->proyecto->usuarios()
            ->wherePivot('rol_en_proyecto', 'responsable')
            ->detach($userId);
    }

    /* ───────────────────── Materiales ───────────────────── */

    public function agregarMaterialProyecto(): void
    {
        if ($this->proyecto === null) {
            return;
        }

        $this->validate([
            'materialAAgregar' => ['required', 'integer', Rule::exists('materiales', 'id')],
        ], [], [
            'materialAAgregar' => 'material',
        ]);

        Gate::authorize('update', $this->proyecto);

        $yaExiste = $this->proyecto->materiales()
            ->where('materiales.id', $this->materialAAgregar)
            ->exists();

        if ($yaExiste) {
            $this->addError('materialAAgregar', 'Este material ya está asignado al proyecto.');

            return;
        }

        $this->proyecto->materiales()->attach($this->materialAAgregar);
        $this->materialAAgregar = null;
        $this->materialSelectKey++;
    }

    public function quitarMaterialProyecto(int $materialId): void
    {
        if ($this->proyecto === null) {
            return;
        }

        Gate::authorize('update', $this->proyecto);

        $this->proyecto->materiales()->detach($materialId);
    }

    /* ───────────────────── Conceptos ───────────────────── */

    public function agregarConceptoProyecto(): void
    {
        if ($this->proyecto === null) {
            return;
        }

        $this->validate([
            'conceptoAAgregar' => ['required', 'integer', Rule::exists('conceptos', 'id')],
        ], [], [
            'conceptoAAgregar' => 'concepto',
        ]);

        Gate::authorize('update', $this->proyecto);

        $yaExiste = $this->proyecto->conceptos()
            ->where('conceptos.id', $this->conceptoAAgregar)
            ->exists();

        if ($yaExiste) {
            $this->addError('conceptoAAgregar', 'Este concepto ya está asignado al proyecto.');

            return;
        }

        $this->proyecto->conceptos()->attach($this->conceptoAAgregar);
        $this->conceptoAAgregar = null;
        $this->conceptoSelectKey++;
    }

    public function quitarConceptoProyecto(int $conceptoId): void
    {
        if ($this->proyecto === null) {
            return;
        }

        Gate::authorize('update', $this->proyecto);

        $this->proyecto->conceptos()->detach($conceptoId);
    }

    /* ───────────── Sub-modal: crear tipo de proyecto al vuelo ───────────── */

    public function abrirModalTipo(): void
    {
        Gate::authorize('create', TiposProyecto::class);

        $this->tipoForm->reset();
        $this->resetErrorBag('tipoForm.*');
        $this->modalTipoAbierto = true;
    }

    public function cerrarModalTipo(): void
    {
        $this->modalTipoAbierto = false;
        $this->tipoForm->reset();
        $this->resetErrorBag('tipoForm.*');
    }

    public function guardarTipo(): void
    {
        Gate::authorize('create', TiposProyecto::class);

        $tipo = $this->tipoForm->save();

        // Auto-seleccionar el nuevo tipo en el form principal
        $this->form->tipo_proyecto_id = (int) $tipo->getKey();
        $this->selectorGrupo = (string) $tipo->getKey();
        $this->nuevoGrupoNombre = null;

        $this->modalTipoAbierto = false;
        $this->tipoForm->reset();

        session()->flash('status', "Tipo «{$tipo->nombre}» creado y seleccionado.");
    }

    /* ───────────────────── Eliminar ───────────────────── */

    public function confirmarEliminar(): void
    {
        if ($this->proyecto === null) {
            return;
        }

        Gate::authorize('delete', $this->proyecto);
        $this->confirmarEliminarId = $this->proyecto->id;
    }

    public function cancelarEliminar(): void
    {
        $this->confirmarEliminarId = null;
    }

    public function eliminar(): void
    {
        if ($this->proyecto === null) {
            return;
        }

        Gate::authorize('delete', $this->proyecto);
        $nombre = $this->proyecto->nombre;
        $this->proyecto->delete();

        session()->flash('status', "Proyecto «{$nombre}» enviado a papelera.");
        $this->redirectRoute('proyectos.index', navigate: true);
    }

    /* ───────────────────────── Computeds ────────────────────── */

    /**
     * @return Collection<int, TiposProyecto>
     */
    #[Computed]
    public function tiposDisponibles(): Collection
    {
        return TiposProyecto::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /**
     * @return Collection<int, \App\Models\Cliente>
     */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return \App\Models\Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesDisponibles(): Collection
    {
        return User::query()
            ->where('tipo_usuario', 'interno')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function trabajadoresDisponibles(): Collection
    {
        if ($this->proyecto === null) {
            return collect();
        }

        $asignados = $this->trabajadoresProyecto->pluck('id')->all();

        return User::query()
            ->where('tipo_usuario', 'interno')
            ->where('activo', true)
            ->whereNotIn('id', $asignados)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesProyectoDisponibles(): Collection
    {
        if ($this->proyecto === null) {
            return collect();
        }

        $asignados = $this->responsablesProyecto->pluck('id')->all();

        return User::query()
            ->where('tipo_usuario', 'externo')
            ->where('activo', true)
            ->whereNotIn('id', $asignados)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function trabajadoresProyecto(): Collection
    {
        return $this->usuariosProyectoPorRol('trabajador');
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesProyecto(): Collection
    {
        return $this->usuariosProyectoPorRol('responsable');
    }

    /**
     * @return Collection<int, Material>
     */
    #[Computed]
    public function materialesProyecto(): Collection
    {
        if ($this->proyecto === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with(['materiales' => function ($q): void {
                $q->orderBy('descripcion');
            }])
            ->find($this->proyecto->id);

        if (! $proyecto instanceof Proyecto) {
            return collect();
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Material> $materiales */
        $materiales = $proyecto->materiales;

        return $materiales->toBase();
    }

    /**
     * @return Collection<int, Material>
     */
    #[Computed]
    public function materialesDisponibles(): Collection
    {
        if ($this->proyecto === null) {
            return collect();
        }

        $asignados = $this->materialesProyecto->pluck('id')->all();

        return Material::query()
            ->whereNotIn('id', $asignados)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'unidad_medida', 'stock']);
    }

    /**
     * @return Collection<int, Concepto>
     */
    #[Computed]
    public function conceptosProyecto(): Collection
    {
        if ($this->proyecto === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()
            ->with(['conceptos' => function ($q): void {
                $q->orderBy('nombre');
            }])
            ->find($this->proyecto->id);

        if (! $proyecto instanceof Proyecto) {
            return collect();
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Concepto> $conceptos */
        $conceptos = $proyecto->conceptos;

        return $conceptos->toBase();
    }

    /**
     * @return Collection<int, Concepto>
     */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        if ($this->proyecto === null) {
            return collect();
        }

        $asignados = $this->conceptosProyecto->pluck('id')->all();

        return Concepto::query()
            ->whereNotIn('id', $asignados)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /* ───────────────────────── Render ───────────────────────────────── */

    public function render(): View
    {
        $titulo = $this->proyecto ? 'Editar proyecto' : 'Nuevo proyecto';
        $backUrl = route('proyectos.index');

        return view('livewire.proyectos.editar', compact('titulo', 'backUrl'));
    }

    /* ───────────────────────── Privados ────────────────────── */

    private function resolverGrupoSeleccionado(): void
    {
        if ($this->selectorGrupo === '__otro__') {
            $this->validate([
                'nuevoGrupoNombre' => ['required', 'string', 'max:255'],
            ], [], [
                'nuevoGrupoNombre' => 'nuevo grupo',
            ]);

            $nombre = trim((string) $this->nuevoGrupoNombre);

            $grupoExistente = TiposProyecto::withTrashed()
                ->where('nombre', $nombre)
                ->first();

            if ($grupoExistente !== null) {
                if ($grupoExistente->trashed()) {
                    $grupoExistente->restore();
                }

                if (! $grupoExistente->activo) {
                    $grupoExistente->activo = true;
                    $grupoExistente->save();
                }

                $this->form->tipo_proyecto_id = (int) $grupoExistente->getKey();
                $this->selectorGrupo = (string) $grupoExistente->getKey();

                return;
            }

            $grupo = TiposProyecto::create([
                'nombre' => $nombre,
                'descripcion' => null,
                'activo' => true,
            ]);

            $this->form->tipo_proyecto_id = (int) $grupo->getKey();
            $this->selectorGrupo = (string) $grupo->getKey();

            return;
        }

        $this->nuevoGrupoNombre = null;

        if ($this->selectorGrupo === '') {
            $this->form->tipo_proyecto_id = null;

            return;
        }

        $this->form->tipo_proyecto_id = (int) $this->selectorGrupo;
    }

    /**
     * @return Collection<int, User>
     */
    private function usuariosProyectoPorRol(string $rol): Collection
    {
        if ($this->proyecto === null) {
            return collect();
        }

        /** @var Proyecto|null $proy */
        $proy = Proyecto::query()
            ->with(['usuarios' => function ($q) use ($rol): void {
                $q->wherePivot('rol_en_proyecto', $rol)
                    ->orderBy('nombre')
                    ->orderBy('apellidos');
            }])
            ->find($this->proyecto->id);

        if (! $proy instanceof Proyecto) {
            return collect();
        }

        /** @var EloquentCollection<int, User> $usuarios */
        $usuarios = $proy->usuarios;

        return $usuarios->toBase();
    }
}
