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
            // Mostrar modal "¿firmar ahora o luego?" en lugar de redirigir
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
     * Proyectos disponibles para el usuario actual:
     *  - Admin/superadmin (con `albaranes.ver_todos`) ven todos los proyectos.
     *  - Trabajadores ven sólo aquellos en los que participan o son responsable principal.
     *
     * @return Collection<int, Proyecto>
     */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        $userId = (int) Auth::id();
        $puedeVerTodos = Auth::user()?->can('albaranes.ver_todos') ?? false;

        $query = Proyecto::query()->orderBy('nombre');

        if (! $puedeVerTodos) {
            $query->where(function ($q) use ($userId): void {
                $q->whereHas('usuarios', fn ($qu) => $qu->where('users.id', $userId))
                    ->orWhere('responsable_principal_id', $userId);
            });
        }

        return $query->get(['id', 'nombre', 'cliente_id', 'responsable_principal_id']);
    }

    /**
     * Conceptos asignados al proyecto seleccionado.
     *
     * @return Collection<int, Concepto>
     */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->conceptos()->orderBy('nombre')->get(['conceptos.id', 'conceptos.nombre']);
    }

    /**
     * Usuarios asignados al proyecto seleccionado (para responsable + compañeros).
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function usuariosProyecto(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->usuarios()->orderBy('nombre')->get(['users.id', 'users.nombre', 'users.apellidos']);
    }

    /**
     * Materiales asignados al proyecto seleccionado.
     *
     * @return Collection<int, Material>
     */
    #[Computed]
    public function materialesProyecto(): Collection
    {
        if ($this->form->proyecto_id === null) {
            return collect();
        }

        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::query()->find($this->form->proyecto_id);

        if ($proyecto === null) {
            return collect();
        }

        return $proyecto->materiales()
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
