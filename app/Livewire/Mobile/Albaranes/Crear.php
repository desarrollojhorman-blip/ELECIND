<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Enums\TipoHora;
use App\Livewire\Forms\AlbaranForm;
use App\Models\Albaran;
use App\Models\Concepto;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Crear extends Component
{
    public AlbaranForm $form;

    public ?Albaran $albaran = null;

    /** ID del albarán recién creado, mientras el modal de firma está visible */
    public ?int $albaranCreadoId = null;

    public int $selectKey = 0;

    public function mount(?Albaran $albaran = null): void
    {
        if ($albaran !== null && $albaran->exists) {
            Gate::authorize('update', $albaran);
            $this->albaran = $albaran->loadMissing(['lineasPersonal', 'lineasMaterial.material']);
            $this->form->fromModel($this->albaran);
        } else {
            Gate::authorize('create', Albaran::class);
            $this->form->fecha = now()->format('Y-m-d');
        }
    }

    public function updatedFormProyectoId(): void
    {
        $this->form->sincronizarClienteDesdeProyecto();
        $this->selectKey++;
    }

    public function addCompanero(): void
    {
        $this->form->addCompanero();
    }

    public function removeCompanero(int $index): void
    {
        $this->form->removeCompanero($index);
    }

    public function addMaterial(): void
    {
        $this->form->addMaterial();
    }

    public function removeMaterial(int $index): void
    {
        $this->form->removeMaterial($index);
    }

    public function guardar(): void
    {
        $esNuevo = $this->albaran === null;

        if (! $esNuevo) {
            Gate::authorize('update', $this->albaran);
        } else {
            Gate::authorize('create', Albaran::class);
        }

        $albaran = $this->form->save();

        if ($esNuevo) {
            $this->albaranCreadoId = $albaran->getKey();

            return;
        }

        session()->flash('status', "Parte «{$albaran->numero}» actualizado correctamente.");
        $this->redirectRoute('mobile.albaranes.ver', ['albaran' => $albaran->getKey()], navigate: false);
    }

    public function irAFirmar(): void
    {
        if ($this->albaranCreadoId === null) {
            return;
        }

        $this->redirectRoute('mobile.albaranes.firmar', ['albaran' => $this->albaranCreadoId], navigate: false);
    }

    public function irAlDashboard(): void
    {
        $this->redirectRoute('mobile.dashboard', navigate: false);
    }

    /**
     * Proyectos disponibles para el usuario actual.
     *
     * @return Collection<int, Proyecto>
     */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        $userId = (int) Auth::id();
        $puedeVerTodos = Auth::user()?->can('albaranes.ver_todos') ?? false;

        $query = Proyecto::query()
            ->with('cliente:id,nombre')
            ->where('estado', 'activo')
            ->orderBy('nombre');

        if (! $puedeVerTodos) {
            $query->where(function ($q) use ($userId): void {
                $q->whereHas('usuarios', fn ($qu) => $qu->where('users.id', $userId))
                    ->orWhere('responsable_principal_id', $userId);
            });
        }

        return $query->get(['id', 'nombre', 'codigo', 'cliente_id', 'responsable_principal_id']);
    }

    /** @return Collection<int, Concepto> */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        $proyecto = Proyecto::query()->find($this->form->proyecto_id);

        return $proyecto
            ? $proyecto->conceptos()->orderBy('nombre')->get(['conceptos.id', 'conceptos.nombre'])
            : collect();
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function responsablesDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        $proyecto = Proyecto::query()->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->usuarios()
            ->where('users.activo', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'responsable'))
            ->orderBy('nombre')
            ->get(['users.id', 'users.nombre', 'users.apellidos']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function usuariosProyecto(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        $proyecto = Proyecto::query()->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->usuarios()
            ->where('users.activo', true)
            ->orderBy('nombre')
            ->get(['users.id', 'users.nombre', 'users.apellidos']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function companerosDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        $proyecto = Proyecto::query()->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        $myId = (int) Auth::id();

        return $proyecto->usuarios()
            ->where('users.activo', true)
            ->where('users.id', '!=', $myId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'trabajador'))
            ->orderBy('nombre')
            ->get(['users.id', 'users.nombre', 'users.apellidos']);
    }

    /**
     * Materiales del proyecto seleccionado — opciones para el select + badge de unidad.
     *
     * @return Collection<int, Material>
     */
    #[Computed]
    public function materialesProyecto(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        $proyecto = Proyecto::query()->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->materiales()
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get(['materiales.id', 'materiales.descripcion', 'materiales.unidad_medida', 'materiales.stock']);
    }

    public function render(): View
    {
        $titulo = $this->albaran ? 'Editar parte' : 'Parte de Trabajo';
        $backRoute = $this->albaran
            ? route('mobile.albaranes.ver', ['albaran' => $this->albaran->getKey()])
            : route('mobile.dashboard');

        return view('livewire.mobile.albaranes.crear', [
            'tiposHora' => TipoHora::cases(),
        ])->layout('components.layouts.mobile', [
            'title' => $titulo,
            'showBack' => true,
            'backRoute' => $backRoute,
        ]);
    }
}
