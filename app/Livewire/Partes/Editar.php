<?php

namespace App\Livewire\Partes;

use App\Livewire\Forms\ParteForm;
use App\Models\AtributoHora;
use App\Models\Parte;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Componente que gestiona Crear y Editar partes.
 *
 * Si llega con $parte=null estamos en modo Crear; si llega con $parte
 * estamos en modo Editar. El form se rellena en mount() vía fromModel().
 */
#[Layout('components.layouts.web', ['active' => 'partes'])]
#[Title('Editar parte')]
class Editar extends Component
{
    public ?Parte $parte = null;

    public ParteForm $form;

    public bool $modoCrear = true;

    public function mount(?Parte $parte = null): void
    {
        $this->parte = $parte;
        $this->modoCrear = $parte === null;

        if ($parte !== null) {
            Gate::authorize('update', $parte);
            $parte->load(['lineasPersonal']);
            $this->form->fromModel($parte);
        } else {
            Gate::authorize('create', Parte::class);
            // Defaults razonables al crear:
            //   - el operario logueado se preselecciona si es interno
            //   - fecha = hoy
            $user = Auth::user();
            if ($user && ! $user->roles->contains(fn ($r) => (bool) $r->es_externo)) {
                $this->form->user_id = $user->id;
            }
            $this->form->fecha = now()->toDateString();
            $this->form->hora_inicio = '08:00';
            $this->form->hora_fin = '17:00';
        }
    }

    public function updatedFormProyectoId(): void
    {
        // Al elegir proyecto, autoflag es_albaran desde el tipo_proyecto.
        if (! $this->form->proyecto_id) {
            return;
        }
        $proyecto = Proyecto::find($this->form->proyecto_id);
        if (! $proyecto?->tipo_proyecto_id) {
            return;
        }
        $tipo = TiposProyecto::find($proyecto->tipo_proyecto_id);
        if ($tipo === null) {
            return;
        }
        $this->form->es_albaran = (bool) $tipo->genera_albaran_por_defecto;
    }

    public function addLinea(): void
    {
        $this->form->addLineaPersonal();
    }

    public function removeLinea(int $index): void
    {
        $this->form->removeLineaPersonal($index);
    }

    public function guardar(): mixed
    {
        if ($this->parte !== null) {
            Gate::authorize('update', $this->parte);
        } else {
            Gate::authorize('create', Parte::class);
        }

        $parte = $this->form->save();
        $this->parte = $parte;
        $this->modoCrear = false;

        session()->flash('status', "Parte «{$parte->codigo}» guardado correctamente.");

        return $this->redirect(route('partes.editar', $parte), navigate: true);
    }

    public function deshacer(): mixed
    {
        if ($this->parte !== null) {
            return $this->redirect(route('partes.editar', $this->parte), navigate: true);
        }

        return $this->redirect(route('partes.crear'), navigate: true);
    }

    /* ── Computeds ────────────────────────────────────────────── */

    /** @return Collection<int, User> Operarios disponibles (internos activos). */
    #[Computed]
    public function operariosDisponibles(): Collection
    {
        return User::query()
            ->whereNull('deleted_at')
            ->where('activo', true)
            ->whereDoesntHave('roles', fn ($q) => $q->where('es_externo', true))
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos', 'username', 'numero_empleado']);
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        return Proyecto::query()
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre']);
    }

    /** @return Collection<int, AtributoHora> */
    #[Computed]
    public function atributosDisponibles(): Collection
    {
        return AtributoHora::query()->orderBy('orden')->get();
    }

    public function render(): View
    {
        return view('livewire.partes.editar', [
            'titulo' => $this->modoCrear ? 'Nuevo parte' : 'Editar parte',
        ]);
    }
}
