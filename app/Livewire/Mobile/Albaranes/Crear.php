<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Livewire\Forms\AlbaranForm;
use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
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
use Livewire\Component;

class Crear extends Component
{
    public AlbaranForm $form;

    public ?Albaran $albaran = null;

    /** ID del albarán recién creado, mientras el modal de firma está visible */
    public ?int $albaranCreadoId = null;

    /** Cuando es true, al guardar se genera también un albarán vinculado al parte */
    public bool $crearAlbaran = false;

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
            // Edición de albarán existente: flujo original.
            Gate::authorize('update', $this->albaran);
            $albaran = $this->form->save();
            session()->flash('status', "Parte «{$albaran->numero}» actualizado correctamente.");
            $this->redirectRoute('mobile.albaranes.ver', ['albaran' => $albaran->getKey()], navigate: false);

            return;
        }

        // Creación nueva.
        if ($this->crearAlbaran) {
            Gate::authorize('create', Albaran::class);

            $albaran = DB::transaction(function (): Albaran {
                $parte = $this->form->saveComoParte();

                $numero  = app(NumeracionService::class)->siguienteNumeroAlbaran(Carbon::parse($parte->fecha));
                $albaran = Albaran::create([
                    'numero'              => $numero,
                    'fecha'               => $parte->fecha,
                    'cliente_id'          => $parte->cliente_id,
                    'proyecto_id'         => $parte->proyecto_id,
                    'concepto_id'         => $parte->concepto_id,
                    'creado_por'          => $parte->creado_por ?? (int) Auth::id(),
                    'responsable_id'      => $parte->responsable_id,
                    'estado'              => EstadoAlbaran::PENDIENTE_FIRMA,
                    'tipo_hora'           => TipoHora::from($parte->tipo_hora),
                    'tiene_plus_retencion' => (bool) $parte->tiene_plus_retencion,
                    'observaciones'       => $parte->observaciones,
                    'es_personalizado'    => $parte->es_personalizado,
                    'cliente_texto'       => $parte->cliente_texto,
                    'proyecto_texto'      => $parte->proyecto_texto,
                    'concepto_texto'      => $parte->concepto_texto,
                    'responsable_texto'   => $parte->responsable_texto,
                    'firma_trabajador_user_id' => (int) Auth::id(),
                ]);

                foreach ($parte->lineasPersonal as $linea) {
                    AlbaranLineaPersonal::create([
                        'albaran_id'    => $albaran->id,
                        'trabajador_id' => $linea->trabajador_id,
                        'horas'         => $linea->horas,
                        'horas_extra'   => $linea->horas_extra,
                    ]);
                }

                foreach ($parte->lineasMaterial as $linea) {
                    AlbaranLineaMaterial::create([
                        'albaran_id'  => $albaran->id,
                        'material_id' => $linea->material_id,
                        'cantidad'    => $linea->cantidad,
                    ]);
                }

                $parte->albaran_id = $albaran->id;
                $parte->estado     = Parte::ESTADO_CERRADO;
                $parte->save();

                return $albaran;
            });

            $this->albaranCreadoId = $albaran->getKey();
        } else {
            Gate::authorize('create', Parte::class);

            $parte = $this->form->saveComoParte();
            session()->flash('status', "Parte «{$parte->numero}» creado correctamente.");
            $this->redirectRoute('mobile.dashboard', navigate: false);
        }
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
