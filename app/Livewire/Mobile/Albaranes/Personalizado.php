<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Enums\TipoHora;
use App\Livewire\Forms\AlbaranForm;
use App\Models\Borrador;
use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\Material;
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

class Personalizado extends Component
{
    public AlbaranForm $form;

    public int $selectKey = 0;

    public ?int $borradorCreadoId = null;

    public bool $crearAlbaran = false;

    public function mount(): void
    {
        Gate::authorize('create', Borrador::class);

        $this->form->esPersonalizado = true;
        $this->form->fecha = now()->format('Y-m-d');
    }

    public function updatedFormClienteId(): void
    {
        $this->form->proyecto_id = null;
        $this->form->proyectoOtro = false;
        $this->form->proyectoTexto = '';
        $this->form->responsable_id = null;
        $this->selectKey++;
    }

    public function updatedFormProyectoId(): void
    {
        if ($this->form->proyecto_id !== null) {
            $proyecto = Proyecto::query()->find($this->form->proyecto_id);
            if ($proyecto !== null) {
                $this->form->responsable_id = $proyecto->responsable_principal_id;
            }
        }
    }

    public function toggleClienteOtro(): void
    {
        $this->form->clienteOtro = ! $this->form->clienteOtro;

        if ($this->form->clienteOtro) {
            $this->form->cliente_id = null;
            $this->form->proyecto_id = null;
            $this->form->proyectoOtro = true;
            $this->form->proyectoTexto = '';
            $this->selectKey++;
        } else {
            $this->form->clienteTexto = '';
            $this->form->proyectoOtro = false;
            $this->form->proyectoTexto = '';
            $this->form->proyecto_id = null;
        }
    }

    public function toggleProyectoOtro(): void
    {
        $this->form->proyectoOtro = ! $this->form->proyectoOtro;

        if ($this->form->proyectoOtro) {
            $this->form->proyecto_id = null;
        } else {
            $this->form->proyectoTexto = '';
        }
    }

    public function toggleConceptoOtro(): void
    {
        $this->form->conceptoOtro = ! $this->form->conceptoOtro;

        if ($this->form->conceptoOtro) {
            $this->form->concepto_id = null;
        } else {
            $this->form->conceptoTexto = '';
        }
    }

    public function toggleResponsableOtro(): void
    {
        $this->form->responsableOtro = ! $this->form->responsableOtro;

        if ($this->form->responsableOtro) {
            $this->form->responsable_id = null;
            $this->selectKey++;
        } else {
            $this->form->responsableTexto = '';
        }
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
        Gate::authorize('create', Borrador::class);

        $this->form->validate();

        $borrador = DB::transaction(function (): Borrador {
            $borrador = new Borrador;
            $borrador->numero_borrador = app(NumeracionService::class)->siguienteNumeroBorrador();
            $borrador->creado_por = (int) Auth::id();
            $borrador->estado = 'pendiente';

            $borrador->cliente_id = $this->form->clienteOtro ? null : ($this->form->cliente_id ? (int) $this->form->cliente_id : null);
            $borrador->cliente_texto = $this->form->clienteOtro ? trim($this->form->clienteTexto) : null;

            $borrador->proyecto_id = $this->form->proyectoOtro ? null : ($this->form->proyecto_id ? (int) $this->form->proyecto_id : null);
            $borrador->proyecto_texto = $this->form->proyectoOtro ? trim($this->form->proyectoTexto) : null;

            $borrador->concepto_id = $this->form->conceptoOtro ? null : ($this->form->concepto_id ? (int) $this->form->concepto_id : null);
            $borrador->concepto_texto = $this->form->conceptoOtro ? trim($this->form->conceptoTexto) : null;

            $borrador->responsable_id = $this->form->responsableOtro ? null : ($this->form->responsable_id ? (int) $this->form->responsable_id : null);
            $borrador->responsable_texto = $this->form->responsableOtro ? trim($this->form->responsableTexto) : null;

            $borrador->fecha = Carbon::parse($this->form->fecha);
            $borrador->tipo_hora = $this->form->tipo_hora;
            $borrador->observaciones = $this->form->observaciones;
            $borrador->tiene_plus_retencion = $this->form->tienesPlusRetencion;
            $borrador->crear_albaran = $this->crearAlbaran;
            $borrador->save();

            // Línea del creador
            $borrador->lineasPersonal()->create([
                'trabajador_id' => Auth::id(),
                'horas'         => $this->form->mi_horas,
                'horas_extra'   => $this->form->mi_horas_extra,
            ]);

            // Compañeros
            foreach ($this->form->companeros as $companero) {
                $borrador->lineasPersonal()->create([
                    'trabajador_id' => $companero['trabajador_id'],
                    'horas'         => $companero['horas'],
                    'horas_extra'   => $companero['horas_extra'] ?? '0.00',
                ]);
            }

            // Materiales (sin observer de stock — el borrador no descuenta)
            foreach ($this->form->materiales as $material) {
                $borrador->lineasMaterial()->create([
                    'material_id' => $material['material_id'],
                    'cantidad'    => $material['cantidad'],
                ]);
            }

            return $borrador;
        });

        $this->borradorCreadoId = $borrador->getKey();
    }

    public function irAlDashboard(): void
    {
        $this->redirectRoute('mobile.dashboard', navigate: false);
    }

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo_cliente']);
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosPorCliente(): Collection
    {
        if ($this->form->cliente_id === null) {
            return collect();
        }

        return Proyecto::query()
            ->where('cliente_id', $this->form->cliente_id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'cliente_id', 'responsable_principal_id']);
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
    public function responsablesDisponibles(): Collection
    {
        if ($this->form->cliente_id === null) {
            return collect();
        }

        return User::query()
            ->where('activo', true)
            ->where('cliente_id', $this->form->cliente_id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'responsable'))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function companerosDisponibles(): Collection
    {
        $myId = (int) Auth::id();

        return User::query()
            ->where('activo', true)
            ->where('id', '!=', $myId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'trabajador'))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos', 'numero_empleado']);
    }

    /** @return Collection<int, Material> */
    #[Computed]
    public function materialesDisponibles(): Collection
    {
        return Material::query()
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'unidad_medida', 'stock']);
    }

    public function render(): View
    {
        return view('livewire.mobile.albaranes.personalizado', [
            'tiposHora' => TipoHora::cases(),
        ])->layout('components.layouts.mobile', [
            'title'     => 'Parte personalizado',
            'showBack'  => true,
            'backRoute' => route('mobile.dashboard'),
        ]);
    }
}
