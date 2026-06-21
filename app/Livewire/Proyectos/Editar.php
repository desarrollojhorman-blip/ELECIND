<?php

namespace App\Livewire\Proyectos;

use App\Livewire\Forms\ProyectoForm;
use App\Models\Albaran;
use App\Models\Concepto;
use App\Models\Empresa;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use App\Models\User;
use App\Services\NumeracionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.web', ['active' => 'proyectos_lista'])]
class Editar extends Component
{
    use WithFileUploads;

    public ProyectoForm $form;

    public ?Proyecto $proyecto = null;

    /** Selector de grupo en UI: '' | id | '__otro__' */
    public string $selectorGrupo = '';

    public ?int $trabajadorAAgregar = null;

    public ?int $responsableAAgregar = null;

    public ?int $materialAAgregar = null;

    public ?int $conceptoAAgregar = null;

    public int $trabajadorSelectKey = 0;

    public int $responsableSelectKey = 0;

    public int $materialSelectKey = 0;

    public int $conceptoSelectKey = 0;

    public ?int $confirmarEliminarId = null;

    // ── Inline archivo ───────────────────────────────────────────────────────
    public bool $subiendoArchivo = false;
    public string $modalArchivoNombre = '';
    public ?TemporaryUploadedFile $modalArchivoFichero = null;
    public ?int $confirmarEliminarArchivoId = null;

    public string $ordenTrabajadoresColumna = 'nombre';

    public string $ordenTrabajadoresDireccion = 'asc';

    public string $ordenResponsablesColumna = 'nombre';

    public string $ordenResponsablesDireccion = 'asc';

    public string $ordenConceptosColumna = 'nombre';

    public string $ordenConceptosDireccion = 'asc';

    public string $ordenMaterialesColumna = 'descripcion';

    public string $ordenMaterialesDireccion = 'asc';

    public string $ordenAlbaranesColumna = 'fecha';

    public string $ordenAlbaranesDireccion = 'desc';

    public function deshacer(): void
    {
        if ($this->proyecto !== null) {
            $this->form->fromModel($this->proyecto);
        } else {
            $this->form->reset();
            $this->form->estado = 'activo';
            $this->form->codigo = app(NumeracionService::class)->siguienteNumeroProyecto();
            $this->form->fecha_inicio = now()->format('Y-m-d');
        }
    }

    public function mount(?Proyecto $proyecto = null): void
    {
        if ($proyecto !== null && $proyecto->exists) {
            Gate::authorize('update', $proyecto);
            $this->proyecto = $proyecto;
            $this->proyecto->load(['archivos']);
            $this->form->fromModel($proyecto);
            if ($this->form->codigo === null) {
                $this->form->codigo = app(NumeracionService::class)->siguienteNumeroProyecto();
            }
            $this->selectorGrupo = $this->form->tipo_proyecto_id !== null
                ? (string) $this->form->tipo_proyecto_id
                : '';
        } else {
            Gate::authorize('create', Proyecto::class);
            $this->form->estado = 'activo';
            $this->form->codigo = app(NumeracionService::class)->siguienteNumeroProyecto();
            $this->form->fecha_inicio = now()->format('Y-m-d');
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
        $empresa     = Empresa::actual();
        $maxMb       = $empresa->archivo_tamano_max_mb ?? 10;
        $maxArchivos = $empresa->archivo_cantidad_max ?? 20;

        if ($this->proyecto !== null && $this->proyecto->archivos->count() >= $maxArchivos) {
            $this->addError('modalArchivoFichero', "Este proyecto ya tiene el máximo de {$maxArchivos} archivos permitidos.");
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

        if ($this->proyecto === null || $this->modalArchivoFichero === null) {
            return;
        }

        $ruta = $this->modalArchivoFichero->store(
            "proyectos/{$this->proyecto->id}",
            'public'
        );

        $nombre = trim($this->modalArchivoNombre) !== ''
            ? $this->modalArchivoNombre
            : $this->modalArchivoFichero->getClientOriginalName();

        $this->proyecto->archivos()->create([
            'nombre'          => $nombre,
            'ruta'            => $ruta,
            'nombre_original' => $this->modalArchivoFichero->getClientOriginalName(),
            'mime_type'       => $this->modalArchivoFichero->getMimeType(),
            'tamano'          => $this->modalArchivoFichero->getSize(),
            'subido_por'      => Auth::id(),
        ]);

        $this->proyecto->load('archivos');
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
        if ($this->confirmarEliminarArchivoId === null || $this->proyecto === null) {
            return;
        }

        $archivo = $this->proyecto->archivos()->find($this->confirmarEliminarArchivoId);

        if ($archivo !== null) {
            Storage::disk('public')->delete($archivo->ruta);
            $archivo->delete();
        }

        $this->proyecto->load('archivos');
        $this->confirmarEliminarArchivoId = null;
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

        // Verificar que el usuario realmente tenga rol "trabajador" (defensa
        // contra manipulación del payload por DOM/Livewire).
        $user = User::find($this->trabajadorAAgregar);
        if ($user === null || ! $user->hasRole('trabajador')) {
            $this->addError('trabajadorAAgregar', 'Solo se pueden asignar usuarios con rol trabajador.');

            return;
        }

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

        // Forzar re-render del select para que el trabajador vuelva a
        // estar disponible inmediatamente sin recargar la página.
        $this->trabajadorSelectKey++;
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

        // Forzar re-render del select para que el responsable vuelva a
        // estar disponible inmediatamente.
        $this->responsableSelectKey++;
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

        $this->materialSelectKey++;
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

        $this->conceptoSelectKey++;
    }

    /* ───────────────────── Ordenación tabs ────────────── */

    public function ordenarTrabajadoresPor(string $columna): void
    {
        if (! \in_array($columna, ['nombre'], true)) {
            return;
        }
        $this->ordenTrabajadoresDireccion = $this->ordenTrabajadoresColumna === $columna
            ? ($this->ordenTrabajadoresDireccion === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->ordenTrabajadoresColumna = $columna;
    }

    public function ordenarResponsablesPor(string $columna): void
    {
        if (! \in_array($columna, ['nombre'], true)) {
            return;
        }
        $this->ordenResponsablesDireccion = $this->ordenResponsablesColumna === $columna
            ? ($this->ordenResponsablesDireccion === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->ordenResponsablesColumna = $columna;
    }

    public function ordenarConceptosPor(string $columna): void
    {
        if (! \in_array($columna, ['nombre'], true)) {
            return;
        }
        $this->ordenConceptosDireccion = $this->ordenConceptosColumna === $columna
            ? ($this->ordenConceptosDireccion === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->ordenConceptosColumna = $columna;
    }

    public function ordenarMaterialesPor(string $columna): void
    {
        if (! \in_array($columna, ['descripcion', 'stock'], true)) {
            return;
        }
        $this->ordenMaterialesDireccion = $this->ordenMaterialesColumna === $columna
            ? ($this->ordenMaterialesDireccion === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->ordenMaterialesColumna = $columna;
    }

    /* ───────────────────── Albaranes ──────────────────── */

    public function ordenarAlbaranesPor(string $columna): void
    {
        $permitidas = ['numero', 'fecha', 'estado'];
        if (! \in_array($columna, $permitidas, true)) {
            return;
        }

        if ($this->ordenAlbaranesColumna === $columna) {
            $this->ordenAlbaranesDireccion = $this->ordenAlbaranesDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenAlbaranesColumna = $columna;
            $this->ordenAlbaranesDireccion = 'asc';
        }
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
        $this->redirectRoute('proyectos.index', navigate: false);
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
            ->get(['id', 'codigo_cliente', 'nombre']);
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
            ->role('trabajador')
            ->where('activo', true)
            ->whereNotIn('id', $asignados)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'numero_empleado', 'nombre', 'apellidos']);
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
            ->when($this->form->cliente_id, fn ($q) => $q->where('cliente_id', $this->form->cliente_id))
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
        return $this->usuariosProyectoPorRol('trabajador', $this->ordenTrabajadoresColumna, $this->ordenTrabajadoresDireccion);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesProyecto(): Collection
    {
        return $this->usuariosProyectoPorRol('responsable', $this->ordenResponsablesColumna, $this->ordenResponsablesDireccion);
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
                $q->orderBy($this->ordenMaterialesColumna, $this->ordenMaterialesDireccion);
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
            ->with('numeroPedido:id,numero')
            ->where('activo', true)
            ->whereNotIn('id', $asignados)
            ->orderBy('descripcion')
            ->get(['id', 'numero_pedido_id', 'descripcion', 'unidad_medida', 'stock']);
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
                $q->orderBy($this->ordenConceptosColumna, $this->ordenConceptosDireccion);
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

    /** @return EloquentCollection<int, Albaran> */
    #[Computed]
    public function albaranesDelProyecto(): EloquentCollection
    {
        if ($this->proyecto === null) {
            return new EloquentCollection;
        }

        return Albaran::query()
            ->where('proyecto_id', $this->proyecto->id)
            ->orderBy($this->ordenAlbaranesColumna, $this->ordenAlbaranesDireccion)
            ->orderBy('id', $this->ordenAlbaranesDireccion)
            ->get(['id', 'numero', 'fecha', 'estado', 'creado_por']);
    }

    /* ───────────────────────── Privados ────────────────────── */

    private function resolverGrupoSeleccionado(): void
    {
        if ($this->selectorGrupo === '') {
            $this->form->tipo_proyecto_id = null;

            return;
        }

        $this->form->tipo_proyecto_id = (int) $this->selectorGrupo;
    }

    /**
     * @return Collection<int, User>
     */
    private function usuariosProyectoPorRol(string $rol, string $columna = 'nombre', string $direccion = 'asc'): Collection
    {
        if ($this->proyecto === null) {
            return collect();
        }

        /** @var Proyecto|null $proy */
        $proy = Proyecto::query()
            ->with(['usuarios' => function ($q) use ($rol, $columna, $direccion): void {
                $q->wherePivot('rol_en_proyecto', $rol)
                    ->orderBy($columna, $direccion)
                    ->orderBy('apellidos', $direccion);
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
